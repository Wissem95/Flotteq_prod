import React, { useEffect, useState } from "react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { 
  Car, 
  Calendar, 
  AlertTriangle, 
  Euro,
  TrendingUp,
  BarChart3,
  ArrowUpDown
} from "lucide-react";

// Composants existants et nouveaux
import FleetStatusCard from "./FleetStatusCard";
import UpcomingMaintenance from "./UpcomingMaintenance";
import PriorityAlerts from "./PriorityAlerts";
import ActivityChart from "./ActivityChart";
import StatusDistribution from "./StatusDistribution";
import RecentEvents from "./RecentEvents";
import FinancialStatusDashboard from "./FinancialStatusDashboard";
import TransactionsPage from "./TransactionsPage";

// Services
import { api } from "@/lib/api";
import { notificationService } from "@/services/notificationService";
import { fetchVehicles } from "@/services/vehicleService";
import { toast } from "@/hooks/use-toast";

interface DashboardStats {
  overview: {
    total_vehicles: number;
    active_vehicles: number;
    inactive_vehicles: number;
    total_users: number;
    new_vehicles_this_month: number;
    new_users_this_month: number;
    average_vehicle_age: number;
  };
  distributions: {
    vehicles_by_status: Record<string, number>;
    vehicles_by_fuel: Record<string, number>;
  };
}

interface NotificationCounts {
  total: number;
  urgent: number;
  upcoming: number;
  critical: number;
}

interface MaintenanceItem {
  id: number;
  vehicle_name: string;
  plate: string;
  type: string;
  scheduled_date: string;
  status: string;
}

interface AlertItem {
  id: number;
  vehicle_name: string;
  plate: string;
  issue: string;
  severity: 'high' | 'medium' | 'low';
  status: string;
}

interface Event {
  id: string;
  type: 'vehicle_added' | 'maintenance_completed' | 'alert_created' | 'ct_scheduled';
  title: string;
  description: string;
  timestamp: string;
  vehicleName?: string;
  vehiclePlate?: string;
}

