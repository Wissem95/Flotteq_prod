import React from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { AlertTriangle, AlertCircle, Info, CheckCircle, Clock, Zap, Shield, Database } from 'lucide-react';

interface SystemAlert {
  id: number;
  title: string;
  description: string;
  type: 'critical' | 'warning' | 'info' | 'success';
  category: 'security' | 'performance' | 'system' | 'database';
  status: 'active' | 'resolved' | 'investigating';
  created_at: string;
  resolved_at?: string;
}

const SystemAlerts: React.FC = () => {
  // Mock data - replace with real API calls
  const alerts: SystemAlert[] = [
    {
      id: 1,
      title: 'Utilisation élevée du CPU',
      description: 'Le serveur principal affiche une utilisation CPU de 85% depuis 10 minutes',
      type: 'warning',
      category: 'performance',
      status: 'active',
      created_at: '2025-07-31T15:30:00Z'
    },
    {
      id: 2,
      title: 'Tentative de connexion suspecte',
      description: 'Plusieurs tentatives de connexion échouées depuis l\'IP 192.168.1.100',
      type: 'critical',
      category: 'security',
      status: 'investigating',
      created_at: '2025-07-31T14:45:00Z'
    },
    {
      id: 3,
      title: 'Sauvegarde quotidienne réussie',
      description: 'La sauvegarde automatique des données s\'est terminée avec succès',
      type: 'success',
      category: 'database',
      status: 'resolved',
      created_at: '2025-07-31T02:00:00Z',
      resolved_at: '2025-07-31T02:15:00Z'
    },
    {
      id: 4,
      title: 'Mise à jour système disponible',
      description: 'Une nouvelle version de sécurité est disponible pour le serveur',
      type: 'info',
      category: 'system',
      status: 'active',
      created_at: '2025-07-31T08:00:00Z'
    }
  ];

  const getAlertIcon = (type: string) => {
    switch (type) {
      case 'critical': return <AlertTriangle className="h-5 w-5 text-red-600" />;
      case 'warning': return <AlertCircle className="h-5 w-5 text-yellow-600" />;
      case 'info': return <Info className="h-5 w-5 text-blue-600" />;
      case 'success': return <CheckCircle className="h-5 w-5 text-green-600" />;
      default: return <Info className="h-5 w-5 text-gray-600" />;
    }
  };

  const getCategoryIcon = (category: string) => {
    switch (category) {
      case 'security': return <Shield className="h-4 w-4" />;
      case 'performance': return <Zap className="h-4 w-4" />;
      case 'database': return <Database className="h-4 w-4" />;
      default: return <AlertCircle className="h-4 w-4" />;
    }
  };

  const getStatusBadge = (status: string) => {
    const variants = {
      active: { variant: 'destructive' as const, text: 'Actif' },
      investigating: { variant: 'secondary' as const, text: 'En cours' },
      resolved: { variant: 'default' as const, text: 'Résolu' }
    };
    
    const config = variants[status as keyof typeof variants];
    return <Badge variant={config.variant}>{config.text}</Badge>;
  };

  const getTypeBadge = (type: string) => {
    const variants = {
      critical: { variant: 'destructive' as const, text: 'Critique' },
      warning: { variant: 'secondary' as const, text: 'Attention' },
      info: { variant: 'outline' as const, text: 'Info' },
      success: { variant: 'default' as const, text: 'Succès' }
    };
    
    const config = variants[type as keyof typeof variants];
    return <Badge variant={config.variant}>{config.text}</Badge>;
  };

  const alertCounts = {
    critical: alerts.filter(a => a.type === 'critical' && a.status === 'active').length,
    warning: alerts.filter(a => a.type === 'warning' && a.status === 'active').length,
    active: alerts.filter(a => a.status === 'active').length,
    resolved: alerts.filter(a => a.status === 'resolved').length
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Alertes Système</h1>
          <p className="text-gray-600">Monitoring et surveillance de la plateforme</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline">
            <AlertTriangle className="h-4 w-4 mr-2" />
            Configurer Alertes
          </Button>
          <Button>
            Actualiser
          </Button>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card className={alertCounts.critical > 0 ? 'border-red-200 bg-red-50' : ''}>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Alertes Critiques</CardTitle>
            <AlertTriangle className="h-4 w-4 text-red-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-red-600">{alertCounts.critical}</div>
            <p className="text-xs text-red-600">Nécessitent une action immédiate</p>
          </CardContent>
        </Card>

        <Card className={alertCounts.warning > 0 ? 'border-yellow-200 bg-yellow-50' : ''}>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Avertissements</CardTitle>
            <AlertCircle className="h-4 w-4 text-yellow-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-yellow-600">{alertCounts.warning}</div>
            <p className="text-xs text-yellow-600">À surveiller</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Alertes Actives</CardTitle>
            <Clock className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{alertCounts.active}</div>
            <p className="text-xs text-muted-foreground">En cours de traitement</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Résolues (24h)</CardTitle>
            <CheckCircle className="h-4 w-4 text-green-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-green-600">{alertCounts.resolved}</div>
            <p className="text-xs text-green-600">Problèmes résolus</p>
          </CardContent>
        </Card>
      </div>

      {/* Alerts List */}
      <Card>
        <CardHeader>
          <CardTitle>Journal des Alertes</CardTitle>
          <CardDescription>
            Historique complet des alertes système et de sécurité
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {alerts.map((alert) => (
              <div key={alert.id} className="flex items-start gap-4 p-4 border rounded-lg hover:bg-gray-50">
                <div className="flex-shrink-0 mt-1">
                  {getAlertIcon(alert.type)}
                </div>
                
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2 mb-1">
                    <h3 className="font-medium text-gray-900">{alert.title}</h3>
                    {getTypeBadge(alert.type)}
                    {getStatusBadge(alert.status)}
                  </div>
                  
                  <p className="text-sm text-gray-600 mb-2">{alert.description}</p>
                  
                  <div className="flex items-center gap-4 text-xs text-gray-500">
                    <div className="flex items-center gap-1">
                      {getCategoryIcon(alert.category)}
                      <span className="capitalize">{alert.category}</span>
                    </div>
                    <span>
                      {new Date(alert.created_at).toLocaleString('fr-FR')}
                    </span>
                    {alert.resolved_at && (
                      <span className="text-green-600">
                        Résolu le {new Date(alert.resolved_at).toLocaleString('fr-FR')}
                      </span>
                    )}
                  </div>
                </div>
                
                <div className="flex-shrink-0">
                  {alert.status === 'active' && (
                    <div className="flex gap-2">
                      <Button size="sm" variant="outline">
                        Enquêter
                      </Button>
                      <Button size="sm" variant="outline">
                        Résoudre
                      </Button>
                    </div>
                  )}
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default SystemAlerts;