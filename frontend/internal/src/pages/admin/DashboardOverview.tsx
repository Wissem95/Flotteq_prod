// DashboardOverview.tsx - Vue d'ensemble du tableau de bord administrateur FlotteQ

import React, { useState, useEffect } from "react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  LineChart,
  Line,
  PieChart,
  Pie,
  Cell,
} from "recharts";
import {
  Building2,
  Users,
  DollarSign,
  TrendingUp,
  AlertTriangle,
  CheckCircle,
  Clock,
  ArrowUpRight,
  ArrowDownRight,
  Activity,
} from "lucide-react";
import AlertsModal from "@/components/modals/AlertsModal";
import { reportService } from "@/services/reportService";
import { toast } from "@/hooks/use-toast";

// Types pour les métriques
interface PlatformMetrics {
  tenants: {
    total: number;
    active: number;
    growth_percentage: number;
  };
  revenue: {
    monthly: number;
    annual: number;
    growth_percentage: number;
  };
  partners: {
    total: number;
    active: number;
    pending: number;
  };
  users: {
    total: number;
    active_monthly: number;
    growth_percentage: number;
  };
  system_health: {
    uptime_percentage: number;
    response_time_ms: number;
    status: 'healthy' | 'warning' | 'critical';
  };
}

const DashboardOverview: React.FC = () => {
  const [metrics, setMetrics] = useState<PlatformMetrics | null>(null);
  const [loading, setLoading] = useState(true);
  const [showAlertsModal, setShowAlertsModal] = useState(false);
  const [generatingReport, setGeneratingReport] = useState(false);
  const [loadingAlerts, setLoadingAlerts] = useState(false);

  // Données simulées (à remplacer par de vraies données de l'API)
  const mockMetrics: PlatformMetrics = {
    tenants: {
      total: 127,
      active: 119,
      growth_percentage: 15.2,
    },
    revenue: {
      monthly: 45760,
      annual: 523480,
      growth_percentage: 23.1,
    },
    partners: {
      total: 89,
      active: 73,
      pending: 12,
    },
    users: {
      total: 2847,
      active_monthly: 2341,
      growth_percentage: 18.7,
    },
    system_health: {
      uptime_percentage: 99.97,
      response_time_ms: 142,
      status: 'healthy',
    },
  };

  // Données pour les graphiques
  const monthlyRevenueData = [
    { month: 'Jan', revenue: 35000, tenants: 98 },
    { month: 'Fév', revenue: 38500, tenants: 104 },
    { month: 'Mar', revenue: 42000, tenants: 112 },
    { month: 'Avr', revenue: 39800, tenants: 108 },
    { month: 'Mai', revenue: 43200, tenants: 119 },
    { month: 'Jun', revenue: 45760, tenants: 127 },
  ];

  const partnersDistribution = [
    { name: 'Garages', value: 45, color: '#3b82f6' },
    { name: 'Centres CT', value: 28, color: '#10b981' },
    { name: 'Assurances', value: 16, color: '#f59e0b' },
  ];

  useEffect(() => {
    // Simuler le chargement des données
    const loadMetrics = async () => {
      setLoading(true);
      // TODO: Remplacer par un vrai appel API
      await new Promise(resolve => setTimeout(resolve, 1000));
      setMetrics(mockMetrics);
      setLoading(false);
    };

    loadMetrics();
  }, []);

  // Gestionnaire pour vérifier les alertes
  const handleCheckAlerts = async () => {
    setLoadingAlerts(true);
    try {
      // Simulation du chargement des alertes
      await new Promise(resolve => setTimeout(resolve, 500));
      setShowAlertsModal(true);
      toast({
        title: "Alertes chargées",
        description: "Vérification des alertes système en cours",
      });
    } catch (error) {
      toast({
        title: "Erreur",
        description: "Impossible de charger les alertes système",
        variant: "destructive"
      });
    } finally {
      setLoadingAlerts(false);
    }
  };

  // Gestionnaire pour générer le rapport complet
  const handleGenerateReport = async () => {
    setGeneratingReport(true);
    try {
      const blob = await reportService.generateDashboardReport({
        startDate: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        endDate: new Date().toISOString().split('T')[0]
      });
      
      // Télécharger le fichier
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `rapport-dashboard-${new Date().toISOString().split('T')[0]}.pdf`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
      
      toast({
        title: "Rapport généré",
        description: "Le rapport complet du tableau de bord a été téléchargé",
      });
    } catch (error) {
      toast({
        title: "Erreur",
        description: "Impossible de générer le rapport",
        variant: "destructive"
      });
    } finally {
      setGeneratingReport(false);
    }
  };

  // Gestionnaire pour actualiser les alertes
  const handleRefreshAlerts = () => {
    toast({
      title: "Alertes actualisées",
      description: "Les alertes système ont été mises à jour",
    });
  };

  const MetricCard = ({ 
    title, 
    value, 
    subtitle, 
    icon: Icon, 
    trend, 
    color = "blue" 
  }: {
    title: string;
    value: string | number;
    subtitle?: string;
    icon: any;
    trend?: { value: number; positive: boolean };
    color?: string;
  }) => (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle className="text-sm font-medium">{title}</CardTitle>
        <Icon className={`h-4 w-4 text-${color}-600`} />
      </CardHeader>
      <CardContent>
        <div className="text-2xl font-bold">{value}</div>
        {subtitle && (
          <p className="text-xs text-gray-600 mt-1">{subtitle}</p>
        )}
        {trend && (
          <div className="flex items-center mt-2">
            {trend.positive ? (
              <ArrowUpRight className="h-4 w-4 text-green-600" />
            ) : (
              <ArrowDownRight className="h-4 w-4 text-red-600" />
            )}
            <span className={`text-xs ml-1 ${trend.positive ? 'text-green-600' : 'text-red-600'}`}>
              {Math.abs(trend.value)}%
            </span>
          </div>
        )}
      </CardContent>
    </Card>
  );

  if (loading) {
    return (
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h1 className="text-2xl font-bold">Vue d'ensemble</h1>
          <Badge variant="secondary">
            <Activity className="w-4 h-4 mr-1" />
            Chargement...
          </Badge>
        </div>
        
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          {[1, 2, 3, 4].map((i) => (
            <Card key={i} className="animate-pulse">
              <CardHeader>
                <div className="h-4 bg-gray-200 rounded w-3/4"></div>
              </CardHeader>
              <CardContent>
                <div className="h-8 bg-gray-200 rounded w-1/2 mb-2"></div>
                <div className="h-3 bg-gray-200 rounded w-2/3"></div>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    );
  }

  if (!metrics) return null;

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">Vue d'ensemble</h1>
          <p className="text-gray-600">Métriques globales de la plateforme FlotteQ</p>
        </div>
        <Badge 
          variant={metrics.system_health.status === 'healthy' ? 'default' : 'destructive'}
          className="bg-green-100 text-green-800"
        >
          <CheckCircle className="w-4 h-4 mr-1" />
          Système opérationnel
        </Badge>
      </div>

      {/* Métriques principales */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <MetricCard
          title="Tenants"
          value={metrics.tenants.total}
          subtitle={`${metrics.tenants.active} actifs`}
          icon={Building2}
          trend={{ value: metrics.tenants.growth_percentage, positive: true }}
          color="blue"
        />
        
        <MetricCard
          title="Revenus mensuels"
          value={`${metrics.revenue.monthly.toLocaleString()} €`}
          subtitle="Croissance mensuelle"
          icon={DollarSign}
          trend={{ value: metrics.revenue.growth_percentage, positive: true }}
          color="green"
        />
        
        <MetricCard
          title="Partenaires"
          value={metrics.partners.total}
          subtitle={`${metrics.partners.active} actifs, ${metrics.partners.pending} en attente`}
          icon={Users}
          color="purple"
        />
        
        <MetricCard
          title="Utilisateurs"
          value={metrics.users.total}
          subtitle={`${metrics.users.active_monthly} actifs ce mois`}
          icon={Activity}
          trend={{ value: metrics.users.growth_percentage, positive: true }}
          color="orange"
        />
      </div>

      {/* Graphiques */}
      <div className="grid gap-6 md:grid-cols-2">
        {/* Revenus et Tenants */}
        <Card>
          <CardHeader>
            <CardTitle>Évolution des revenus et tenants</CardTitle>
            <CardDescription>
              Progression des revenus mensuels et nombre de tenants
            </CardDescription>
          </CardHeader>
          <CardContent>
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={monthlyRevenueData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip 
                  formatter={(value, name) => [
                    name === 'revenue' ? `${value.toLocaleString()} €` : value,
                    name === 'revenue' ? 'Revenus' : 'Tenants'
                  ]}
                />
                <Line 
                  type="monotone" 
                  dataKey="revenue" 
                  stroke="#3b82f6" 
                  strokeWidth={2}
                  dot={{ fill: '#3b82f6' }}
                />
                <Line 
                  type="monotone" 
                  dataKey="tenants" 
                  stroke="#10b981" 
                  strokeWidth={2}
                  dot={{ fill: '#10b981' }}
                />
              </LineChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>

        {/* Répartition des partenaires */}
        <Card>
          <CardHeader>
            <CardTitle>Répartition des partenaires</CardTitle>
            <CardDescription>
              Distribution par type de partenaire
            </CardDescription>
          </CardHeader>
          <CardContent>
            <ResponsiveContainer width="100%" height={300}>
              <PieChart>
                <Pie
                  data={partnersDistribution}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                  outerRadius={80}
                  fill="#8884d8"
                  dataKey="value"
                >
                  {partnersDistribution.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={entry.color} />
                  ))}
                </Pie>
                <Tooltip />
              </PieChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>
      </div>

      {/* Métriques système */}
      <Card>
        <CardHeader>
          <CardTitle>État du système</CardTitle>
          <CardDescription>
            Performances et disponibilité de la plateforme
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid gap-4 md:grid-cols-3">
            <div className="text-center">
              <div className="text-2xl font-bold text-green-600">
                {metrics.system_health.uptime_percentage}%
              </div>
              <div className="text-sm text-gray-600">Disponibilité</div>
            </div>
            <div className="text-center">
              <div className="text-2xl font-bold text-blue-600">
                {metrics.system_health.response_time_ms}ms
              </div>
              <div className="text-sm text-gray-600">Temps de réponse</div>
            </div>
            <div className="text-center">
              <Badge 
                variant={metrics.system_health.status === 'healthy' ? 'default' : 'destructive'}
                className="text-lg px-4 py-2"
              >
                {metrics.system_health.status === 'healthy' ? 'Excellent' : 'Attention'}
              </Badge>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Actions rapides */}
      <div className="flex gap-4">
        <Button 
          variant="outline" 
          className="flex items-center gap-2"
          onClick={handleGenerateReport}
          disabled={generatingReport}
        >
          <TrendingUp className={`w-4 h-4 ${generatingReport ? 'animate-spin' : ''}`} />
          {generatingReport ? 'Génération...' : 'Voir le rapport complet'}
        </Button>
        <Button 
          variant="outline" 
          className="flex items-center gap-2"
          onClick={handleCheckAlerts}
          disabled={loadingAlerts}
        >
          <AlertTriangle className={`w-4 h-4 ${loadingAlerts ? 'animate-spin' : ''}`} />
          {loadingAlerts ? 'Chargement...' : 'Vérifier les alertes'}
        </Button>
      </div>

      {/* Modal des alertes */}
      <AlertsModal 
        isOpen={showAlertsModal}
        onClose={() => setShowAlertsModal(false)}
        onRefresh={handleRefreshAlerts}
      />
    </div>
  );
};

export default DashboardOverview; 