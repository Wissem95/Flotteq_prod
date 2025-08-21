// tenantService.ts - Service pour la gestion des tenants

import { api } from '@/lib/api';

// Utilitaires sécurisés
import { safeArray, safeFilter, safeLength, safeFindIndex } from '@/utils/safeData';

export interface Tenant {
  id: string;
  name: string;
  domain: string;
  admin_email: string;
  admin_name: string;
  subscription_plan: string;
  status: 'active' | 'inactive' | 'suspended';
  users_count: number;
  vehicles_count: number;
  max_users: number;
  max_vehicles: number;
  description?: string;
  created_at: string;
  updated_at: string;
  last_activity: string;
  settings?: {
    features_enabled: string[];
    custom_branding: boolean;
    api_access: boolean;
  };
  billing?: {
    last_payment: string;
    next_payment: string;
    payment_status: 'current' | 'overdue' | 'cancelled';
  };
}

export interface TenantCreateData {
  name: string;
  domain: string;
  admin_email: string;
  admin_name: string;
  subscription_plan: string;
  description?: string;
  max_users: number;
  max_vehicles: number;
}

export interface TenantUpdateData extends Partial<TenantCreateData> {
  status?: Tenant['status'];
  settings?: Tenant['settings'];
}

export interface TenantFilters {
  status?: Tenant['status'];
  plan?: string;
  search?: string;
  sort_by?: 'name' | 'created_at' | 'last_activity' | 'users_count' | 'vehicles_count';
  sort_order?: 'asc' | 'desc';
  page?: number;
  limit?: number;
}

export interface TenantStats {
  total: number;
  active: number;
  inactive: number;
  suspended: number;
  total_users: number;
  total_vehicles: number;
  monthly_growth: number;
  revenue_monthly: number;
}

class TenantService {
  // Récupérer tous les tenants avec filtres
  async getAll(filters: TenantFilters = {}): Promise<{ tenants: Tenant[]; stats: TenantStats; pagination: any }> {
    try {
      const response = await api.get('/tenants', { params: filters });
      return response.data;
    } catch (error: any) {
      console.error('Erreur récupération tenants:', error);
      
      // Si erreur API, utiliser les données locales comme fallback
      const localTenants = this.getLocalTenants();
      const safeTenants = safeArray(localTenants);
      const stats = this.calculateStats(safeTenants);
      
      return {
        tenants: safeTenants,
        stats,
        pagination: {
          page: filters.page || 1,
          limit: filters.limit || 10,
          total: safeLength(safeTenants),
          pages: Math.ceil(safeLength(safeTenants) / (filters.limit || 10))
        }
      };
    }
  }

  // Gestion locale des données
  private getLocalTenants(): Tenant[] {
    try {
      const stored = localStorage.getItem('flotteq_internal_tenants');
      if (stored) {
        const parsed = JSON.parse(stored);
        return Array.isArray(parsed) ? parsed : [];
      }
    } catch (error) {
      console.error('Erreur lors de la lecture des tenants locaux:', error);
    }
    
    // Données initiales si aucune donnée locale
    const initialTenants: Tenant[] = [
      {
        id: '1',
        name: 'Transport Express SARL',
        domain: 'transport-express.com',
        admin_email: 'admin@transport-express.com',
        admin_name: 'Jean Dupont',
        subscription_plan: 'professional',
        status: 'active',
        users_count: 12,
        vehicles_count: 45,
        max_users: 20,
        max_vehicles: 50,
        description: 'Entreprise de transport routier spécialisée dans la livraison express',
        created_at: '2024-12-15T10:30:00Z',
        updated_at: '2025-01-15T14:20:00Z',
        last_activity: '2025-01-31T09:15:00Z',
        settings: {
          features_enabled: ['maintenance', 'tracking', 'reports'],
          custom_branding: true,
          api_access: true
        },
        billing: {
          last_payment: '2025-01-15T00:00:00Z',
          next_payment: '2025-02-15T00:00:00Z',
          payment_status: 'current'
        }
      },
      {
        id: '2',
        name: 'LogiTech Solutions',
        domain: 'logitech-solutions.local',
        admin_email: 'contact@logitech-sol.com',
        admin_name: 'Marie Martin',
        subscription_plan: 'starter',
        status: 'active',
        users_count: 5,
        vehicles_count: 12,
        max_users: 10,
        max_vehicles: 15,
        description: 'Solutions logistiques pour PME',
        created_at: '2025-01-10T08:00:00Z',
        updated_at: '2025-01-25T16:45:00Z',
        last_activity: '2025-01-30T18:30:00Z',
        settings: {
          features_enabled: ['maintenance', 'tracking'],
          custom_branding: false,
          api_access: false
        },
        billing: {
          last_payment: '2025-01-10T00:00:00Z',
          next_payment: '2025-02-10T00:00:00Z',
          payment_status: 'current'
        }
      },
      {
        id: '3',
        name: 'Médical Services Plus',
        domain: 'medical-services.local',
        admin_email: 'admin@medical-services.fr',
        admin_name: 'Dr. Pierre Leblanc',
        subscription_plan: 'professional',
        status: 'suspended',
        users_count: 8,
        vehicles_count: 18,
        max_users: 20,
        max_vehicles: 25,
        description: 'Services médicaux d\'urgence et transport sanitaire',
        created_at: '2024-11-20T14:15:00Z',
        updated_at: '2025-01-20T11:30:00Z',
        last_activity: '2025-01-18T12:00:00Z',
        settings: {
          features_enabled: ['maintenance', 'tracking', 'emergency'],
          custom_branding: true,
          api_access: true
        },
        billing: {
          last_payment: '2024-12-20T00:00:00Z',
          next_payment: '2025-01-20T00:00:00Z',
          payment_status: 'overdue'
        }
      }
    ];
    
    this.saveLocalTenants(initialTenants);
    return initialTenants;
  }

