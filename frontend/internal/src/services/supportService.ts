// supportService.ts - Service de gestion du support client FlotteQ

import { api } from "@/lib/api";

// Types pour le système de support
export interface SupportTicket {
  id: number;
  ticket_number: string;
  subject: string;
  description: string;
  status: 'open' | 'in_progress' | 'resolved' | 'closed';
  priority: 'low' | 'medium' | 'high' | 'urgent';
  category: 'technical' | 'billing' | 'general' | 'feature_request' | 'bug_report';
  tenant_id: number;
  tenant_name: string;
  user_id: number;
  user_name: string;
  user_email: string;
  assigned_to?: number;
  assigned_to_name?: string;
  messages: SupportMessage[];
  attachments?: SupportAttachment[];
  tags?: string[];
  created_at: string;
  updated_at: string;
  resolved_at?: string;
  closed_at?: string;
  response_time_hours?: number;
  resolution_time_hours?: number;
}

export interface SupportMessage {
  id: number;
  ticket_id: number;
  sender_type: 'customer' | 'admin';
  sender_id: number;
  sender_name: string;
  message: string;
  is_internal: boolean;
  attachments?: SupportAttachment[];
  created_at: string;
}

export interface SupportAttachment {
  id: number;
  filename: string;
  original_name: string;
  file_size: number;
  mime_type: string;
  url: string;
  created_at: string;
}

export interface CreateTicketData {
  subject: string;
  description: string;
  priority: 'low' | 'medium' | 'high' | 'urgent';
  category: 'technical' | 'billing' | 'general' | 'feature_request' | 'bug_report';
  tenant_id: number;
  user_id: number;
  tags?: string[];
}

export interface UpdateTicketData {
  subject?: string;
  status?: 'open' | 'in_progress' | 'resolved' | 'closed';
  priority?: 'low' | 'medium' | 'high' | 'urgent';
  category?: string;
  assigned_to?: number;
  tags?: string[];
}

export interface SupportFilters {
  status?: 'open' | 'in_progress' | 'resolved' | 'closed';
  priority?: 'low' | 'medium' | 'high' | 'urgent';
  category?: string;
  assigned_to?: number;
  tenant_id?: number;
  search?: string;
  date_from?: string;
  date_to?: string;
}

export interface SupportStats {
  total: number;
  by_status: {
    open: number;
    in_progress: number;
    resolved: number;
    closed: number;
  };
  by_priority: {
    low: number;
    medium: number;
    high: number;
    urgent: number;
  };
  average_response_time_hours: number;
  average_resolution_time_hours: number;
  open_tickets_older_than_24h: number;
  satisfaction_rating?: number;
}

export interface QuickResponse {
  id: number;
  title: string;
  content: string;
  category: string;
  is_active: boolean;
  usage_count: number;
  created_at: string;
}

/**
 * Service de gestion du support client FlotteQ
 */
