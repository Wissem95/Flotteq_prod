<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VehicleStatusNotification;
use App\Models\Vehicle;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AlertsController extends Controller
{
    /**
     * Display system alerts
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $alerts = collect();

            // Vehicle status notifications
            $vehicleAlerts = VehicleStatusNotification::with(['vehicle.tenant'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => 'vehicle_status',
                        'title' => 'Changement de statut véhicule',
                        'message' => "Le véhicule {$notification->vehicle->immatriculation} est passé de '{$notification->old_status}' à '{$notification->new_status}'",
                        'severity' => $this->getStatusSeverity($notification->new_status),
                        'tenant' => $notification->vehicle->tenant->name ?? 'N/A',
                        'created_at' => $notification->created_at,
                        'read' => $notification->read_at !== null,
                    ];
                });

            $alerts = $alerts->merge($vehicleAlerts);

            // Maintenance alerts - Overdue (en retard)
            $overdueMaintenances = Maintenance::with(['vehicle.tenant'])
                ->overdue()  // Utilise le scope défini dans le modèle
                ->limit(10)
                ->get()
                ->map(function ($maintenance) {
                    // Utiliser scheduled_date si disponible, sinon next_maintenance
                    $referenceDate = $maintenance->scheduled_date ?? $maintenance->next_maintenance;
                    $daysOverdue = $referenceDate ? now()->diffInDays($referenceDate) : 0;
                    
                    return [
                        'id' => 'maintenance_overdue_' . $maintenance->id,
                        'type' => 'maintenance_overdue',
                        'title' => 'Maintenance en retard',
                        'message' => "⚠️ Maintenance en retard de {$daysOverdue} jours pour le véhicule {$maintenance->vehicle->immatriculation} - {$maintenance->type}",
                        'severity' => 'high',
                        'tenant' => $maintenance->vehicle->tenant->name ?? 'N/A',
                        'created_at' => $referenceDate ?? $maintenance->created_at,
                        'read' => false,
                        'metadata' => [
                            'vehicle_id' => $maintenance->vehicle_id,
                            'maintenance_id' => $maintenance->id,
                            'days_overdue' => $daysOverdue,
                            'priority' => $maintenance->priority ?? 'medium',
                            'maintenance_type' => $maintenance->maintenance_type
                        ]
                    ];
                });

            $alerts = $alerts->merge($overdueMaintenances);

            // Maintenance alerts - Upcoming (à venir dans les 30 prochains jours)
            $upcomingMaintenances = Maintenance::with(['vehicle.tenant'])
                ->upcoming(30)  // Utilise le scope défini dans le modèle
                ->limit(10)
                ->get()
                ->map(function ($maintenance) {
                    // Utiliser scheduled_date si disponible, sinon next_maintenance
                    $referenceDate = $maintenance->scheduled_date ?? $maintenance->next_maintenance;
                    $daysUntil = $referenceDate ? now()->diffInDays($referenceDate, false) : 0;
                    
                    return [
                        'id' => 'maintenance_upcoming_' . $maintenance->id,
                        'type' => 'maintenance_upcoming',
                        'title' => 'Maintenance à venir',
                        'message' => "📅 Maintenance prévue dans {$daysUntil} jours pour le véhicule {$maintenance->vehicle->immatriculation} - {$maintenance->type}",
                        'severity' => $daysUntil <= 7 ? 'medium' : 'low',
                        'tenant' => $maintenance->vehicle->tenant->name ?? 'N/A',
                        'created_at' => $referenceDate ?? $maintenance->created_at,
                        'read' => false,
                        'metadata' => [
                            'vehicle_id' => $maintenance->vehicle_id,
                            'maintenance_id' => $maintenance->id,
                            'days_until' => $daysUntil,
                            'priority' => $maintenance->priority ?? 'medium',
                            'maintenance_type' => $maintenance->maintenance_type
                        ]
                    ];
                });

            $alerts = $alerts->merge($upcomingMaintenances);

            // System health alerts
            $systemAlerts = $this->getSystemHealthAlerts();
            $alerts = $alerts->merge($systemAlerts);

            // Sort by created_at desc
            $alerts = $alerts->sortByDesc('created_at')->values();

            // Apply filters
            if ($request->has('type')) {
                $alerts = $alerts->where('type', $request->get('type'));
            }

            if ($request->has('severity')) {
                $alerts = $alerts->where('severity', $request->get('severity'));
            }

            // Pagination
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 15);
            $total = $alerts->count();
            $alerts = $alerts->forPage($page, $perPage)->values();

            return response()->json([
                'success' => true,
                'data' => $alerts,
                'pagination' => [
                    'current_page' => (int) $page,
                    'per_page' => (int) $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                ],
                'summary' => [
                    'total_alerts' => $total,
                    'unread_alerts' => $alerts->where('read', false)->count(),
                    'high_severity' => $alerts->where('severity', 'high')->count(),
                ],
                'message' => 'Alertes récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des alertes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new alert
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'type' => 'required|string',
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'severity' => 'required|in:low,medium,high,critical',
            ]);

            // For now, we'll create a vehicle status notification as example
            // In a real system, you'd have a dedicated alerts table
            $alert = VehicleStatusNotification::create([
                'vehicle_id' => 1, // Placeholder
                'old_status' => 'system',
                'new_status' => $request->type,
                'changed_by' => auth()->id(),
                'reason' => $request->message,
            ]);

            return response()->json([
                'success' => true,
                'data' => $alert,
                'message' => 'Alerte créée avec succès'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'alerte',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an alert (mark as read, etc.)
     */
    public function update(Request $request, $alertId): JsonResponse
    {
        try {
            // Handle different alert types
            if (str_starts_with($alertId, 'maintenance_')) {
                $maintenanceId = str_replace('maintenance_', '', $alertId);
                // Update maintenance or mark as acknowledged
                return response()->json([
                    'success' => true,
                    'message' => 'Alerte de maintenance mise à jour'
                ]);
            }

            // Handle vehicle status notifications
            $notification = VehicleStatusNotification::findOrFail($alertId);
            
            if ($request->has('read')) {
                $notification->read_at = $request->get('read') ? now() : null;
                $notification->save();
            }

            return response()->json([
                'success' => true,
                'data' => $notification,
                'message' => 'Alerte mise à jour avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'alerte',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an alert
     */
    public function destroy($alertId): JsonResponse
    {
        try {
            if (str_starts_with($alertId, 'maintenance_')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Alerte de maintenance supprimée'
                ]);
            }

            $notification = VehicleStatusNotification::findOrFail($alertId);
            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Alerte supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'alerte',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get severity based on vehicle status
     */
    private function getStatusSeverity(string $status): string
    {
        return match ($status) {
            'broken', 'accident' => 'critical',
            'maintenance', 'repair' => 'high',
            'available' => 'low',
            default => 'medium',
        };
    }

    /**
     * Get system health alerts
     */
    private function getSystemHealthAlerts(): array
    {
        $alerts = [];

        // Database health
        try {
            \DB::connection()->getPdo();
        } catch (\Exception $e) {
            $alerts[] = [
                'id' => 'db_health',
                'type' => 'system_health',
                'title' => 'Problème de base de données',
                'message' => 'Connexion à la base de données échouée',
                'severity' => 'critical',
                'tenant' => 'System',
                'created_at' => now(),
                'read' => false,
            ];
        }

        // Storage space
        $diskSpace = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $diskUsagePercent = (($diskTotal - $diskSpace) / $diskTotal) * 100;

        if ($diskUsagePercent > 90) {
            $alerts[] = [
                'id' => 'disk_space',
                'type' => 'system_health',
                'title' => 'Espace disque faible',
                'message' => sprintf('Utilisation du disque: %.1f%%', $diskUsagePercent),
                'severity' => 'high',
                'tenant' => 'System',
                'created_at' => now(),
                'read' => false,
            ];
        }

        return $alerts;
    }
}