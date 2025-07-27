<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Maintenance;
use App\Models\TechnicalInspection;
use App\Models\VehicleStatusNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NotificationController extends Controller 
{
    /**
     * Get all notifications for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $tenant = app('currentTenant');
        $user = $request->user();
        
        $notifications = collect();
        
        // Contrôles techniques à venir ou expirés
        $ctNotifications = $this->getControleTechniqueNotifications($tenant->id, $user->id);
        $notifications = $notifications->concat($ctNotifications);
        
        // Maintenances à venir
        $maintenanceNotifications = $this->getMaintenanceNotifications($tenant->id, $user->id);
        $notifications = $notifications->concat($maintenanceNotifications);
        
        // Véhicules avec problèmes
        $issueNotifications = $this->getVehicleIssueNotifications($tenant->id, $user->id);
        $notifications = $notifications->concat($issueNotifications);
        
        // Notifications de changement de statut de véhicules
        $statusNotifications = $this->getVehicleStatusNotifications($tenant->id, $user->id);
        $notifications = $notifications->concat($statusNotifications);
        
        // Transformer les notifications pour le frontend
        $formattedNotifications = $notifications->map(function($notification) {
            return [
                'id' => $notification['id'],
                'type' => $this->mapNotificationType($notification['type']),
                'title' => $this->getNotificationTitle($notification),
                'message' => $this->getNotificationMessage($notification),
                'data' => [
                    'vehicle' => [
                        'id' => $notification['vehicle_id'],
                        'marque' => explode(' ', $notification['vehicle'])[0] ?? '',
                        'modele' => explode(' ', $notification['vehicle'], 2)[1] ?? '',
                        'immatriculation' => $notification['plate'] ?? '',
                    ],
                    'due_date' => $notification['dueDate'] ?? null,
                    'priority' => $notification['priority'] ?? 'medium',
                    'maintenance_type' => $notification['maintenance_type'] ?? null,
                    'reason' => $notification['reason'] ?? null,
                ],
                'read_at' => null, // Toutes les notifications sont considérées comme non lues pour l'instant
                'created_at' => $notification['created'] ?? now()->toDateString(),
            ];
        });
        
        // Trier par priorité et date
        $sortedNotifications = $formattedNotifications->sortByDesc(function($notification) {
            $priorityWeight = match($notification['data']['priority']) {
                'critical' => 4,
                'high' => 3,
                'medium' => 2,
                'low' => 1,
                default => 0
            };
            
            $dateWeight = isset($notification['data']['due_date']) && 
                          Carbon::parse($notification['data']['due_date'])->isPast() ? 1000 : 0;
            return $priorityWeight + $dateWeight;
        })->values();
        
        return response()->json([
            'data' => $sortedNotifications,
            'total' => $sortedNotifications->count(),
            'unread' => $sortedNotifications->whereNull('read_at')->count(),
        ]);
    }

    private function mapNotificationType(string $type): string
    {
        return match($type) {
            'ct' => 'ct_reminder',
            'maintenance' => 'maintenance_reminder',
            'repair' => 'repair',
            'issue' => 'vehicle_status',
            'status_change' => 'vehicle_status',
            default => 'general'
        };
    }

    private function getNotificationTitle(array $notification): string
    {
        return match($notification['type']) {
            'ct' => 'Contrôle technique',
            'maintenance' => 'Maintenance programmée',
            'repair' => 'Réparation urgente',
            'issue' => 'Problème véhicule',
            'status_change' => 'Changement de statut',
            default => 'Notification'
        };
    }

    private function getNotificationMessage(array $notification): string
    {
        $vehicle = $notification['vehicle'] . ' (' . $notification['plate'] . ')';
        
        switch ($notification['type']) {
            case 'ct':
                $dueDate = Carbon::parse($notification['dueDate']);
                $daysDiff = Carbon::now()->diffInDays($dueDate, false);
                
                if ($daysDiff < 0) {
                    return "Contrôle technique expiré depuis " . abs($daysDiff) . " jour(s) pour le véhicule {$vehicle}";
                } elseif ($daysDiff <= 7) {
                    return "Contrôle technique à effectuer dans {$daysDiff} jour(s) pour le véhicule {$vehicle}";
                } else {
                    return "Contrôle technique à programmer pour le véhicule {$vehicle} (échéance : " . $dueDate->format('d/m/Y') . ")";
                }
                
            case 'maintenance':
                $maintenanceType = $this->getMaintenanceTypeLabel($notification['maintenance_type'] ?? 'other');
                $dueDate = Carbon::parse($notification['dueDate']);
                $daysDiff = Carbon::now()->diffInDays($dueDate, false);
                
                if ($daysDiff < 0) {
                    return "Maintenance {$maintenanceType} en retard de " . abs($daysDiff) . " jour(s) pour le véhicule {$vehicle}";
                } elseif ($daysDiff <= 3) {
                    return "Maintenance {$maintenanceType} prévue dans {$daysDiff} jour(s) pour le véhicule {$vehicle}";
                } else {
                    return "Maintenance {$maintenanceType} programmée le " . $dueDate->format('d/m/Y') . " pour le véhicule {$vehicle}";
                }
                
            case 'issue':
            case 'status_change':
                return $notification['message'] . " : {$vehicle}";
                
            default:
                return $notification['message'] ?? "Notification pour le véhicule {$vehicle}";
        }
    }

    private function getMaintenanceTypeLabel(string $type): string
    {
        return match($type) {
            'oil_change' => 'vidange',
            'revision' => 'révision',
            'tires' => 'pneus',
            'brakes' => 'freins',
            'belt' => 'courroie',
            'filters' => 'filtres',
            'other' => 'générale',
            default => 'générale'
        };
    }
    
    /**
     * Get contrôle technique notifications
     */
    private function getControleTechniqueNotifications(int $tenantId, int $userId): \Illuminate\Support\Collection
    {
        $vehicles = Vehicle::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->whereNotNull('next_ct_date')
            ->get();
            
        $notifications = collect();
        $now = Carbon::now();
        
        foreach ($vehicles as $vehicle) {
            $nextCtDate = Carbon::parse($vehicle->next_ct_date);
            $daysDiff = $now->diffInDays($nextCtDate, false);
            
            $status = 'upcoming';
            $priority = 'medium';
            $message = 'Contrôle technique à effectuer';
            
            if ($daysDiff < 0) {
                $status = 'overdue';
                $priority = 'critical';
                $message = 'Contrôle technique expiré';
            } elseif ($daysDiff <= 7) {
                $status = 'urgent';
                $priority = 'high';
                $message = 'Contrôle technique urgent';
            } elseif ($daysDiff <= 30) {
                $priority = 'high';
            }
            
            if ($daysDiff <= 60) { // Afficher seulement les CT dans les 60 prochains jours ou expirés
                $notifications->push([
                    'id' => 'ct_' . $vehicle->id,
                    'type' => 'ct',
                    'vehicle' => $vehicle->marque . ' ' . $vehicle->modele,
                    'plate' => $vehicle->immatriculation,
                    'message' => $message,
                    'dueDate' => $vehicle->next_ct_date,
                    'status' => $status,
                    'priority' => $priority,
                    'created' => $vehicle->created_at->toDateString(),
                    'vehicle_id' => $vehicle->id,
                ]);
            }
        }
        
        return $notifications;
    }
    
    /**
     * Get maintenance notifications
     */
    private function getMaintenanceNotifications(int $tenantId, int $userId): \Illuminate\Support\Collection
    {
        // Récupérer les maintenances programmées pour les véhicules de l'utilisateur
        $maintenances = Maintenance::whereHas('vehicle', function($query) use ($tenantId, $userId) {
                $query->where('tenant_id', $tenantId)
                      ->where('user_id', $userId);
            })
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_date')
            ->get();
            
        $notifications = collect();
        $now = Carbon::now();
        
        foreach ($maintenances as $maintenance) {
            $scheduledDate = Carbon::parse($maintenance->scheduled_date);
            $daysDiff = $now->diffInDays($scheduledDate, false);
            
            $status = 'upcoming';
            $priority = 'medium';
            $message = 'Entretien programmé';
            
            if ($daysDiff < 0) {
                $status = 'overdue';
                $priority = 'high';
                $message = 'Entretien en retard';
            } elseif ($daysDiff <= 3) {
                $status = 'urgent';
                $priority = 'high';
                $message = 'Entretien imminent';
            }
            
            if ($daysDiff <= 30) { // Afficher les maintenances dans les 30 prochains jours
                $notifications->push([
                    'id' => 'maintenance_' . $maintenance->id,
                    'type' => 'maintenance',
                    'vehicle' => $maintenance->vehicle->marque . ' ' . $maintenance->vehicle->modele,
                    'plate' => $maintenance->vehicle->immatriculation,
                    'message' => $message . ' - ' . $maintenance->maintenance_type,
                    'dueDate' => $maintenance->maintenance_date ?? $maintenance->scheduled_date,
                    'status' => $status,
                    'priority' => $priority,
                    'created' => $maintenance->created_at->toDateString(),
                    'vehicle_id' => $maintenance->vehicle_id,
                    'maintenance_id' => $maintenance->id,
                    'maintenance_type' => $maintenance->maintenance_type,
                    'reason' => $maintenance->reason,
                ]);
            }
        }
        
        return $notifications;
    }
    
    /**
     * Get vehicle issue notifications
     */
    private function getVehicleIssueNotifications(int $tenantId, int $userId): \Illuminate\Support\Collection
    {
        // Récupérer les véhicules avec un statut problématique
        $vehicles = Vehicle::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->whereIn('status', ['maintenance', 'broken', 'accident'])
            ->get();
            
        $notifications = collect();
        
        foreach ($vehicles as $vehicle) {
            $priority = match($vehicle->status) {
                'broken' => 'critical',
                'accident' => 'critical', 
                'maintenance' => 'medium',
                default => 'low'
            };
            
            $message = match($vehicle->status) {
                'broken' => 'Véhicule en panne',
                'accident' => 'Véhicule accidenté',
                'maintenance' => 'Véhicule en maintenance',
                default => 'Problème véhicule'
            };
            
            $notifications->push([
                'id' => 'issue_' . $vehicle->id,
                'type' => 'issue',
                'vehicle' => $vehicle->marque . ' ' . $vehicle->modele,
                'plate' => $vehicle->immatriculation,
                'message' => $message,
                'dueDate' => $vehicle->updated_at->toDateString(),
                'status' => $vehicle->status === 'broken' || $vehicle->status === 'accident' ? 'urgent' : 'upcoming',
                'priority' => $priority,
                'created' => $vehicle->updated_at->toDateString(),
                'vehicle_id' => $vehicle->id,
            ]);
        }
        
        return $notifications;
    }
    
    /**
     * Get vehicle status change notifications
     */
    private function getVehicleStatusNotifications(int $tenantId, int $userId): \Illuminate\Support\Collection
    {
        $notifications = collect();
        
        // Récupérer les notifications de changement de statut des 7 derniers jours
        $statusNotifications = VehicleStatusNotification::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->with('vehicle')
            ->latest()
            ->get();
            
        foreach ($statusNotifications as $statusNotification) {
            // Déterminer le type de notification selon le nouveau statut
            $notificationType = match($statusNotification->new_status) {
                'en_maintenance' => 'maintenance_reminder',
                'en_reparation' => 'repair',
                'en_service' => 'vehicle_status',
                'hors_service' => 'vehicle_status',
                default => 'vehicle_status'
            };
            
            $notifications->push([
                'id' => 'status_' . $statusNotification->id,
                'type' => $notificationType,
                'vehicle' => $statusNotification->vehicle->marque . ' ' . $statusNotification->vehicle->modele,
                'plate' => $statusNotification->vehicle->immatriculation,
                'message' => $statusNotification->message,
                'dueDate' => $statusNotification->created_at->toDateString(),
                'status' => $statusNotification->new_status === 'hors_service' ? 'urgent' : 
                           ($statusNotification->new_status === 'en_maintenance' ? 'upcoming' : 'info'),
                'priority' => $statusNotification->priority,
                'created' => $statusNotification->created_at->toDateString(),
                'vehicle_id' => $statusNotification->vehicle_id,
                'old_status' => $statusNotification->old_status,
                'new_status' => $statusNotification->new_status,
                'reason' => $statusNotification->reason,
            ]);
        }
        
        return $notifications;
    }
    
    /**
     * Mark notification as read/handled
     */
    public function markAsRead(Request $request, string $notificationId): JsonResponse
    {
        $tenant = app('currentTenant');
        $user = $request->user();
        
        // Extraire le type et l'id de la notification
        if (str_starts_with($notificationId, 'status_')) {
            $realId = str_replace('status_', '', $notificationId);
            $notification = VehicleStatusNotification::where('id', $realId)
                ->where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->first();
                
            if ($notification) {
                $notification->update(['is_read' => true]);
                return response()->json([
                    'success' => true,
                    'message' => 'Notification marquée comme lue',
                    'notification_id' => $notificationId
                ]);
            }
        }
        
        // Pour les autres types de notifications (CT, maintenance, etc.)
        // On peut juste retourner un succès pour l'instant
        return response()->json([
            'success' => true,
            'message' => 'Notification marquée comme lue',
            'notification_id' => $notificationId
        ]);
    }
    
    /**
     * Get notification counts for dashboard
     */
    public function getCounts(Request $request): JsonResponse
    {
        $tenant = app('currentTenant');
        $user = $request->user();
        
        // Utiliser la même logique que index() mais juste compter
        $notifications = collect();
        
        $ctNotifications = $this->getControleTechniqueNotifications($tenant->id, $user->id);
        $notifications = $notifications->concat($ctNotifications);
        
        $maintenanceNotifications = $this->getMaintenanceNotifications($tenant->id, $user->id);
        $notifications = $notifications->concat($maintenanceNotifications);
        
        $issueNotifications = $this->getVehicleIssueNotifications($tenant->id, $user->id);
        $notifications = $notifications->concat($issueNotifications);
        
        $statusNotifications = $this->getVehicleStatusNotifications($tenant->id, $user->id);
        $notifications = $notifications->concat($statusNotifications);
        
        return response()->json([
            'total' => $notifications->count(),
            'unread' => $notifications->count(), // Toutes les notifications sont considérées comme non lues pour l'instant
            'urgent' => $notifications->whereIn('status', ['urgent', 'overdue'])->count(),
            'critical' => $notifications->where('priority', 'critical')->count(),
            'upcoming' => $notifications->where('status', 'upcoming')->count(),
        ]);
    }
}