// tenantService.ts - Service pour la gestion des tenants

import { api } from '@/lib/api';

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
      // TODO: Remplacer par un vrai appel API
      // const response = await api.get('/internal/tenants', { params: filters });
      
      // Mock data pour le développement
      await new Promise(resolve => setTimeout(resolve, 800));
      
      const mockTenants: Tenant[] = [
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

      const stats: TenantStats = {
        total: mockTenants.length,
        active: mockTenants.filter(t => t.status === 'active').length,
        inactive: mockTenants.filter(t => t.status === 'inactive').length,
        suspended: mockTenants.filter(t => t.status === 'suspended').length,
        total_users: mockTenants.reduce((sum, t) => sum + t.users_count, 0),
        total_vehicles: mockTenants.reduce((sum, t) => sum + t.vehicles_count, 0),
        monthly_growth: 15.2,
        revenue_monthly: 45760
      };

      // Appliquer les filtres
      let filteredTenants = mockTenants;
      
      if (filters.status) {
        filteredTenants = filteredTenants.filter(t => t.status === filters.status);
      }
      
      if (filters.plan) {
        filteredTenants = filteredTenants.filter(t => t.subscription_plan === filters.plan);
      }
      
      if (filters.search) {
        const search = filters.search.toLowerCase();
        filteredTenants = filteredTenants.filter(t => 
          t.name.toLowerCase().includes(search) ||
          t.domain.toLowerCase().includes(search) ||
          t.admin_email.toLowerCase().includes(search)
        );
      }

      return {
        tenants: filteredTenants,
        stats,
        pagination: {
          page: filters.page || 1,
          limit: filters.limit || 10,
          total: filteredTenants.length,
          pages: Math.ceil(filteredTenants.length / (filters.limit || 10))
        }
      };
    } catch (error) {
      console.error('Erreur récupération tenants:', error);
      throw new Error('Impossible de récupérer la liste des tenants');
    }
  }

  // Récupérer un tenant par ID
  async getById(id: string): Promise<Tenant> {
    try {
      // TODO: Remplacer par un vrai appel API
      // const response = await api.get(`/internal/tenants/${id}`);
      
      const { tenants } = await this.getAll();
      const tenant = tenants.find(t => t.id === id);
      
      if (!tenant) {
        throw new Error('Tenant non trouvé');
      }
      
      return tenant;
    } catch (error) {
      console.error('Erreur récupération tenant:', error);
      throw new Error('Impossible de récupérer les détails du tenant');
    }
  }

  // Créer un nouveau tenant
  async create(data: TenantCreateData): Promise<Tenant> {
    try {
      // TODO: Remplacer par un vrai appel API
      // const response = await api.post('/internal/tenants', data);
      
      await new Promise(resolve => setTimeout(resolve, 1500));
      
      // Simulation de création
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
      
      return newTenant;
    } catch (error) {
      console.error('Erreur création tenant:', error);
      throw new Error('Impossible de créer le tenant');
    }
  }

  // Mettre à jour un tenant
  async update(id: string, data: TenantUpdateData): Promise<Tenant> {
    try {
      // TODO: Remplacer par un vrai appel API
      // const response = await api.put(`/internal/tenants/${id}`, data);
      
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      const tenant = await this.getById(id);
      const updatedTenant: Tenant = {
        ...tenant,
        ...data,
        updated_at: new Date().toISOString()
      };
      
      return updatedTenant;
    } catch (error) {
      console.error('Erreur mise à jour tenant:', error);
      throw new Error('Impossible de mettre à jour le tenant');
    }
  }

  // Suspendre un tenant
  async suspend(id: string, reason?: string): Promise<void> {
    try {
      // TODO: Remplacer par un vrai appel API
      // await api.post(`/internal/tenants/${id}/suspend`, { reason });
      
      await new Promise(resolve => setTimeout(resolve, 800));
      console.log(`Tenant ${id} suspendu. Raison: ${reason || 'Non spécifiée'}`);
    } catch (error) {
      console.error('Erreur suspension tenant:', error);
      throw new Error('Impossible de suspendre le tenant');
    }
  }

  // Réactiver un tenant
  async activate(id: string): Promise<void> {
    try {
      // TODO: Remplacer par un vrai appel API
      // await api.post(`/internal/tenants/${id}/activate`);
      
      await new Promise(resolve => setTimeout(resolve, 600));
      console.log(`Tenant ${id} réactivé`);
    } catch (error) {
      console.error('Erreur activation tenant:', error);
      throw new Error('Impossible de réactiver le tenant');
    }
  }

  // Supprimer un tenant (soft delete)
  async delete(id: string): Promise<void> {
    try {
      // TODO: Remplacer par un vrai appel API
      // await api.delete(`/internal/tenants/${id}`);
      
      await new Promise(resolve => setTimeout(resolve, 1200));
      console.log(`Tenant ${id} supprimé`);
    } catch (error) {
      console.error('Erreur suppression tenant:', error);
      throw new Error('Impossible de supprimer le tenant');
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