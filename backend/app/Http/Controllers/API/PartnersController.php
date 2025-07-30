<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\TenantPartnerRelation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class PartnersController extends Controller
{
    /**
     * Get partners for Internal management.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Partner::query();

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('city')) {
            $query->where('city', 'LIKE', '%' . $request->city . '%');
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('is_verified')) {
            $query->where('is_verified', $request->boolean('is_verified'));
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('address', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('city', 'LIKE', '%' . $request->search . '%');
            });
        }

        $partners = $query->paginate($request->get('per_page', 15));

        return response()->json($partners);
    }

    /**
     * Create a new partner (Internal only).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['garage', 'controle_technique', 'assurance'])],
            'description' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'country' => 'string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'services' => 'nullable|array',
            'pricing' => 'nullable|array',
            'availability' => 'nullable|array',
            'service_zone' => 'nullable|array',
            'metadata' => 'nullable|array',
        ]);

        $partner = Partner::create($validated);

        return response()->json($partner, 201);
    }

    /**
     * Get a specific partner.
     */
    public function show(Partner $partner): JsonResponse
    {
        return response()->json($partner->load('relations.tenant'));
    }

    /**
     * Update a partner (Internal only).
     */
    public function update(Request $request, Partner $partner): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => ['sometimes', Rule::in(['garage', 'controle_technique', 'assurance'])],
            'description' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:10',
            'country' => 'sometimes|string|max:100',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'services' => 'nullable|array',
            'pricing' => 'nullable|array',
            'availability' => 'nullable|array',
            'service_zone' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
            'is_verified' => 'sometimes|boolean',
            'metadata' => 'nullable|array',
        ]);

        $partner->update($validated);

        return response()->json($partner);
    }

    /**
     * Delete a partner (Internal only).
     */
    public function destroy(Partner $partner): JsonResponse
    {
        $partner->delete();

        return response()->json(['message' => 'Partner deleted successfully']);
    }

    /**
     * Find partners near location (Tenant use).
     */
    public function findNearby(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:100',
            'type' => ['nullable', Rule::in(['garage', 'controle_technique', 'assurance'])],
            'service' => 'nullable|string',
        ]);

        $radius = $validated['radius'] ?? 50;
        $query = Partner::active()
            ->verified()
            ->nearLocation($validated['latitude'], $validated['longitude'], $radius);

        if (isset($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (isset($validated['service'])) {
            $query->whereJsonContains('services', $validated['service']);
        }

        $partners = $query->get();

        // Add relation data for current tenant if authenticated
        $user = $request->user();
        if ($user && $user->tenant) {
            $partners->each(function ($partner) use ($user) {
                $relation = TenantPartnerRelation::where('tenant_id', $user->tenant_id)
                    ->where('partner_id', $partner->id)
                    ->first();
                
                $partner->tenant_relation = $relation;
            });
        }

        return response()->json($partners);
    }

    /**
     * Rate a partner (Tenant only).
     */
    public function rate(Request $request, Partner $partner): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        if (!$user || !$user->tenant) {
            return response()->json(['error' => 'Tenant information required'], 403);
        }

        $relation = TenantPartnerRelation::firstOrCreate([
            'tenant_id' => $user->tenant_id,
            'partner_id' => $partner->id,
        ]);

        $relation->updateRating($validated['rating'], $validated['comment'] ?? null);

        return response()->json([
            'message' => 'Rating updated successfully',
            'partner' => $partner->fresh(),
            'relation' => $relation->fresh(),
        ]);
    }

    /**
     * Book a service with partner (Tenant only).
     */
    public function book(Request $request, Partner $partner): JsonResponse
    {
        $validated = $request->validate([
            'service_type' => 'required|string',
            'preferred_date' => 'required|date|after:today',
            'preferred_time' => 'required|string',
            'vehicle_id' => 'required|exists:vehicles,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        if (!$user || !$user->tenant) {
            return response()->json(['error' => 'Tenant information required'], 403);
        }

        // Verify vehicle belongs to user's tenant
        $vehicle = \App\Models\Vehicle::where('id', $validated['vehicle_id'])
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found or access denied'], 404);
        }

        // Create or update relation
        $relation = TenantPartnerRelation::firstOrCreate([
            'tenant_id' => $user->tenant_id,
            'partner_id' => $partner->id,
        ]);

        $relation->recordBooking();

        // TODO: Implement actual booking system with partner
        // For now, we'll create a basic booking record

        return response()->json([
            'message' => 'Booking request sent successfully',
            'booking_id' => 'BK-' . time(), // Temporary ID
            'partner' => $partner,
            'details' => $validated,
        ]);
    }

    /**
     * Get partner statistics (Internal only).
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_partners' => Partner::count(),
            'active_partners' => Partner::active()->count(),
            'verified_partners' => Partner::verified()->count(),
            'by_type' => Partner::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type'),
            'by_city' => Partner::selectRaw('city, COUNT(*) as count')
                ->groupBy('city')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->pluck('count', 'city'),
            'average_rating' => Partner::where('rating_count', '>', 0)->avg('rating'),
        ];

        return response()->json($stats);
    }
}