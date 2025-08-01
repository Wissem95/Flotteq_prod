// alertsService.ts - Service pour la gestion des alertes système

import { api } from '@/lib/api';

export interface SystemAlert {
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
  resolved_at?: string;
  resolved_by?: string;
}

export interface AlertConfig {
  id?: string;
  name: string;
  type: 'cpu' | 'memory' | 'disk' | 'response_time' | 'error_rate';
  threshold: number;
  operator: 'greater_than' | 'less_than' | 'equals';
  duration_minutes: number;
  recipients: string[];
  is_active: boolean;
}

class AlertsService {
  private getLocalAlerts(): SystemAlert[] {
    const stored = localStorage.getItem('flotteq_internal_alerts');
    if (stored) {
      return JSON.parse(stored);
    }
    
    // Données initiales
    const initialAlerts: SystemAlert[] = [
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
        resolution_comment: 'Maintenance reportée à demain 2h du matin',
        resolved_at: new Date().toISOString(),
        resolved_by: 'Admin System'
      }
    ];
    
    this.saveLocalAlerts(initialAlerts);
    return initialAlerts;
  }

  private saveLocalAlerts(alerts: SystemAlert[]): void {
    localStorage.setItem('flotteq_internal_alerts', JSON.stringify(alerts));
  }

  private getLocalConfigs(): AlertConfig[] {
    const stored = localStorage.getItem('flotteq_internal_alert_configs');
    if (stored) {
      return JSON.parse(stored);
    }
    
    const initialConfigs: AlertConfig[] = [
      {
        id: '1',
        name: 'CPU Usage High',
        type: 'cpu',
        threshold: 90,
        operator: 'greater_than',
        duration_minutes: 5,
        recipients: ['admin@flotteq.com', 'tech@flotteq.com'],
        is_active: true
      },
      {
        id: '2',
        name: 'Memory Usage Critical',
        type: 'memory',
        threshold: 85,
        operator: 'greater_than',
        duration_minutes: 10,
        recipients: ['admin@flotteq.com'],
        is_active: true
      }
    ];
    
    localStorage.setItem('flotteq_internal_alert_configs', JSON.stringify(initialConfigs));
    return initialConfigs;
  }

  private saveLocalConfigs(configs: AlertConfig[]): void {
    localStorage.setItem('flotteq_internal_alert_configs', JSON.stringify(configs));
  }

  // Récupérer toutes les alertes
  async getAll(): Promise<SystemAlert[]> {
    try {
      const response = await api.get('/alerts');
      return response.data;
    } catch (error: any) {
      console.error('Erreur récupération alertes:', error);
      return this.getLocalAlerts();
    }
  }

  // Configurer une nouvelle alerte
  async configure(config: AlertConfig): Promise<AlertConfig> {
    try {
      const response = await api.post('/alerts/config', config);
      
      // Mettre à jour localement
      const configs = this.getLocalConfigs();
      configs.push(response.data);
      this.saveLocalConfigs(configs);
      
      return response.data;
    } catch (error: any) {
      console.error('Erreur configuration alerte:', error);
      
      // Fallback local
      const newConfig: AlertConfig = {
        ...config,
        id: config.id || Date.now().toString()
      };
      
      const configs = this.getLocalConfigs();
      configs.push(newConfig);
      this.saveLocalConfigs(configs);
      
      return newConfig;
    }
  }

  // Enquêter sur une alerte
  async investigate(id: string): Promise<{
    logs: string[];
    metrics: any;
    recommendations: string[];
  }> {
    try {
      const response = await api.get(`/alerts/${id}/investigate`);
      
      // Mettre à jour le statut localement
      await this.updateAlertStatus(id, 'investigating');
      
      return response.data;
    } catch (error: any) {
      console.error('Erreur investigation alerte:', error);
      
      // Fallback local
      await this.updateAlertStatus(id, 'investigating');
      
      // Données simulées d'investigation
      return {
        logs: [
          `[${new Date().toISOString()}] CPU usage spike detected`,
          `[${new Date().toISOString()}] High load on API endpoints`,
          `[${new Date().toISOString()}] Database connection pool exhausted`
        ],
        metrics: {
          cpu_trend: [85, 87, 90, 92, 95],  
          memory_trend: [65, 68, 72, 75, 78],
          error_rate: [0.1, 0.2, 0.3, 0.5, 0.8]
        },
        recommendations: [
          'Augmenter les ressources CPU du serveur',
          'Optimiser les requêtes de base de données',
          'Implementer un système de cache Redis',
          'Configurer un load balancer'
        ]
      };
    }
  }

  // Résoudre une alerte
  async resolve(id: string, comment: string): Promise<void> {
    try {
      await api.post(`/alerts/${id}/resolve`, { comment });
      
      // Mettre à jour localement
      await this.updateAlertResolution(id, comment);
    } catch (error: any) {
      console.error('Erreur résolution alerte:', error);
      
      // Fallback local
      await this.updateAlertResolution(id, comment);
    }
  }

  // Mettre à jour le statut d'une alerte localement
  private async updateAlertStatus(id: string, status: SystemAlert['status']): Promise<void> {
    const alerts = this.getLocalAlerts();
    const index = alerts.findIndex(a => a.id === id);
    
    if (index !== -1) {
      alerts[index].status = status;
      this.saveLocalAlerts(alerts);
    }
  }

  // Mettre à jour la résolution d'une alerte localement
  private async updateAlertResolution(id: string, comment: string): Promise<void> {
    const alerts = this.getLocalAlerts();
    const index = alerts.findIndex(a => a.id === id);
    
    if (index !== -1) {
      alerts[index].status = 'resolved';
      alerts[index].resolution_comment = comment;
      alerts[index].resolved_at = new Date().toISOString();
      alerts[index].resolved_by = 'Admin Current'; // TODO: Récupérer le vrai utilisateur
      this.saveLocalAlerts(alerts);
    }
  }

  // Obtenir les configurations d'alertes
  async getConfigs(): Promise<AlertConfig[]> {
    try {
      const response = await api.get('/alerts/configs');
      return response.data;
    } catch (error: any) {
      console.error('Erreur récupération configs alertes:', error);
      return this.getLocalConfigs();
    }
  }

  // Actualiser les alertes (récupérer les dernières)
  async refresh(): Promise<SystemAlert[]> {
    try {
      const response = await api.get('/alerts/refresh');
      
      // Mettre à jour le cache local
      this.saveLocalAlerts(response.data);
      
      return response.data;
    } catch (error: any) {
      console.error('Erreur actualisation alertes:', error);
      
      // Simulation d'actualisation locale
      const alerts = this.getLocalAlerts();
      
      // Ajouter une nouvelle alerte simulée parfois
      if (Math.random() > 0.7) {
        const newAlert: SystemAlert = {
          id: Date.now().toString(),
          type: 'warning',
          category: 'performance',
          title: 'Nouvelle alerte détectée',
          description: 'Surveillance automatique a détecté une anomalie',
          timestamp: new Date().toISOString(),
          status: 'active',
          affected_services: ['Monitoring System']
        };
        
        alerts.unshift(newAlert);
        this.saveLocalAlerts(alerts);
      }
      
      return alerts;
    }
  }

  // Supprimer une alerte
  async delete(id: string): Promise<void> {
    try {
      await api.delete(`/alerts/${id}`);
      
      // Supprimer localement
      const alerts = this.getLocalAlerts();
      const filteredAlerts = alerts.filter(a => a.id !== id);
      this.saveLocalAlerts(filteredAlerts);
    } catch (error: any) {
      console.error('Erreur suppression alerte:', error);
      
      // Fallback local
      const alerts = this.getLocalAlerts();
      const filteredAlerts = alerts.filter(a => a.id !== id);
      this.saveLocalAlerts(filteredAlerts);
    }
  }
}

export const alertsService = new AlertsService();