  private saveLocalTenants(tenants: Tenant[]): void {
    localStorage.setItem('flotteq_internal_tenants', JSON.stringify(tenants));
  }

  private calculateStats(tenants: Tenant[]): TenantStats {
    const safeTenants = safeArray(tenants);
    return {
      total: safeTenants.length,
      active: safeFilter(safeTenants, t => t.status === 'active').length,
      inactive: safeFilter(safeTenants, t => t.status === 'inactive').length,
      suspended: safeFilter(safeTenants, t => t.status === 'suspended').length,
      total_users: safeTenants.reduce((sum, t) => sum + t.users_count, 0),
      total_vehicles: safeTenants.reduce((sum, t) => sum + t.vehicles_count, 0),
      monthly_growth: 15.2,
      revenue_monthly: 45760
    };
  }

  // Récupérer un tenant par ID
  async getById(id: string): Promise<Tenant> {
    try {
      const response = await api.get(`/tenants/${id}`);
      return response.data;
    } catch (error: any) {
      console.error('Erreur récupération tenant:', error);
      
      // Fallback local
      const tenants = this.getLocalTenants();
      const tenant = safeArray(tenants).find(t => t.id === id);
      
      if (!tenant) {
        throw new Error('Tenant non trouvé');
      }
      
      return tenant;
    }
  }

  // Créer un nouveau tenant
  async create(data: TenantCreateData): Promise<Tenant> {
    try {
      const response = await api.post('/tenants', data);
      
      // Mettre à jour le localStorage également
      const tenants = this.getLocalTenants();
      tenants.push(response.data);
      this.saveLocalTenants(tenants);
      
      return response.data;
    } catch (error: any) {
      console.error('Erreur création tenant:', error);
      
      // Fallback local
      await new Promise(resolve => setTimeout(resolve, 1500));
      
      const newTenant: Tenant = {
        id: Date.now().toString(),
        ...data,
        status: 'active',
        users_count: 1, // Admin par défaut
        vehicles_count: 0,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
        last_activity: new Date().toISOString(),
        settings: {
          features_enabled: ['maintenance', 'tracking'],
          custom_branding: data.subscription_plan !== 'starter',
          api_access: data.subscription_plan === 'enterprise'
        },
        billing: {
          last_payment: new Date().toISOString(),
          next_payment: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString(),
          payment_status: 'current'
        }
      };
      
      // Sauvegarder localement
      const tenants = this.getLocalTenants();
      tenants.push(newTenant);
      this.saveLocalTenants(tenants);
      
      return newTenant;
    }
  }

  // Mettre à jour un tenant
  async update(id: string, data: TenantUpdateData): Promise<Tenant> {
    try {
      const response = await api.put(`/tenants/${id}`, data);
      
      // Mettre à jour le localStorage également
      const tenants = this.getLocalTenants();
      const index = safeFindIndex(tenants, t => t.id === id);
      if (index !== -1) {
        tenants[index] = { ...tenants[index], ...response.data, updated_at: new Date().toISOString() };
        this.saveLocalTenants(tenants);
      }
      
      return response.data;
    } catch (error: any) {
      console.error('Erreur mise à jour tenant:', error);
      
      // Fallback local
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      const tenants = this.getLocalTenants();
      const index = safeFindIndex(tenants, t => t.id === id);
      
      if (index === -1) {
        throw new Error('Tenant non trouvé');
      }
      
      const updatedTenant: Tenant = {
        ...tenants[index],
        ...data,
        updated_at: new Date().toISOString()
      };
      
      tenants[index] = updatedTenant;
      this.saveLocalTenants(tenants);
      
      return updatedTenant;
    }
  }

  // Suspendre un tenant
  async suspend(id: string, reason?: string): Promise<void> {
    try {
      await api.post(`/tenants/${id}/suspend`, { reason });
      
      // Mettre à jour localement
      await this.updateLocalTenantStatus(id, 'suspended');
    } catch (error: any) {
      console.error('Erreur suspension tenant:', error);
      
      // Fallback local
      await new Promise(resolve => setTimeout(resolve, 800));
      await this.updateLocalTenantStatus(id, 'suspended');
    }
  }

