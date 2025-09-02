// Internal Dashboard - Vue d'ensemble hybride global/tenant FlotteQ

import React from "react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { 
  AlertTriangle, 
  Car, 
  CheckCircle, 
  Clock, 
  Calendar,
  ArrowRight,
  Users,
  Building2,
  Wrench,
  RefreshCw
} from "lucide-react";

// Nouveaux composants hybrides
import { useDashboardScope } from "@/hooks/useDashboardScope";
import TenantScopeSelector from "@/components/dashboard/TenantScopeSelector";
import DashboardStats from "@/components/dashboard/DashboardStats";

const Dashboard: React.FC = () => {
  // Utilisation du hook hybride
  const {
    selectedTenantId,
    setSelectedTenantId,
    stats,
    upcomingMaintenances,
    systemAlerts,
    availableTenants,
    loading,
    tenantsLoading,
    error,
    refreshData
  } = useDashboardScope();

  // Helper functions pour la compatibilité
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

  // Helper pour obtenir le titre de page dynamique
  const getPageTitle = () => {
    if (!stats) return 'Dashboard';
    
    if (stats.scope === 'global') {
      return 'Dashboard Global - Vue d\'ensemble de la plateforme';
    } else {
      return `Dashboard - ${stats.tenant_info?.name}`;
    }
  };

  // Gestion des erreurs
  if (error) {
    return (
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h1 className="text-3xl font-bold">Dashboard</h1>
          <Button onClick={refreshData} variant="outline" className="gap-2">
            <RefreshCw className="h-4 w-4" />
            Réessayer
          </Button>
        </div>
        <Card>
          <CardContent className="py-8 text-center">
            <AlertTriangle className="w-12 h-12 text-red-500 mx-auto mb-4" />
            <h3 className="text-lg font-semibold mb-2">Erreur de chargement</h3>
            <p className="text-gray-600 mb-4">{error}</p>
            <Button onClick={refreshData}>Réessayer</Button>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header avec sélecteur de scope */}
      <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h1 className="text-3xl font-bold">{getPageTitle()}</h1>
          <p className="text-muted-foreground">
            {stats?.scope === 'global' 
              ? 'Métriques et alertes de toute la plateforme FlotteQ'
              : `Données spécifiques au tenant ${stats?.tenant_info?.name}`
            }
          </p>
        </div>
        
        <div className="flex items-center gap-4">
          <Button 
            onClick={refreshData} 
            variant="outline" 
            size="sm" 
            className="gap-2"
            disabled={loading}
          >
            <RefreshCw className={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
            Actualiser
          </Button>
          
          <TenantScopeSelector
            value={selectedTenantId}
            onChange={setSelectedTenantId}
            tenants={availableTenants}
            loading={tenantsLoading}
          />
        </div>
      </div>

      {/* Composant de statistiques réutilisable */}
      <DashboardStats data={stats} loading={loading} />

      {/* Section des maintenances et alertes */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Maintenances à venir */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Wrench className="h-5 w-5" />
              Prochaines Maintenances
              {upcomingMaintenances.length > 0 && (
                <Badge variant="secondary">{upcomingMaintenances.length}</Badge>
              )}
            </CardTitle>
            <CardDescription>
              {stats?.scope === 'global' 
                ? 'Maintenances programmées dans tous les tenants'
                : 'Maintenances programmées pour ce tenant'
              }
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {loading ? (
                <div className="space-y-3">
                  {[1, 2, 3].map((i) => (
                    <div key={i} className="animate-pulse">
                      <div className="flex items-center gap-3">
                        <div className="h-10 w-10 bg-gray-200 rounded-full"></div>
                        <div className="flex-1">
                          <div className="h-4 bg-gray-200 rounded w-3/4"></div>
                          <div className="h-3 bg-gray-200 rounded w-1/2 mt-1"></div>
                        </div>
                        <div className="h-4 bg-gray-200 rounded w-20"></div>
                      </div>
                    </div>
                  ))}
                </div>
              ) : upcomingMaintenances.length > 0 ? (
                upcomingMaintenances.map((maintenance) => (
                  <div key={maintenance.id} className="flex items-center p-3 rounded-md border border-slate-100 hover:bg-slate-50 transition-colors">
                    <div className="h-10 w-10 rounded-full flex items-center justify-center bg-blue-100">
                      {getStatusIcon(maintenance.status)}
                    </div>
                    <div className="ml-4 flex-1">
                      <p className="font-medium">{maintenance.vehicle_name}</p>
                      <p className="text-sm text-slate-500">{maintenance.license_plate} • {maintenance.maintenance_type}</p>
                      {stats?.scope === 'global' && maintenance.tenant_name && (
                        <p className="text-xs text-slate-400">{maintenance.tenant_name}</p>
                      )}
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
                  <p className="text-sm text-slate-400">Tout est à jour</p>
                </div>
              )}
            </div>
          </CardContent>
        </Card>

        {/* Alertes système */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <AlertTriangle className="h-5 w-5" />
              Alertes Système
              {systemAlerts.length > 0 && (
                <Badge variant="destructive">{systemAlerts.length}</Badge>
              )}
            </CardTitle>
            <CardDescription>
              {stats?.scope === 'global' 
                ? 'Incidents critiques de tous les tenants'
                : 'Incidents critiques pour ce tenant'
              }
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {loading ? (
                <div className="space-y-3">
                  {[1, 2, 3].map((i) => (
                    <div key={i} className="animate-pulse">
                      <div className="flex items-center gap-3">
                        <div className="h-10 w-10 bg-gray-200 rounded-full"></div>
                        <div className="flex-1">
                          <div className="h-4 bg-gray-200 rounded w-3/4"></div>
                          <div className="h-3 bg-gray-200 rounded w-1/2 mt-1"></div>
                        </div>
                        <div className="h-4 bg-gray-200 rounded w-20"></div>
                      </div>
                    </div>
                  ))}
                </div>
              ) : systemAlerts.length > 0 ? (
                systemAlerts.map((alert) => (
                  <div key={alert.id} className="flex items-center p-3 rounded-md border border-slate-100 hover:bg-slate-50 transition-colors">
                    <div className={`h-10 w-10 rounded-full flex items-center justify-center ${getSeverityColor(alert.severity)}`}>
                      <AlertTriangle size={20} />
                    </div>
                    <div className="ml-4 flex-1">
                      <p className="font-medium">{alert.title}</p>
                      <p className="text-sm text-slate-500">{alert.description}</p>
                      {stats?.scope === 'global' && alert.tenant_name && (
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
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Actions rapides */}
      <Card>
        <CardHeader>
          <CardTitle>Actions Rapides</CardTitle>
          <CardDescription>
            Navigation vers les sections principales
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <Button variant="outline" className="h-20 flex-col gap-2">
              <Building2 className="h-6 w-6" />
              <span className="text-sm">Gestion Tenants</span>
            </Button>
            <Button variant="outline" className="h-20 flex-col gap-2">
              <Users className="h-6 w-6" />
              <span className="text-sm">Utilisateurs</span>
            </Button>
            <Button variant="outline" className="h-20 flex-col gap-2">
              <Car className="h-6 w-6" />
              <span className="text-sm">Véhicules</span>
            </Button>
            <Button variant="outline" className="h-20 flex-col gap-2">
              <Wrench className="h-6 w-6" />
              <span className="text-sm">Maintenance</span>
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default Dashboard;
