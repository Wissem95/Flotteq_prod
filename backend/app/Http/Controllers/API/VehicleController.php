<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Data\VehicleData;
use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Maintenance;
use App\Models\VehicleStatusNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use App\Http\Resources\VehicleResource;

class VehicleController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of vehicles for the current tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Vehicle::class);

        // Récupérer le tenant depuis l'utilisateur authentifié au lieu du binding
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $vehicles = Vehicle::where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id) // Filtrer par utilisateur spécifique
            ->with(['user:id,first_name,last_name'])
            ->when($request->search, function($q) use ($request) {
                $q->where(function($query) use ($request) {
                    $query->where('marque', 'ILIKE', "%{$request->search}%")
                          ->orWhere('modele', 'ILIKE', "%{$request->search}%")
                          ->orWhere('immatriculation', 'ILIKE', "%{$request->search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // return VehicleResource::collection($vehicles)->response();
        return response()->json($vehicles);
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Vehicle::class);

        // Récupérer le tenant depuis l'utilisateur authentifié
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'marque' => ['required', 'string', 'max:100'],
            'modele' => ['required', 'string', 'max:100'],
            'immatriculation' => ['required', 'string', 'regex:/^[A-Z]{2}-[0-9]{3}-[A-Z]{2}$/', 'unique:vehicles,immatriculation'],
            'vin' => ['nullable', 'string', 'max:17', 'unique:vehicles,vin'],
            'annee' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'couleur' => ['nullable', 'string', 'max:50'],
            'kilometrage' => ['required', 'integer', 'min:0'],
            'carburant' => ['required', 'in:essence,diesel,electrique,hybride,gpl'],
            'transmission' => ['required', 'in:manuelle,automatique'],
            'puissance' => ['nullable', 'integer', 'min:0'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:active,vendu,en_reparation,en_maintenance,hors_service'],
            'last_ct_date' => ['nullable', 'date'],
            'next_ct_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $vehicle = Vehicle::create([
            ...$validated,
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'status' => 'active',
        ]);

        $vehicle->load('user:id,first_name,last_name');

        return response()->json([
            'message' => 'Vehicle created successfully',
            'vehicle' => $vehicle,
        ], 201);
    }

    /**
     * Display the specified vehicle.
     */
    public function show(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('view', $vehicle);

        $vehicle->load([
            'user:id,first_name,last_name',
            'invoices' => fn($q) => $q->latest()->take(5),
            'maintenances' => fn($q) => $q->latest()->take(5),
            'technicalInspections' => fn($q) => $q->latest()->take(1),
        ]);

        return response()->json([
            'data' => $vehicle
        ]);
    }

    /**
     * Update the specified vehicle.
     */
    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorize('update', $vehicle);

        $validated = $request->validate([
            'marque' => ['sometimes', 'string', 'max:100'],
            'modele' => ['sometimes', 'string', 'max:100'],
            'immatriculation' => ['sometimes', 'string', 'regex:/^[A-Z]{2}-[0-9]{3}-[A-Z]{2}$/', Rule::unique('vehicles')->ignore($vehicle->id)],
            'vin' => ['nullable', 'string', 'max:17', Rule::unique('vehicles')->ignore($vehicle->id)],
            'annee' => ['sometimes', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'couleur' => ['nullable', 'string', 'max:50'],
            'kilometrage' => ['sometimes', 'integer', 'min:0'],
            'carburant' => ['sometimes', 'in:essence,diesel,electrique,hybride,gpl'],
            'transmission' => ['sometimes', 'in:manuelle,automatique'],
            'puissance' => ['nullable', 'integer', 'min:0'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:active,vendu,en_reparation,en_maintenance,hors_service'],
            'last_ct_date' => ['nullable', 'date'],
            'next_ct_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'status_reason' => ['nullable', 'string', 'max:255'], // Raison du changement de statut
        ]);

        // Détecter changement de statut avant la mise à jour
        $oldStatus = $vehicle->status;
        $newStatus = $validated['status'] ?? $oldStatus;
        $statusReason = $validated['status_reason'] ?? null;

        // Retirer status_reason des données à sauvegarder (ce n'est que pour la notification)
        unset($validated['status_reason']);

        $vehicle->update($validated);

        // Créer une notification si le statut a changé
        if ($oldStatus !== $newStatus) {
            VehicleStatusNotification::createForStatusChange(
                $vehicle,
                $oldStatus,
                $newStatus,
                $statusReason
            );
        }

        $vehicle->load('user:id,first_name,last_name');

        return response()->json([
            'message' => 'Vehicle updated successfully',
            'vehicle' => $vehicle,
        ]);
    }

    /**
     * Remove the specified vehicle.
     */
    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('delete', $vehicle);

        $vehicle->delete();

        return response()->json([
            'message' => 'Vehicle deleted successfully',
        ]);
    }

    /**
     * Get vehicle history for the current tenant.
     */
    public function history(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Récupérer l'historique basé sur les maintenances terminées
        $history = Maintenance::whereHas('vehicle', function ($query) use ($user) {
            $query->where('tenant_id', $user->tenant_id);
        })
        ->where('status', 'completed')
        ->with(['vehicle:id,marque,modele,immatriculation'])
        ->orderBy('maintenance_date', 'desc')
        ->get()
        ->map(function ($maintenance) {
            // Traduction des types de maintenance
            $maintenanceTypes = [
                'oil_change' => 'Vidange',
                'revision' => 'Révision générale',
                'tires' => 'Pneus',
                'brakes' => 'Freins',
                'belt' => 'Courroie',
                'filters' => 'Filtres',
                'other' => 'Autre'
            ];

            return [
                'id' => $maintenance->id,
                'date' => $maintenance->maintenance_date->format('d/m/Y'),
                'description' => "{$maintenanceTypes[$maintenance->maintenance_type]} - {$maintenance->description}",
                'vehicle' => [
                    'marque' => $maintenance->vehicle->marque,
                    'modele' => $maintenance->vehicle->modele,
                    'plaque' => $maintenance->vehicle->immatriculation,
                ],
                'type' => 'maintenance',
                'vehicleId' => $maintenance->vehicle_id,
                // Détails supplémentaires pour l'historique
                'details' => [
                    'maintenance_type' => $maintenanceTypes[$maintenance->maintenance_type],
                    'workshop' => $maintenance->workshop,
                    'cost' => $maintenance->cost,
                    'mileage' => $maintenance->mileage,
                    'notes' => $maintenance->notes,
                    'next_maintenance' => $maintenance->next_maintenance ? $maintenance->next_maintenance->format('d/m/Y') : null,
                ],
                'maintenance_id' => $maintenance->id,
                'can_edit' => true,
                'can_delete' => true,
            ];
        });

        return response()->json($history);
    }
}
