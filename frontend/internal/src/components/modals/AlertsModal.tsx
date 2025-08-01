// AlertsModal.tsx - Modal pour afficher et gérer les alertes système

import React, { useState, useEffect } from "react";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Textarea } from "@/components/ui/textarea";
import { Separator } from "@/components/ui/separator";
import { toast } from "@/hooks/use-toast";
import { 
  AlertTriangle, 
  CheckCircle, 
  Clock, 
  AlertCircle, 
  Server, 
  Database, 
  Cpu, 
  HardDrive,
  Wifi,
  Users,
  RefreshCw,
  FileText
} from "lucide-react";

interface SystemAlert {
  id: string;
  type: 'critical' | 'warning' | 'info';
  category: 'system' | 'performance' | 'security' | 'business';
  title: string;
  description: string;
  timestamp: string;
  status: 'active' | 'investigating' | 'resolved';
  affected_services?: string[];
  metrics?: {
    cpu_usage?: number;
    memory_usage?: number;
    disk_usage?: number;
    response_time?: number;
  };
  resolution_comment?: string;
}

interface AlertsModalProps {
  isOpen: boolean;
  onClose: () => void;
  onRefresh: () => void;
}

const AlertsModal: React.FC<AlertsModalProps> = ({ isOpen, onClose, onRefresh }) => {
  const [alerts, setAlerts] = useState<SystemAlert[]>([]);
  const [loading, setLoading] = useState(false);
  const [selectedAlert, setSelectedAlert] = useState<SystemAlert | null>(null);
  const [resolutionComment, setResolutionComment] = useState('');
  const [filterStatus, setFilterStatus] = useState<'all' | SystemAlert['status']>('all');

  // Mock data pour les alertes
  const mockAlerts: SystemAlert[] = [
    {
      id: '1',
      type: 'critical',
      category: 'system',
      title: 'Utilisation CPU élevée',
      description: 'Le serveur principal affiche une utilisation CPU de 95% depuis 15 minutes',
      timestamp: new Date(Date.now() - 15 * 60 * 1000).toISOString(),
      status: 'active',
      affected_services: ['API Principal', 'Interface Utilisateur'],
      metrics: {
        cpu_usage: 95,
        memory_usage: 78,
        response_time: 1500
      }
    },
    {
      id: '2',
      type: 'warning',
      category: 'performance',
      title: 'Temps de réponse dégradé',
      description: 'Les temps de réponse API ont augmenté de 40% par rapport à la normale',
      timestamp: new Date(Date.now() - 30 * 60 * 1000).toISOString(),
      status: 'investigating',
      affected_services: ['API Véhicules', 'API Maintenance'],
      metrics: {
        response_time: 850
      }
    },
    {
      id: '3',
      type: 'warning',
      category: 'business',
      title: 'Pic de connexions utilisateurs',
      description: 'Nombre de connexions simultanées inhabituel détecté',
      timestamp: new Date(Date.now() - 45 * 60 * 1000).toISOString(),
      status: 'active',
      affected_services: ['Authentification']
    },
    {
      id: '4',
      type: 'info',
      category: 'system',
      title: 'Maintenance programmée',
      description: 'Mise à jour de sécurité prévue dans 2 heures',
      timestamp: new Date(Date.now() - 60 * 60 * 1000).toISOString(),
      status: 'resolved',
      resolution_comment: 'Maintenance reportée à demain 2h du matin'
    }
  ];

  useEffect(() => {
    if (isOpen) {
      loadAlerts();
    }
  }, [isOpen]);

  const loadAlerts = async () => {
    setLoading(true);
    try {
      // TODO: Remplacer par un vrai appel API
      await new Promise(resolve => setTimeout(resolve, 1000));
      setAlerts(mockAlerts);
    } catch (error) {
      toast({
        title: "Erreur",
        description: "Impossible de charger les alertes",
        variant: "destructive"
      });
    } finally {
      setLoading(false);
    }
  };

  const getAlertIcon = (type: SystemAlert['type'], category: SystemAlert['category']) => {
    if (type === 'critical') return <AlertTriangle className="h-4 w-4" />;
    if (type === 'warning') return <AlertCircle className="h-4 w-4" />;
    
    switch (category) {
      case 'system': return <Server className="h-4 w-4" />;
      case 'performance': return <Cpu className="h-4 w-4" />;
      case 'security': return <Shield className="h-4 w-4" />;
      case 'business': return <Users className="h-4 w-4" />;
      default: return <AlertCircle className="h-4 w-4" />;
    }
  };

  const getAlertBadge = (type: SystemAlert['type']) => {
    const configs = {
      critical: { variant: 'destructive' as const, label: 'Critique' },
      warning: { variant: 'default' as const, label: 'Avertissement' },
      info: { variant: 'secondary' as const, label: 'Information' }
    };
    
    const config = configs[type];
    return <Badge variant={config.variant}>{config.label}</Badge>;
  };

  const getStatusBadge = (status: SystemAlert['status']) => {
    const configs = {
      active: { variant: 'destructive' as const, icon: AlertCircle, label: 'Actif' },
      investigating: { variant: 'default' as const, icon: Clock, label: 'En cours' },
      resolved: { variant: 'secondary' as const, icon: CheckCircle, label: 'Résolu' }
    };
    
    const config = configs[status];
    const Icon = config.icon;
    
    return (
      <Badge variant={config.variant} className="flex items-center gap-1">
        <Icon className="h-3 w-3" />
        {config.label}
      </Badge>
    );
  };

  const handleInvestigate = (alert: SystemAlert) => {
    // TODO: Implémenter la logique d'investigation
    toast({
      title: "Investigation lancée",
      description: `Investigation de l'alerte "${alert.title}" en cours`,
    });
  };

  const handleResolve = async (alert: SystemAlert) => {
    if (!resolutionComment.trim()) {
      toast({
        title: "Commentaire requis",
        description: "Veuillez ajouter un commentaire de résolution",
        variant: "destructive"
      });
      return;
    }

    try {
      // TODO: Implémenter la résolution via API
      setAlerts(prev => 
        prev.map(a => 
          a.id === alert.id 
            ? { ...a, status: 'resolved' as const, resolution_comment: resolutionComment }
            : a
        )
      );
      
      setSelectedAlert(null);
      setResolutionComment('');
      
      toast({
        title: "Alerte résolue",
        description: `L'alerte "${alert.title}" a été marquée comme résolue`,
      });
    } catch (error) {
      toast({
        title: "Erreur",
        description: "Impossible de résoudre l'alerte",
        variant: "destructive"
      });
    }
  };

  const filteredAlerts = filterStatus === 'all' 
    ? alerts 
    : alerts.filter(alert => alert.status === filterStatus);

  const formatTimestamp = (timestamp: string) => {
    const date = new Date(timestamp);
    const now = new Date();
    const diffMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60));
    
    if (diffMinutes < 60) {
      return `Il y a ${diffMinutes} min`;
    } else if (diffMinutes < 1440) {
      return `Il y a ${Math.floor(diffMinutes / 60)}h`;
    } else {
      return date.toLocaleDateString('fr-FR');
    }
  };

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <AlertTriangle className="h-5 w-5" />
            Alertes système
          </DialogTitle>
          <DialogDescription>
            Surveillez et gérez les alertes de la plateforme FlotteQ
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-4">
          {/* Actions et filtres */}
          <div className="flex items-center justify-between">
            <div className="flex gap-2">
              <Button
                variant={filterStatus === 'all' ? 'default' : 'outline'}
                size="sm"
                onClick={() => setFilterStatus('all')}
              >
                Toutes ({alerts.length})
              </Button>
              <Button
                variant={filterStatus === 'active' ? 'default' : 'outline'}
                size="sm"
                onClick={() => setFilterStatus('active')}
              >
                Actives ({alerts.filter(a => a.status === 'active').length})
              </Button>
              <Button
                variant={filterStatus === 'investigating' ? 'default' : 'outline'}
                size="sm"
                onClick={() => setFilterStatus('investigating')}
              >
                En cours ({alerts.filter(a => a.status === 'investigating').length})
              </Button>
            </div>
            
            <Button
              variant="outline"
              size="sm"
              onClick={() => { onRefresh(); loadAlerts(); }}
              disabled={loading}
            >
              <RefreshCw className={`h-4 w-4 mr-2 ${loading ? 'animate-spin' : ''}`} />
              Actualiser
            </Button>
          </div>

          {/* Liste des alertes */}
          <div className="space-y-3">
            {loading ? (
              <div className="text-center py-8">
                <RefreshCw className="h-8 w-8 animate-spin mx-auto mb-4" />
                <p>Chargement des alertes...</p>
              </div>
            ) : filteredAlerts.length === 0 ? (
              <div className="text-center py-8">
                <CheckCircle className="h-8 w-8 text-green-500 mx-auto mb-4" />
                <p className="text-gray-600">Aucune alerte {filterStatus !== 'all' ? filterStatus : ''}</p>
              </div>
            ) : (
              filteredAlerts.map((alert) => (
                <Card key={alert.id} className={`${
                  alert.type === 'critical' ? 'border-red-200' : 
                  alert.type === 'warning' ? 'border-yellow-200' : 'border-blue-200'
                }`}>
                  <CardHeader className="pb-3">
                    <div className="flex items-start justify-between">
                      <div className="flex items-start gap-3">
                        <div className={`p-2 rounded-full ${
                          alert.type === 'critical' ? 'bg-red-100 text-red-600' : 
                          alert.type === 'warning' ? 'bg-yellow-100 text-yellow-600' : 'bg-blue-100 text-blue-600'
                        }`}>
                          {getAlertIcon(alert.type, alert.category)}
                        </div>
                        <div className="flex-1">
                          <div className="flex items-center gap-2 mb-1">
                            <CardTitle className="text-base">{alert.title}</CardTitle>
                            {getAlertBadge(alert.type)}
                            {getStatusBadge(alert.status)}
                          </div>
                          <CardDescription>{alert.description}</CardDescription>
                          <div className="text-xs text-gray-500 mt-1">
                            {formatTimestamp(alert.timestamp)}
                          </div>
                        </div>
                      </div>
                      
                      <div className="flex gap-2 ml-4">
                        {alert.status === 'active' && (
                          <>
                            <Button
                              size="sm"
                              variant="outline"
                              onClick={() => handleInvestigate(alert)}
                            >
                              Enquêter
                            </Button>
                            <Button
                              size="sm"
                              onClick={() => setSelectedAlert(alert)}
                            >
                              Résoudre
                            </Button>
                          </>
                        )}
                        {alert.status === 'investigating' && (
                          <Button
                            size="sm"
                            onClick={() => setSelectedAlert(alert)}
                          >
                            Résoudre
                          </Button>
                        )}
                      </div>
                    </div>
                  </CardHeader>
                  
                  {(alert.affected_services || alert.metrics) && (
                    <CardContent className="pt-0">
                      {alert.affected_services && (
                        <div className="mb-3">
                          <p className="text-sm font-medium mb-1">Services affectés :</p>
                          <div className="flex flex-wrap gap-1">
                            {alert.affected_services.map((service, index) => (
                              <Badge key={index} variant="outline" className="text-xs">
                                {service}
                              </Badge>
                            ))}
                          </div>
                        </div>
                      )}
                      
                      {alert.metrics && (
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                          {alert.metrics.cpu_usage && (
                            <div>
                              <span className="text-gray-500">CPU:</span> {alert.metrics.cpu_usage}%
                            </div>
                          )}
                          {alert.metrics.memory_usage && (
                            <div>
                              <span className="text-gray-500">RAM:</span> {alert.metrics.memory_usage}%
                            </div>
                          )}
                          {alert.metrics.response_time && (
                            <div>
                              <span className="text-gray-500">Réponse:</span> {alert.metrics.response_time}ms
                            </div>
                          )}
                        </div>
                      )}
                      
                      {alert.resolution_comment && (
                        <div className="mt-3 p-3 bg-green-50 rounded-md">
                          <p className="text-sm text-green-800">
                            <strong>Résolution :</strong> {alert.resolution_comment}
                          </p>
                        </div>
                      )}
                    </CardContent>
                  )}
                </Card>
              ))
            )}
          </div>
        </div>

        {/* Modal de résolution */}
        {selectedAlert && (
          <Dialog open={!!selectedAlert} onOpenChange={() => setSelectedAlert(null)}>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Résoudre l'alerte</DialogTitle>
                <DialogDescription>
                  Marquez cette alerte comme résolue et ajoutez un commentaire
                </DialogDescription>
              </DialogHeader>
              
              <div className="space-y-4">
                <div className="p-3 bg-gray-50 rounded-md">
                  <p className="font-medium">{selectedAlert.title}</p>
                  <p className="text-sm text-gray-600">{selectedAlert.description}</p>
                </div>
                
                <div className="space-y-2">
                  <label className="text-sm font-medium">Commentaire de résolution *</label>
                  <Textarea
                    value={resolutionComment}
                    onChange={(e) => setResolutionComment(e.target.value)}
                    placeholder="Décrivez comment l'alerte a été résolue..."
                    rows={3}
                  />
                </div>
              </div>
              
              <div className="flex justify-end gap-2">
                <Button variant="outline" onClick={() => setSelectedAlert(null)}>
                  Annuler
                </Button>
                <Button onClick={() => handleResolve(selectedAlert)}>
                  Marquer comme résolu
                </Button>
              </div>
            </DialogContent>
          </Dialog>
        )}
      </DialogContent>
    </Dialog>
  );
};

// Shield icon pour la sécurité
const Shield = ({ className }: { className?: string }) => (
  <svg className={className} fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
  </svg>
);

export default AlertsModal;