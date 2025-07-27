<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\Vehicle;
use App\Models\EtatDesLieux;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;

class MaintenanceController extends Controller
{
    use AuthorizesRequests;

    /**
     * Afficher la liste des maintenances
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Construire la requête de base
        $query = Maintenance::whereHas('vehicle', function ($query) use ($user) {
            $query->where('tenant_id', $user->tenant_id)
                  ->where('user_id', $user->id);
        })
        ->with(['vehicle:id,marque,modele,immatriculation']);

        // Appliquer les filtres si fournis
        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Récupérer les maintenances avec tri
        $maintenances = $query->orderBy('maintenance_date', 'desc')->get();

        // Adapter le format pour correspondre à ce que le frontend attend
        $formattedMaintenances = $maintenances->map(function ($maintenance) {
            return [
                'id' => $maintenance->id,
                'date' => $maintenance->maintenance_date->format('Y-m-d'),
                'type' => $maintenance->maintenance_type,
                'garage' => $maintenance->workshop,
                'kilometrage' => $maintenance->mileage,
                'montant' => $maintenance->cost,
                'pieces' => $maintenance->description, // En attendant d'avoir une vraie table de pièces
                'status' => $maintenance->status, // ✅ Ajout du statut
                'vehicle' => [
                    'marque' => $maintenance->vehicle->marque,
                    'modele' => $maintenance->vehicle->modele,
                    'plaque' => $maintenance->vehicle->immatriculation,
                ],
                'facture' => null, // À implémenter plus tard si nécessaire
            ];
        });

        return response()->json($formattedMaintenances);
    }

    /**
     * Créer une nouvelle maintenance
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Debug : afficher les données reçues
        \Log::info('Données reçues pour maintenance:', $request->all());

        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'maintenance_type' => ['required', 'in:oil_change,revision,tires,brakes,belt,filters,other'],
            'reason' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'maintenance_date' => ['required', 'date'],
            'mileage' => ['required', 'integer', 'min:0'],
            'cost' => ['required', 'numeric', 'min:0'],
            'workshop' => ['required', 'string', 'max:255'],
            'next_maintenance' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:scheduled,in_progress,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        // Vérifier que le véhicule appartient au tenant de l'utilisateur
        $vehicle = Vehicle::where('id', $validated['vehicle_id'])
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (!$vehicle) {
            return response()->json(['message' => 'Vehicle not found or access denied'], 404);
        }

        // Validation du kilométrage cohérent
        $minKilometrage = $this->getMinimumKilometrage($validated['vehicle_id']);
        
        if ($validated['mileage'] < $minKilometrage) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'mileage' => ["Le kilométrage ne peut pas être inférieur à {$minKilometrage} km (dernier enregistrement)"]
                ]
            ], 422);
        }

        $maintenance = Maintenance::create([
            ...$validated,
            'status' => $validated['status'] ?? 'completed',
        ]);

        // Mettre à jour le kilométrage du véhicule si c'est plus récent
        if ($validated['mileage'] > $vehicle->kilometrage) {
            $vehicle->update(['kilometrage' => $validated['mileage']]);
        }

        $maintenance->load('vehicle:id,marque,modele,immatriculation');

        return response()->json([
            'message' => 'Maintenance created successfully',
            'maintenance' => $maintenance,
        ], 201);
    }

    /**
     * Afficher une maintenance spécifique
     */
    public function show(string $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $maintenance = Maintenance::whereHas('vehicle', function ($query) use ($user) {
            $query->where('tenant_id', $user->tenant_id);
        })
        ->with(['vehicle:id,marque,modele,immatriculation'])
        ->find($id);

        if (!$maintenance) {
            return response()->json(['message' => 'Maintenance not found'], 404);
        }

        return response()->json([
            'data' => $maintenance
        ]);
    }

