<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EtatDesLieux;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EtatDesLieuxController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = EtatDesLieux::with(['vehicle', 'user', 'validator'])
            ->forTenant($request->header('X-Tenant-ID'));

        if ($request->has('vehicle_id') && $request->vehicle_id !== 'all' && $request->vehicle_id !== null) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->has('type') && $request->type !== 'all' && $request->type !== null) {
            $query->ofType($request->type);
        }

        $etatsDesLieux = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($etatsDesLieux);
    }

    public function show(EtatDesLieux $etatDesLieux): JsonResponse
    {
        $etatDesLieux->load(['vehicle', 'user', 'validator']);
        
        return response()->json($etatDesLieux);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'type' => 'required|in:depart,retour',
            'conducteur' => 'nullable|string|max:255',
            'kilometrage' => 'required|integer|min:0',
            'notes' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'file|image|mimes:jpeg,jpg,png,webp|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérifier que le véhicule appartient au tenant
        $vehicle = Vehicle::where('id', $request->vehicle_id)
            ->where('tenant_id', $request->header('X-Tenant-ID'))
            ->first();

        if (!$vehicle) {
            return response()->json(['error' => 'Véhicule non trouvé'], 404);
        }

        // Validation du kilométrage cohérent
        $lastEtatDesLieux = EtatDesLieux::where('vehicle_id', $request->vehicle_id)
            ->orderBy('created_at', 'desc')
            ->first();

        $minKilometrage = max(
            $vehicle->kilometrage ?? 0,
            $lastEtatDesLieux ? $lastEtatDesLieux->kilometrage : 0
        );

        if ($request->kilometrage < $minKilometrage) {
            return response()->json([
                'error' => "Le kilométrage ne peut pas être inférieur à {$minKilometrage} km (dernier enregistrement)"
            ], 422);
        }

        $etatDesLieux = EtatDesLieux::create([
            'vehicle_id' => $request->vehicle_id,
            'user_id' => Auth::id(),
            'tenant_id' => $request->header('X-Tenant-ID'),
            'type' => $request->type,
            'conducteur' => $request->conducteur,
            'kilometrage' => $request->kilometrage,
            'notes' => $request->notes,
        ]);

        // Upload des photos
        if ($request->hasFile('photos')) {
            $photoUrls = [];
            $photoPositions = $etatDesLieux->getPhotoPositions();
            
            foreach ($request->file('photos') as $position => $photo) {
                if (array_key_exists($position, $photoPositions)) {
                    $media = $etatDesLieux->addMediaFromRequest("photos.{$position}")
                        ->usingName($photoPositions[$position])
                        ->usingFileName("{$position}_{$etatDesLieux->id}_{time()}.{$photo->getClientOriginalExtension()}")
                        ->toMediaCollection('etat_des_lieux_photos');
                    
                    $photoUrls[$position] = $media->getUrl();
                }
            }
            
            $etatDesLieux->update(['photos' => $photoUrls]);
        }

        // Mettre à jour le kilométrage du véhicule
        $vehicle->update(['kilometrage' => $request->kilometrage]);

        $etatDesLieux->load(['vehicle', 'user']);

        return response()->json($etatDesLieux, 201);
    }

    public function update(Request $request, EtatDesLieux $etatDesLieux): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|in:depart,retour',
            'conducteur' => 'nullable|string|max:255',
            'kilometrage' => 'sometimes|integer|min:0',
            'notes' => 'nullable|string',
            'is_validated' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('kilometrage')) {
            $vehicle = $etatDesLieux->vehicle;
            $lastEtatDesLieux = EtatDesLieux::where('vehicle_id', $etatDesLieux->vehicle_id)
                ->where('id', '!=', $etatDesLieux->id)
                ->orderBy('created_at', 'desc')
                ->first();

            $minKilometrage = max(
                $vehicle->kilometrage ?? 0,
                $lastEtatDesLieux ? $lastEtatDesLieux->kilometrage : 0
            );

            if ($request->kilometrage < $minKilometrage) {
                return response()->json([
                    'error' => "Le kilométrage ne peut pas être inférieur à {$minKilometrage} km"
                ], 422);
            }
        }

        $updateData = $request->only(['type', 'conducteur', 'kilometrage', 'notes']);

        if ($request->has('is_validated') && $request->is_validated) {
            $updateData['is_validated'] = true;
            $updateData['validated_at'] = now();
            $updateData['validated_by'] = Auth::id();
        }

        $etatDesLieux->update($updateData);

        if ($request->has('kilometrage')) {
            $etatDesLieux->vehicle->update(['kilometrage' => $request->kilometrage]);
        }

        $etatDesLieux->load(['vehicle', 'user', 'validator']);

        return response()->json($etatDesLieux);
    }

    public function destroy(EtatDesLieux $etatDesLieux): JsonResponse
    {
        $etatDesLieux->clearMediaCollection('etat_des_lieux_photos');
        $etatDesLieux->delete();

        return response()->json(['message' => 'État des lieux supprimé avec succès']);
    }

    public function uploadPhoto(Request $request, EtatDesLieux $etatDesLieux): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'position' => 'required|string',
            'photo' => 'required|file|image|mimes:jpeg,jpg,png,webp|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $photoPositions = $etatDesLieux->getPhotoPositions();
        $position = $request->position;

        if (!array_key_exists($position, $photoPositions)) {
            return response()->json(['error' => 'Position de photo invalide'], 422);
        }

        // Supprimer l'ancienne photo pour cette position si elle existe
        $existingMedia = $etatDesLieux->getMedia('etat_des_lieux_photos')
            ->where('name', $photoPositions[$position])
            ->first();
        
        if ($existingMedia) {
            $existingMedia->delete();
        }

        // Ajouter la nouvelle photo
        $media = $etatDesLieux->addMediaFromRequest('photo')
            ->usingName($photoPositions[$position])
            ->usingFileName("{$position}_{$etatDesLieux->id}_{time()}.{$request->file('photo')->getClientOriginalExtension()}")
            ->toMediaCollection('etat_des_lieux_photos');

        // Mettre à jour le champ photos
        $photos = $etatDesLieux->photos ?? [];
        $photos[$position] = $media->getUrl();
        $etatDesLieux->update(['photos' => $photos]);

        return response()->json([
            'message' => 'Photo uploadée avec succès',
            'photo_url' => $media->getUrl(),
            'position' => $position
        ]);
    }

    public function getPhotoPositions(): JsonResponse
    {
        $etatDesLieux = new EtatDesLieux();
        return response()->json($etatDesLieux->getPhotoPositions());
    }
}