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
        
        // Trier par priorité et date
        $sortedNotifications = $notifications->sortByDesc(function($notification) {
            $priorityWeight = match($notification['priority']) {
                'critical' => 4,
                'high' => 3,
                'medium' => 2,
                'low' => 1,
                default => 0
            };
            
            $dateWeight = Carbon::parse($notification['dueDate'])->isPast() ? 1000 : 0;
            return $priorityWeight + $dateWeight;
        })->values();
        
        return response()->json([
            'notifications' => $sortedNotifications,
            'total' => $sortedNotifications->count(),
            'urgent' => $sortedNotifications->where('status', 'urgent')->count(),
            'overdue' => $sortedNotifications->where('status', 'overdue')->count(),
        ]);
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
                    'message' => $message . ' - ' . $maintenance->type,
                    'dueDate' => $maintenance->scheduled_date,
                    'status' => $status,
                    'priority' => $priority,
                    'created' => $maintenance->created_at->toDateString(),
                    'vehicle_id' => $maintenance->vehicle_id,
                    'maintenance_id' => $maintenance->id,
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
            $notifications->push([
                'id' => 'status_' . $statusNotification->id,
                'type' => 'status_change',
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
            'urgent' => $notifications->whereIn('status', ['urgent', 'overdue'])->count(),
            'critical' => $notifications->where('priority', 'critical')->count(),
            'upcoming' => $notifications->where('status', 'upcoming')->count(),
        ]);
    }
}