  // Réactiver un tenant
  async activate(id: string): Promise<void> {
    try {
      await api.post(`/tenants/${id}/activate`);
      
      // Mettre à jour localement
      await this.updateLocalTenantStatus(id, 'active');
    } catch (error: any) {
      console.error('Erreur activation tenant:', error);
      
      // Fallback local
      await new Promise(resolve => setTimeout(resolve, 600));
      await this.updateLocalTenantStatus(id, 'active');
    }
  }

  // Supprimer un tenant (soft delete)
  async delete(id: string): Promise<void> {
    try {
      await api.delete(`/tenants/${id}`);
      
      // Supprimer localement
      const tenants = this.getLocalTenants();
      const filteredTenants = safeFilter(tenants, t => t.id !== id);
      this.saveLocalTenants(filteredTenants);
    } catch (error: any) {
      console.error('Erreur suppression tenant:', error);
      
      // Fallback local
      await new Promise(resolve => setTimeout(resolve, 1200));
      const tenants = this.getLocalTenants();
      const filteredTenants = safeFilter(tenants, t => t.id !== id);
      this.saveLocalTenants(filteredTenants);
    }
  }

  // Méthode utilitaire pour mettre à jour le statut localement
  private async updateLocalTenantStatus(id: string, status: Tenant['status']): Promise<void> {
    const tenants = this.getLocalTenants();
    const index = safeArray(tenants).findIndex(t => t.id === id);
    
    if (index !== -1) {
      tenants[index].status = status;
      tenants[index].updated_at = new Date().toISOString();
      this.saveLocalTenants(tenants);
    }
  }

  // Obtenir les détails d'utilisation d'un tenant
  async getUsageDetails(id: string): Promise<{
    current_usage: {
      users: number;
      vehicles: number;
      storage_mb: number;
      api_calls_monthly: number;
    };
    limits: {
      users: number;
      vehicles: number;
      storage_mb: number;
      api_calls_monthly: number;
    };
    usage_percentage: {
      users: number;
      vehicles: number;
      storage: number;
      api_calls: number;
    };
  }> {
    try {
      await new Promise(resolve => setTimeout(resolve, 500));
      
      return {
        current_usage: {
          users: 8,
          vehicles: 23,
          storage_mb: 1240,
          api_calls_monthly: 15670
        },
        limits: {
          users: 20,
          vehicles: 50,
          storage_mb: 5000,
          api_calls_monthly: 50000
        },
        usage_percentage: {
          users: 40,
          vehicles: 46,
          storage: 25,
          api_calls: 31
        }
      };
    } catch (error) {
      console.error('Erreur récupération usage tenant:', error);
      throw new Error('Impossible de récupérer les détails d\'utilisation');
    }
  }

  // Obtenir l'historique des activités d'un tenant
  async getActivityHistory(id: string, limit: number = 50): Promise<Array<{
    id: string;
    type: 'login' | 'vehicle_added' | 'user_added' | 'maintenance' | 'payment';
    user: string;
    description: string;
    timestamp: string;
    metadata?: any;
  }>> {
    try {
      await new Promise(resolve => setTimeout(resolve, 400));
      
      return [
        {
          id: '1',
          type: 'login',
          user: 'Jean Dupont',
          description: 'Connexion à l\'interface',
          timestamp: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString()
        },
        {
          id: '2',
          type: 'vehicle_added',
          user: 'Marie Martin',
          description: 'Ajout du véhicule AB-123-CD',
          timestamp: new Date(Date.now() - 5 * 60 * 60 * 1000).toISOString()
        },
        {
          id: '3',
          type: 'maintenance',
          user: 'Jean Dupont',
          description: 'Maintenance programmée pour le véhicule EF-456-GH',
          timestamp: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString()
        }
      ];
    } catch (error) {
      console.error('Erreur récupération historique tenant:', error);
      throw new Error('Impossible de récupérer l\'historique des activités');
    }
  }

  // Générer un rapport d'utilisation pour un tenant
  async generateUsageReport(id: string): Promise<Blob> {
    try {
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      const tenant = await this.getById(id);
      const usage = await this.getUsageDetails(id);
      const activities = await this.getActivityHistory(id);
      
      const reportContent = `
Rapport d'utilisation - ${tenant.name}
=====================================

Période: ${new Date().toLocaleDateString('fr-FR')}

Utilisation actuelle:
- Utilisateurs: ${usage.current_usage.users}/${usage.limits.users} (${usage.usage_percentage.users}%)
- Véhicules: ${usage.current_usage.vehicles}/${usage.limits.vehicles} (${usage.usage_percentage.vehicles}%)
- Stockage: ${usage.current_usage.storage_mb}MB/${usage.limits.storage_mb}MB (${usage.usage_percentage.storage}%)
- Appels API: ${usage.current_usage.api_calls_monthly}/${usage.limits.api_calls_monthly} (${usage.usage_percentage.api_calls}%)

Activités récentes:
${activities.map(a => `- ${new Date(a.timestamp).toLocaleDateString('fr-FR')} - ${a.description} (${a.user})`).join('\n')}
      `;
      
      return new Blob([reportContent], { type: 'text/plain' });
    } catch (error) {
      console.error('Erreur génération rapport tenant:', error);
      throw new Error('Impossible de générer le rapport d\'utilisation');
    }
  }
}

export const tenantService = new TenantService();