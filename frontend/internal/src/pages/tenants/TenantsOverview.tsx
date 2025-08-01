import React from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Building2, Users, TrendingUp, AlertCircle, CheckCircle, Clock } from 'lucide-react';

interface Tenant {
  id: number;
  name: string;
  domain: string;
  status: 'active' | 'inactive' | 'suspended';
  users_count: number;
  vehicles_count: number;
  subscription_plan: string;
  created_at: string;
  last_activity: string;
}

const TenantsOverview: React.FC = () => {
  // Mock data - replace with real API calls
  const tenants: Tenant[] = [
    {
      id: 1,
      name: 'FlotteQ Demo',
      domain: 'transexpress.local',
      status: 'active',
      users_count: 2,
      vehicles_count: 18,
      subscription_plan: 'Professional',
      created_at: '2025-07-31',
      last_activity: '2025-07-31'
    },
    {
      id: 2,
      name: 'LogiTech Solutions',
      domain: 'logitech-solutions.local',
      status: 'active',
      users_count: 1,
      vehicles_count: 4,
      subscription_plan: 'Starter',
      created_at: '2025-07-31',
      last_activity: '2025-07-31'
    },
    {
      id: 3,
      name: 'Médical Services Plus',
      domain: 'medical-services.local',
      status: 'active',
      users_count: 2,
      vehicles_count: 12,
      subscription_plan: 'Professional',
      created_at: '2025-07-31',
      last_activity: '2025-07-31'
    }
  ];

  const getStatusBadge = (status: string) => {
    const variants = {
      active: { variant: 'default' as const, icon: CheckCircle, color: 'text-green-600' },
      inactive: { variant: 'secondary' as const, icon: Clock, color: 'text-gray-600' },
      suspended: { variant: 'destructive' as const, icon: AlertCircle, color: 'text-red-600' }
    };
    
    const config = variants[status as keyof typeof variants];
    const Icon = config.icon;
    
    return (
      <Badge variant={config.variant} className="flex items-center gap-1">
        <Icon className={`h-3 w-3 ${config.color}`} />
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </Badge>
    );
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Gestion des Tenants</h1>
          <p className="text-gray-600">Supervision de toutes les entreprises clientes</p>
        </div>
        <Button>
          <Building2 className="h-4 w-4 mr-2" />
          Ajouter un Tenant
        </Button>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Tenants</CardTitle>
            <Building2 className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{tenants.length}</div>
            <p className="text-xs text-muted-foreground">+2 ce mois</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Utilisateurs Actifs</CardTitle>
            <Users className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {tenants.reduce((sum, tenant) => sum + tenant.users_count, 0)}
            </div>
            <p className="text-xs text-muted-foreground">Tous tenants confondus</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Véhicules Gérés</CardTitle>
            <TrendingUp className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {tenants.reduce((sum, tenant) => sum + tenant.vehicles_count, 0)}
            </div>
            <p className="text-xs text-muted-foreground">Total flotte</p>
          </CardContent>
        </Card>
      </div>

      {/* Tenants Table */}
      <Card>
        <CardHeader>
          <CardTitle>Liste des Tenants</CardTitle>
          <CardDescription>
            Toutes les entreprises utilisant la plateforme FlotteQ
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <table className="w-full table-auto">
              <thead>
                <tr className="border-b">
                  <th className="text-left py-3 px-4 font-medium">Entreprise</th>
                  <th className="text-left py-3 px-4 font-medium">Domaine</th>
                  <th className="text-left py-3 px-4 font-medium">Statut</th>
                  <th className="text-left py-3 px-4 font-medium">Utilisateurs</th>
                  <th className="text-left py-3 px-4 font-medium">Véhicules</th>
                  <th className="text-left py-3 px-4 font-medium">Plan</th>
                  <th className="text-left py-3 px-4 font-medium">Dernière activité</th>
                  <th className="text-left py-3 px-4 font-medium">Actions</th>
                </tr>
              </thead>
              <tbody>
                {tenants.map((tenant) => (
                  <tr key={tenant.id} className="border-b hover:bg-gray-50">
                    <td className="py-3 px-4">
                      <div className="flex items-center gap-3">
                        <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                          <Building2 className="h-4 w-4 text-blue-600" />
                        </div>
                        <div>
                          <div className="font-medium">{tenant.name}</div>
                          <div className="text-sm text-gray-500">ID: {tenant.id}</div>
                        </div>
                      </div>
                    </td>
                    <td className="py-3 px-4 text-sm">{tenant.domain}</td>
                    <td className="py-3 px-4">{getStatusBadge(tenant.status)}</td>
                    <td className="py-3 px-4 text-sm">{tenant.users_count}</td>
                    <td className="py-3 px-4 text-sm">{tenant.vehicles_count}</td>
                    <td className="py-3 px-4">
                      <Badge variant="outline">{tenant.subscription_plan}</Badge>
                    </td>
                    <td className="py-3 px-4 text-sm text-gray-500">
                      {new Date(tenant.last_activity).toLocaleDateString('fr-FR')}
                    </td>
                    <td className="py-3 px-4">
                      <div className="flex gap-2">
                        <Button size="sm" variant="outline">
                          Voir
                        </Button>
                        <Button size="sm" variant="outline">
                          Gérer
                        </Button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default TenantsOverview;