    /**
     * Mettre à jour une maintenance
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $maintenance = Maintenance::whereHas('vehicle', function ($query) use ($user) {
            $query->where('tenant_id', $user->tenant_id);
        })->find($id);

        if (!$maintenance) {
            return response()->json(['message' => 'Maintenance not found'], 404);
        }

        $validated = $request->validate([
            'vehicle_id' => ['sometimes', 'exists:vehicles,id'],
            'maintenance_type' => ['sometimes', 'in:oil_change,revision,tires,brakes,belt,filters,other'],
            'description' => ['sometimes', 'string'],
            'maintenance_date' => ['sometimes', 'date'],
            'mileage' => ['sometimes', 'integer', 'min:0'],
            'cost' => ['sometimes', 'numeric', 'min:0'],
            'workshop' => ['sometimes', 'string', 'max:255'],
            'next_maintenance' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:scheduled,in_progress,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        // Si vehicle_id est fourni, vérifier qu'il appartient au tenant
        if (isset($validated['vehicle_id'])) {
            $vehicle = Vehicle::where('id', $validated['vehicle_id'])
                ->where('tenant_id', $user->tenant_id)
                ->first();

            if (!$vehicle) {
                return response()->json(['message' => 'Vehicle not found or access denied'], 404);
            }
        }

        // Validation du kilométrage si modifié
        if (isset($validated['mileage'])) {
            $vehicleId = $validated['vehicle_id'] ?? $maintenance->vehicle_id;
            $minKilometrage = $this->getMinimumKilometrage($vehicleId, $maintenance->id);
            
            if ($validated['mileage'] < $minKilometrage) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'mileage' => ["Le kilométrage ne peut pas être inférieur à {$minKilometrage} km (dernier enregistrement)"]
                    ]
                ], 422);
            }

            // Mettre à jour le kilométrage du véhicule si nécessaire
            $vehicle = $maintenance->vehicle;
            if ($validated['mileage'] > $vehicle->kilometrage) {
                $vehicle->update(['kilometrage' => $validated['mileage']]);
            }
        }

        $maintenance->update($validated);
        $maintenance->load('vehicle:id,marque,modele,immatriculation');

        return response()->json([
            'message' => 'Maintenance updated successfully',
            'maintenance' => $maintenance,
        ]);
    }

    /**
     * Supprimer une maintenance
     */
    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $maintenance = Maintenance::whereHas('vehicle', function ($query) use ($user) {
            $query->where('tenant_id', $user->tenant_id);
        })->find($id);

        if (!$maintenance) {
            return response()->json(['message' => 'Maintenance not found'], 404);
        }

        $maintenance->delete();

        return response()->json([
            'message' => 'Maintenance deleted successfully'
        ]);
    }

    /**
     * Récupère le kilométrage minimum autorisé pour un véhicule
     */
    private function getMinimumKilometrage(int $vehicleId, ?int $excludeMaintenanceId = null): int
    {
        $vehicle = Vehicle::find($vehicleId);
        $vehicleKilometrage = $vehicle->kilometrage ?? 0;

        // Dernière maintenance (en excluant celle en cours d'édition si fournie)
        $lastMaintenanceQuery = Maintenance::where('vehicle_id', $vehicleId)
            ->orderBy('maintenance_date', 'desc')
            ->orderBy('created_at', 'desc');
        
        if ($excludeMaintenanceId) {
            $lastMaintenanceQuery->where('id', '!=', $excludeMaintenanceId);
        }
        
        $lastMaintenance = $lastMaintenanceQuery->first();
        $lastMaintenanceKilometrage = $lastMaintenance ? $lastMaintenance->mileage : 0;

        // Dernier état des lieux
        $lastEtatDesLieux = EtatDesLieux::where('vehicle_id', $vehicleId)
            ->orderBy('created_at', 'desc')
            ->first();
        $lastEtatDesLieuxKilometrage = $lastEtatDesLieux ? $lastEtatDesLieux->kilometrage : 0;

        // Dernier CT
        $lastCtKilometrage = 0;
        if ($vehicle->last_ct_date) {
            // On suppose que le CT a été fait au kilométrage actuel du véhicule à cette date
            // Idealement, il faudrait stocker le kilométrage du CT dans une table séparée
            $lastCtKilometrage = $vehicleKilometrage;
        }

        // Le kilométrage d'achat (purchase_date)
        $purchaseKilometrage = 0; // On suppose que l'achat se fait à 0 km ou on pourrait stocker cette info

        return max($vehicleKilometrage, $lastMaintenanceKilometrage, $lastEtatDesLieuxKilometrage, $lastCtKilometrage, $purchaseKilometrage);
    }
}