export const supportService = {
  /**
   * Récupérer tous les tickets avec filtres
   */
  async getTickets(
    page: number = 1,
    perPage: number = 20,
    filters?: SupportFilters
  ): Promise<{
    tickets: SupportTicket[];
    pagination: {
      current_page: number;
      per_page: number;
      total: number;
      last_page: number;
    };
  }> {
    try {
      let url = `/support/tickets?page=${page}&per_page=${perPage}`;
      
      if (filters) {
        const params = new URLSearchParams();
        Object.entries(filters).forEach(([key, value]) => {
          if (value !== undefined && value !== null) {
            params.append(key, value.toString());
          }
        });
        url += `&${params.toString()}`;
      }
      
      const response = await api.get(url);
      return response.data;
    } catch (error: any) {
      console.error("Erreur lors de la récupération des tickets:", error);
      throw new Error(error.response?.data?.message || "Erreur lors de la récupération");
    }
  },

  /**
   * Récupérer un ticket par ID
   */
  async getTicket(id: number): Promise<SupportTicket> {
    try {
      const response = await api.get(`/support/tickets/${id}`);
      return response.data.ticket;
    } catch (error: any) {
      console.error(`Erreur lors de la récupération du ticket ${id}:`, error);
      throw new Error(error.response?.data?.message || "Ticket non trouvé");
    }
  },

  /**
   * Créer un nouveau ticket
   */
  async createTicket(data: CreateTicketData): Promise<SupportTicket> {
    try {
      const response = await api.post('/support/tickets', data);
      return response.data.ticket;
    } catch (error: any) {
      console.error("Erreur lors de la création du ticket:", error);
      throw new Error(error.response?.data?.message || "Erreur lors de la création");
    }
  },

  /**
   * Mettre à jour un ticket
   */
  async updateTicket(id: number, data: UpdateTicketData): Promise<SupportTicket> {
    try {
      const response = await api.put(`/support/tickets/${id}`, data);
      return response.data.ticket;
    } catch (error: any) {
      console.error(`Erreur lors de la mise à jour du ticket ${id}:`, error);
      throw new Error(error.response?.data?.message || "Erreur lors de la mise à jour");
    }
  },

  /**
   * Ajouter un message à un ticket
   */
  async addMessage(
    ticketId: number, 
    message: string, 
    isInternal: boolean = false
  ): Promise<SupportMessage> {
    try {
      const response = await api.post(`/support/tickets/${ticketId}/messages`, {
        message,
        is_internal: isInternal,
      });
      return response.data.message;
    } catch (error: any) {
      console.error("Erreur lors de l'ajout du message:", error);
      throw new Error(error.response?.data?.message || "Erreur lors de l'ajout du message");
    }
  },

  /**
   * Assigner un ticket à un employé
   */
  async assignTicket(ticketId: number | string, employeeId: number | string): Promise<SupportTicket> {
    try {
      const response = await api.patch(`/support/tickets/${ticketId}/assign`, {
        assigned_to: employeeId,
      });
      return response.data.ticket;
    } catch (error: any) {
      console.error("Erreur lors de l'assignation:", error);
      throw new Error(error.response?.data?.message || "Erreur lors de l'assignation");
    }
  },

  /**
   * Mettre à jour le statut d'un ticket
   */
  async updateTicketStatus(ticketId: string, status: SupportTicket['status']): Promise<void> {
    try {
      await api.patch(`/support/tickets/${ticketId}/status`, { status });
    } catch (error: any) {
      console.error("Erreur lors de la mise à jour du statut:", error);
      throw new Error(error.response?.data?.message || "Erreur lors de la mise à jour");
    }
  },

  /**
   * Changer le statut d'un ticket
   */
  async changeStatus(
    ticketId: number, 
    status: 'open' | 'in_progress' | 'resolved' | 'closed'
  ): Promise<SupportTicket> {
    try {
      const response = await api.patch(`/support/tickets/${ticketId}/status`, {
        status,
      });
      return response.data.ticket;
    } catch (error: any) {
      console.error("Erreur lors du changement de statut:", error);
      throw new Error(error.response?.data?.message || "Erreur lors du changement de statut");
    }
  },

  /**
   * Récupérer les statistiques du support
   */
  async getStats(): Promise<SupportStats> {
    try {
      const response = await api.get('/support/stats');
      return response.data;
    } catch (error: any) {
      console.error("Erreur lors de la récupération des statistiques:", error);
      throw new Error(error.response?.data?.message || "Erreur lors de la récupération des statistiques");
    }
  },

  /**
   * Récupérer les réponses rapides
   */
  async getQuickResponses(): Promise<QuickResponse[]> {
    try {
      const response = await api.get('/support/quick-responses');
      return response.data.quick_responses;
    } catch (error: any) {
      console.error("Erreur lors de la récupération des réponses rapides:", error);
      throw new Error(error.response?.data?.message || "Erreur lors de la récupération");
    }
  },

  /**
   * Utiliser une réponse rapide
   */
  async useQuickResponse(id: number): Promise<QuickResponse> {
    try {
      const response = await api.post(`/support/quick-responses/${id}/use`);
      return response.data.quick_response;
    } catch (error: any) {
      console.error("Erreur lors de l'utilisation de la réponse rapide:", error);
      throw new Error(error.response?.data?.message || "Erreur lors de l'utilisation");
    }
  },

  /**
   * Rechercher dans les tickets
   */
  async searchTickets(query: string): Promise<SupportTicket[]> {
    try {
      const response = await api.get(`/support/search?q=${encodeURIComponent(query)}`);
      return response.data.tickets;
    } catch (error: any) {
      console.error("Erreur lors de la recherche:", error);
      throw new Error(error.response?.data?.message || "Erreur lors de la recherche");
    }
  },

  /**
   * Exporter les tickets
   */
  async exportTickets(
    format: 'csv' | 'excel',
    filters?: SupportFilters
  ): Promise<Blob> {
    try {
      let url = `/support/export?format=${format}`;
      
      if (filters) {
        const params = new URLSearchParams();
        Object.entries(filters).forEach(([key, value]) => {
          if (value !== undefined && value !== null) {
            params.append(key, value.toString());
          }
        });
        url += `&${params.toString()}`;
      }
      
      const response = await api.get(url, {
        responseType: 'blob'
      });
      
      return response.data;
    } catch (error: any) {
      console.error("Erreur lors de l'export:", error);
      throw new Error(error.response?.data?.message || "Erreur lors de l'export");
    }
  },
}; 