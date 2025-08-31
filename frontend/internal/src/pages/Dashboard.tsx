// Internal Dashboard - Vue d'ensemble de la plateforme FlotteQ

import React, { useState, useEffect } from "react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Progress } from "@/components/ui/progress";
import { 
  AlertTriangle, 
  Car, 
  CheckCircle, 
  Clock, 
  Calendar,
  ArrowRight,
  Users,
  Building2
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { toast } from "@/hooks/use-toast";

interface DashboardStats {
  total_tenants: number;
  active_tenants: number;
  total_vehicles: number;
  active_vehicles: number;
  total_users: number;
  active_users: number;
  pending_maintenances: number;
  upcoming_technical_controls: number;
  critical_alerts: number;
  total_alerts: number;
}

interface UpcomingMaintenance {
  id: number;
  vehicle_name: string;
  license_plate: string;
  maintenance_type: string;
  scheduled_date: string;
  tenant_name: string;
  status: 'pending' | 'scheduled' | 'completed';
}

interface SystemAlert {
  id: number;
  title: string;
  description: string;
  severity: 'low' | 'medium' | 'high' | 'critical';
  category: string;
  created_at: string;
  tenant_name?: string;
}

const Dashboard: React.FC = () => {
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [upcomingMaintenances, setUpcomingMaintenances] = useState<UpcomingMaintenance[]>([]);
  const [systemAlerts, setSystemAlerts] = useState<SystemAlert[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadDashboardData();
  }, []);

  const loadDashboardData = async () => {
    try {
      setLoading(true);
      
      const [statsResponse, maintenancesResponse, alertsResponse] = await Promise.all([
        fetch('/api/internal/dashboard/stats'),
        fetch('/api/internal/dashboard/upcoming-maintenances?limit=5'),
        fetch('/api/internal/dashboard/alerts?limit=5')
      ]);
      
      if (statsResponse.ok) {
        const statsData = await statsResponse.json();
        setStats(statsData);
      }
      
      if (maintenancesResponse.ok) {
        const maintenancesData = await maintenancesResponse.json();
        setUpcomingMaintenances(maintenancesData.data || []);
      }
      
      if (alertsResponse.ok) {
        const alertsData = await alertsResponse.json();
        setSystemAlerts(alertsData.data || []);
      }
    } catch (error) {
      console.error('Erreur lors du chargement du dashboard:', error);
      toast({
        title: "Erreur",
        description: "Impossible de charger les données du tableau de bord",
        variant: "destructive"
      });
    } finally {
      setLoading(false);
    }
  };

  const getSeverityColor = (severity: string) => {
    switch (severity) {
      case 'critical': return 'text-red-600 bg-red-100';
      case 'high': return 'text-orange-600 bg-orange-100';
      case 'medium': return 'text-yellow-600 bg-yellow-100';
      case 'low': return 'text-blue-600 bg-blue-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'pending': return <Clock size={20} className="text-amber-600" />;
      case 'scheduled': return <Calendar size={20} className="text-blue-600" />;
      case 'completed': return <CheckCircle size={20} className="text-green-600" />;
      default: return <Clock size={20} className="text-gray-600" />;
    }
  };

  if (loading) {
    return (
      <div className="space-y-6">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {[1, 2, 3].map((i) => (
            <Card key={i} className="animate-pulse">
              <CardHeader className="pb-2">
                <div className="h-4 bg-gray-200 rounded w-3/4"></div>
                <div className="h-3 bg-gray-200 rounded w-1/2"></div>
              </CardHeader>
              <CardContent>
                <div className="h-8 bg-gray-200 rounded w-1/3 mb-2"></div>
                <div className="h-2 bg-gray-200 rounded"></div>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-lg flex items-center">
              <Building2 className="mr-2 text-blue-600" size={20} />
              Tenants
            </CardTitle>
            <CardDescription>Entreprises clientes</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-bold">{stats?.active_tenants || 0}</div>
            <div className="mt-2 text-sm text-muted-foreground">
              <span className="text-blue-500 font-medium">{stats?.total_tenants || 0}</span> au total
            </div>
            <Progress 
              value={stats ? Math.round((stats.active_tenants / Math.max(stats.total_tenants, 1)) * 100) : 0} 
              className="h-2 mt-2" 
            />
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-lg flex items-center">
              <Car className="mr-2 text-green-600" size={20} />
              Véhicules
            </CardTitle>
            <CardDescription>Flotte totale</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-bold">{stats?.active_vehicles || 0}</div>
            <div className="mt-2 text-sm text-muted-foreground">
              <span className="text-green-500 font-medium">{stats?.total_vehicles || 0}</span> enregistrés
            </div>
            <Progress 
              value={stats ? Math.round((stats.active_vehicles / Math.max(stats.total_vehicles, 1)) * 100) : 0} 
              className="h-2 mt-2" 
            />
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-lg flex items-center">
              <Users className="mr-2 text-purple-600" size={20} />
              Utilisateurs
            </CardTitle>
            <CardDescription>Base utilisateurs</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-bold">{stats?.active_users || 0}</div>
            <div className="mt-2 text-sm text-muted-foreground">
              <span className="text-purple-500 font-medium">{stats?.total_users || 0}</span> inscrits
            </div>
            <Progress 
              value={stats ? Math.round((stats.active_users / Math.max(stats.total_users, 1)) * 100) : 0} 
              className="h-2 mt-2" 
            />
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-lg flex items-center">
              <AlertTriangle className="mr-2 text-red-600" size={20} />
              Alertes Système
            </CardTitle>
            <CardDescription>Monitoring plateforme</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-bold">{stats?.critical_alerts || 0}</div>
            <div className="mt-2 text-sm text-muted-foreground">
              <span className="text-red-500 font-medium">{stats?.total_alerts || 0}</span> au total
            </div>
            <Progress 
              value={stats?.total_alerts ? Math.min((stats.critical_alerts / stats.total_alerts) * 100, 100) : 0} 
              className="h-2 mt-2" 
            />
          </CardContent>
        </Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Maintenances Programmées</CardTitle>
            <CardDescription>Prochaines maintenances dans la plateforme</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {upcomingMaintenances.length > 0 ? (
                upcomingMaintenances.map((maintenance) => (
                  <div key={maintenance.id} className="flex items-center p-3 rounded-md border border-slate-100 hover:bg-slate-50 transition-colors">
                    <div className="h-10 w-10 rounded-full flex items-center justify-center bg-blue-100">
                      {getStatusIcon(maintenance.status)}
                    </div>
                    <div className="ml-4 flex-1">
                      <p className="font-medium">{maintenance.vehicle_name}</p>
                      <p className="text-sm text-slate-500">{maintenance.license_plate} • {maintenance.maintenance_type}</p>
                      <p className="text-xs text-slate-400">{maintenance.tenant_name}</p>
                    </div>
                    <div className="text-sm font-medium text-slate-500">
                      {new Date(maintenance.scheduled_date).toLocaleDateString('fr-FR')}
                    </div>
                  </div>
                ))
              ) : (
                <div className="text-center py-8 text-slate-500">
                  <Calendar className="mx-auto mb-2" size={32} />
                  <p>Aucune maintenance programmée</p>
                </div>
              )}
              {upcomingMaintenances.length > 0 && (
                <Button variant="ghost" className="w-full border border-dashed mt-2 text-slate-500 hover:text-blue-600">
                  Voir toutes les maintenances
                  <ArrowRight className="ml-2" size={16} />
                </Button>
              )}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Alertes Système Récentes</CardTitle>
            <CardDescription>Incidents et notifications importantes</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {systemAlerts.length > 0 ? (
                systemAlerts.map((alert) => (
                  <div key={alert.id} className="flex items-center p-3 rounded-md border border-slate-100 hover:bg-slate-50 transition-colors">
                    <div className={`h-10 w-10 rounded-full flex items-center justify-center ${getSeverityColor(alert.severity)}`}>
                      <AlertTriangle size={20} />
                    </div>
                    <div className="ml-4 flex-1">
                      <p className="font-medium">{alert.title}</p>
                      <p className="text-sm text-slate-500">{alert.description}</p>
                      {alert.tenant_name && (
                        <p className="text-xs text-slate-400">{alert.tenant_name}</p>
                      )}
                    </div>
                    <div className="text-xs text-slate-400">
                      {new Date(alert.created_at).toLocaleDateString('fr-FR')}
                    </div>
                  </div>
                ))
              ) : (
                <div className="text-center py-8 text-slate-500">
                  <CheckCircle className="mx-auto mb-2 text-green-500" size={32} />
                  <p>Aucune alerte système</p>
                  <p className="text-sm text-slate-400">Tout fonctionne normalement</p>
                </div>
              )}
              {systemAlerts.length > 0 && (
                <Button variant="ghost" className="w-full border border-dashed mt-2 text-slate-500 hover:text-blue-600">
                  Voir toutes les alertes
                  <ArrowRight className="ml-2" size={16} />
                </Button>
              )}
            </div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Résumé Plateforme</CardTitle>
          <CardDescription>Vue d'ensemble des métriques clés</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div className="p-4 rounded-lg border border-slate-100 flex items-center">
              <div className="h-12 w-12 rounded-full flex items-center justify-center bg-blue-100 text-blue-600">
                <Building2 size={20} />
              </div>
              <div className="ml-4">
                <div className="text-2xl font-bold">{stats?.active_tenants || 0}</div>
                <div className="text-sm text-slate-500">Tenants actifs</div>
              </div>
            </div>
            
            <div className="p-4 rounded-lg border border-slate-100 flex items-center">
              <div className="h-12 w-12 rounded-full flex items-center justify-center bg-green-100 text-green-600">
                <Car size={20} />
              </div>
              <div className="ml-4">
                <div className="text-2xl font-bold">{stats?.active_vehicles || 0}</div>
                <div className="text-sm text-slate-500">Véhicules actifs</div>
              </div>
            </div>
            
            <div className="p-4 rounded-lg border border-slate-100 flex items-center">
              <div className="h-12 w-12 rounded-full flex items-center justify-center bg-purple-100 text-purple-600">
                <Users size={20} />
              </div>
              <div className="ml-4">
                <div className="text-2xl font-bold">{stats?.active_users || 0}</div>
                <div className="text-sm text-slate-500">Utilisateurs actifs</div>
              </div>
            </div>
            
            <div className="p-4 rounded-lg border border-slate-100 flex items-center">
              <div className="h-12 w-12 rounded-full flex items-center justify-center bg-orange-100 text-orange-600">
                <Clock size={20} />
              </div>
              <div className="ml-4">
                <div className="text-2xl font-bold">{stats?.pending_maintenances || 0}</div>
                <div className="text-sm text-slate-500">Maintenances en attente</div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default Dashboard;