const EnhancedDashboard: React.FC = () => {
  const [activeTab, setActiveTab] = useState("fleet");
  const [dashboardStats, setDashboardStats] = useState<DashboardStats | null>(null);
  const [notificationCounts, setNotificationCounts] = useState<NotificationCounts | null>(null);
  const [upcomingMaintenances, setUpcomingMaintenances] = useState<MaintenanceItem[]>([]);
  const [vehicleIssues, setVehicleIssues] = useState<AlertItem[]>([]);
  const [vehicles, setVehicles] = useState<any[]>([]);
  const [activityData, setActivityData] = useState<any[]>([]);
  const [statusData, setStatusData] = useState<any[]>([]);
  const [events, setEvents] = useState<Event[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    // Vérification de l'authentification au chargement du dashboard
    const user = localStorage.getItem("user");
    const token = localStorage.getItem("token");
    
    if (!user || !token) {
      window.location.href = "/login";
      return;
    }
    
    loadDashboardData();
  }, []);

  const loadDashboardData = async () => {
    try {
      setIsLoading(true);
      
      // Charger les données de base
      const [statsResponse, notificationCountsResponse, maintenancesResponse, vehiclesData] = await Promise.all([
        api.get('/analytics/dashboard'),
        notificationService.getNotificationCounts(),
        api.get('/maintenances?status=in_progress&limit=7'),
        fetchVehicles(),
      ]);

      setDashboardStats(statsResponse.data);
      setNotificationCounts(notificationCountsResponse);
      setVehicles(vehiclesData);
      
      // Convertir les maintenances en format attendu
      const maintenances = (Array.isArray(maintenancesResponse.data) ? maintenancesResponse.data : maintenancesResponse.data.data || [])
        .map((maintenance: any) => {
          const vehicle = maintenance.vehicle;
          return {
            id: maintenance.id,
            vehicle_name: `${vehicle?.marque || ''} ${vehicle?.modele || ''}`,
            plate: vehicle?.plaque || vehicle?.immatriculation,
            type: maintenance.type || maintenance.maintenance_type || 'Entretien général',
            scheduled_date: maintenance.date || maintenance.maintenance_date,
            status: maintenance.status
          };
        })
        .filter((maintenance: any) => {
          // Filtrer pour les 7 prochains jours
          const scheduledDate = new Date(maintenance.scheduled_date);
          const today = new Date();
          const nextWeek = new Date();
          nextWeek.setDate(today.getDate() + 7);
          return scheduledDate >= today && scheduledDate <= nextWeek;
        })
        .slice(0, 5); // Limiter à 5 éléments
      
      setUpcomingMaintenances(maintenances);

      // Charger les alertes
      const notificationsResponse = await notificationService.getNotifications();
      const issues = notificationsResponse.notifications
        .filter(n => n.type === 'issue' || n.type === 'alert')
        .slice(0, 3)
        .map(n => ({
          id: parseInt(n.id.replace(/\D/g, '')),
          vehicle_name: n.vehicle || 'Véhicule inconnu',
          plate: n.plate || '',
          issue: n.message,
          severity: (n.priority === 'critical' ? 'high' : n.priority === 'high' ? 'medium' : 'low') as 'high' | 'medium' | 'low',
          status: n.status
        }));
      
      setVehicleIssues(issues);

      // Générer des données d'activité fictives (6 derniers mois)
      const activityData = [];
      for (let i = 5; i >= 0; i--) {
        const date = new Date();
        date.setMonth(date.getMonth() - i);
        activityData.push({
          month: date.toLocaleDateString('fr-FR', { month: 'short' }),
          vehicles: Math.max(0, vehiclesData.length - i + Math.floor(Math.random() * 3)),
          maintenances: Math.floor(Math.random() * 10) + 2,
        });
      }
      setActivityData(activityData);

      // Préparer les données de statut
      const statusMapping = [
        { status: "En service", count: vehiclesData.filter(v => v.status === "active").length, color: "#10B981" },
        { status: "Hors service", count: vehiclesData.filter(v => v.status === "hors_service").length, color: "#EF4444" },
        { status: "En maintenance", count: vehiclesData.filter(v => v.status === "en_maintenance" || v.status === "en_reparation").length, color: "#F59E0B" },
        { status: "À inspecter", count: notificationCountsResponse.upcoming || 0, color: "#3B82F6" },
      ];
      setStatusData(statusMapping);

      // Générer des événements récents
      const recentEvents: Event[] = [
        {
          id: '1',
          type: 'vehicle_added',
          title: 'Nouveau véhicule ajouté',
          description: 'Mercedes Classe A ajoutée à la flotte',
          timestamp: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(),
          vehicleName: 'Mercedes Classe A',
          vehiclePlate: 'AB-123-CD'
        },
        {
          id: '2',
          type: 'maintenance_completed',
          title: 'Entretien terminé',
          description: 'Vidange effectuée sur BMW Serie 3',
          timestamp: new Date(Date.now() - 5 * 60 * 60 * 1000).toISOString(),
          vehicleName: 'BMW Serie 3',
          vehiclePlate: 'EF-456-GH'
        },
        {
          id: '3',
          type: 'ct_scheduled',
          title: 'Contrôle technique programmé',
          description: 'CT prévu pour Audi A4 le 15/08/2025',
          timestamp: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000).toISOString(),
          vehicleName: 'Audi A4',
          vehiclePlate: 'IJ-789-KL'
        },
        {
          id: '4',
          type: 'alert_created',
          title: 'Alerte créée',
          description: 'Problème de freinage détecté',
          timestamp: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000).toISOString(),
          vehicleName: 'Renault Clio',
          vehiclePlate: 'MN-012-PQ'
        }
      ];
      setEvents(recentEvents);

    } catch (error) {
      console.error('Erreur lors du chargement des données du dashboard:', error);
      toast({
        title: "Erreur",
        description: "Impossible de charger les données du dashboard",
        variant: "destructive",
      });
    } finally {
      setIsLoading(false);
    }
  };

  const handleViewAllMaintenances = () => {
    window.location.href = '/maintenances';
  };

  const handleViewAllAlerts = () => {
    window.location.href = '/notifications';
  };

  const handleTreatAlert = (alertId: number) => {
    toast({
      title: "Alerte traitée",
      description: `Alerte ${alertId} marquée comme traitée`,
    });
    // Recharger les données
    loadDashboardData();
  };

  // Calculs dérivés des véhicules
  const valideVehicles = vehicles.filter(v => v.status === "active");
  const invalideVehicles = vehicles.filter(v => v.status === "hors_service");
  const maintenanceVehicles = vehicles.filter(v => v.status === "en_maintenance" || v.status === "en_reparation");

  return (
    <div className="space-y-6">
      <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
        <TabsList className="grid grid-cols-3 w-full max-w-md">
          <TabsTrigger value="fleet" className="flex items-center space-x-2">
            <Car size={16} />
            <span>État de la flotte</span>
          </TabsTrigger>
          <TabsTrigger value="finances" className="flex items-center space-x-2">
            <Euro size={16} />
            <span>État financier</span>
          </TabsTrigger>
          <TabsTrigger value="transactions" className="flex items-center space-x-2">
            <ArrowUpDown size={16} />
            <span>Achats/Reventes</span>
          </TabsTrigger>
        </TabsList>

        {/* Section État de la flotte améliorée */}
        <TabsContent value="fleet" className="space-y-6">
          {/* Métriques principales */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <FleetStatusCard
              title="Véhicules"
              description="État de votre flotte"
              value={dashboardStats?.overview.total_vehicles || vehicles.length}
              subtitle={
                <div>
                  <span className="text-green-500 font-medium">
                    {dashboardStats?.overview.active_vehicles || valideVehicles.length} actif(s)
                  </span>
                  {((dashboardStats?.overview.total_vehicles || vehicles.length) > (dashboardStats?.overview.active_vehicles || valideVehicles.length)) && (
                    <span className="text-orange-500 font-medium ml-2">
                      • {(dashboardStats?.overview.total_vehicles || vehicles.length) - (dashboardStats?.overview.active_vehicles || valideVehicles.length)} en maintenance
                    </span>
                  )}
                </div>
              }
              progress={
                vehicles.length > 0 
                  ? Math.round((valideVehicles.length / vehicles.length) * 100)
                  : dashboardStats?.overview.total_vehicles 
                    ? Math.round((dashboardStats.overview.active_vehicles / dashboardStats.overview.total_vehicles) * 100) 
                    : 0
              }
              icon={Car}
              isLoading={isLoading}
            />

            <FleetStatusCard
              title="Contrôles techniques"
              description="Prochaines échéances"
              value={notificationCounts?.upcoming || 0}
              subtitle={<span className="text-amber-500 font-medium">{notificationCounts?.urgent || 0} urgent(es)</span>}
              progress={notificationCounts?.total ? 
                Math.round((notificationCounts.urgent / notificationCounts.total) * 100) : 0}
              icon={Calendar}
              isLoading={isLoading}
            />

            <FleetStatusCard
              title="Alertes"
              description="Problèmes à résoudre"
              value={notificationCounts?.total || 0}
              subtitle={<span className="text-red-500 font-medium">{notificationCounts?.critical || 0} critique(s)</span>}
              progress={notificationCounts?.total ? 
                Math.round((notificationCounts.critical / notificationCounts.total) * 100) : 0}
              icon={AlertTriangle}
              isLoading={isLoading}
            />
          </div>

          {/* Planning et alertes */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <UpcomingMaintenance
              maintenances={upcomingMaintenances}
              isLoading={isLoading}
              onViewAll={handleViewAllMaintenances}
            />

            <PriorityAlerts
              alerts={vehicleIssues}
              isLoading={isLoading}
              onViewAll={handleViewAllAlerts}
              onTreatAlert={handleTreatAlert}
            />
          </div>

          {/* Graphiques et analyses */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <ActivityChart
              data={activityData}
              isLoading={isLoading}
            />

            <StatusDistribution
              data={statusData}
              isLoading={isLoading}
            />
          </div>

          {/* Statut de la flotte détaillé */}
          <Card>
            <CardHeader>
              <CardTitle>Statut de la flotte</CardTitle>
              <CardDescription>Vue d'ensemble détaillée des véhicules</CardDescription>
            </CardHeader>
            <CardContent>
              {isLoading ? (
                <div className="text-center py-8 text-slate-500">
                  Chargement du statut de la flotte...
                </div>
              ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                  {statusData.map((item, index) => (
                    <div key={index} className="p-4 rounded-lg border border-slate-100 flex items-center">
                      <div className="h-12 w-12 rounded-full flex items-center justify-center" style={{ backgroundColor: `${item.color}20`, color: item.color }}>
                        {item.status === "En service" && <Car size={20} />}
                        {item.status === "Hors service" && <AlertTriangle size={20} />}
                        {item.status === "En maintenance" && <Calendar size={20} />}
                        {item.status === "À inspecter" && <Calendar size={20} />}
                      </div>
                      <div className="ml-4">
                        <div className="text-2xl font-bold">{item.count}</div>
                        <div className="text-sm text-slate-500">{item.status}</div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>

          {/* Flux d'activité */}
          <RecentEvents
            events={events}
            isLoading={isLoading}
          />
        </TabsContent>

        {/* Section État financier */}
        <TabsContent value="finances">
          <FinancialStatusDashboard />
        </TabsContent>

        {/* Section Achats/Reventes */}
        <TabsContent value="transactions">
          <TransactionsPage />
        </TabsContent>
      </Tabs>
    </div>
  );
};

export default EnhancedDashboard;