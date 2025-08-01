// reportService.ts - Service pour la génération de rapports

import { api } from '@/lib/api';

export interface ReportFilters {
  startDate?: string;
  endDate?: string;
  tenantId?: string;
  category?: string;
}

export interface DashboardReportData {
  period: string;
  metrics: {
    tenants: {
      total: number;
      active: number;
      new: number;
      growth_rate: number;
    };
    revenue: {
      total: number;
      monthly_recurring: number;
      growth_rate: number;
      by_plan: Array<{
        plan: string;
        amount: number;
        percentage: number;
      }>;
    };
    users: {
      total: number;
      active: number;
      growth_rate: number;
    };
    vehicles: {
      total: number;
      active: number;
      maintenance_due: number;
    };
    partners: {
      garages: number;
      ct_centers: number;
      insurances: number;
      active_partnerships: number;
    };
    system: {
      uptime: number;
      avg_response_time: number;
      error_rate: number;
      active_alerts: number;
    };
  };
  charts: {
    revenue_evolution: Array<{
      month: string;
      revenue: number;
      tenants: number;
    }>;
    tenant_distribution: Array<{
      plan: string;
      count: number;
      percentage: number;
    }>;
    geographic_distribution: Array<{
      region: string;
      tenants: number;
      revenue: number;
    }>;
  };
}

export interface SupportReportData {
  period: string;
  tickets: {
    total: number;
    open: number;
    resolved: number;
    avg_resolution_time: number;
    satisfaction_score: number;
  };
  categories: Array<{
    category: string;
    count: number;
    avg_resolution_time: number;
  }>;
  agents: Array<{
    name: string;
    tickets_handled: number;
    avg_response_time: number;
    satisfaction_score: number;
  }>;
  trends: Array<{
    date: string;
    tickets_created: number;
    tickets_resolved: number;
  }>;
}

export interface FinancialReportData {
  period: string;
  summary: {
    total_revenue: number;
    recurring_revenue: number;
    one_time_payments: number;
    refunds: number;
    net_revenue: number;
    growth_rate: number;
  };
  by_tenant: Array<{
    tenant_name: string;
    revenue: number;
    plan: string;
    status: string;
    last_payment: string;
  }>;
  by_plan: Array<{
    plan_name: string;
    subscribers: number;
    revenue: number;
    churn_rate: number;
  }>;
  transactions: Array<{
    date: string;
    tenant: string;
    amount: number;
    type: string;
    status: string;
  }>;
}

class ReportService {
  // Génération de rapport du tableau de bord
  async generateDashboardReport(filters: ReportFilters = {}): Promise<Blob> {
    try {
      // En développement, on génère un PDF simulé
      const reportData = await this.getDashboardReportData(filters);
      return this.generateDashboardPDF(reportData);
    } catch (error) {
      console.error('Erreur génération rapport dashboard:', error);
      throw new Error('Impossible de générer le rapport du tableau de bord');
    }
  }

  // Génération de rapport support
  async generateSupportReport(filters: ReportFilters = {}): Promise<Blob> {
    try {
      const reportData = await this.getSupportReportData(filters);
      return this.generateSupportPDF(reportData);
    } catch (error) {
      console.error('Erreur génération rapport support:', error);
      throw new Error('Impossible de générer le rapport support');
    }
  }

  // Génération de rapport financier
  async generateFinancialReport(filters: ReportFilters = {}): Promise<Blob> {
    try {
      const reportData = await this.getFinancialReportData(filters);
      return this.generateFinancialPDF(reportData);
    } catch (error) {
      console.error('Erreur génération rapport financier:', error);
      throw new Error('Impossible de générer le rapport financier');
    }
  }

  // Export Excel des données
  async exportToExcel(type: 'tenants' | 'support' | 'financial', filters: ReportFilters = {}): Promise<Blob> {
    try {
      let data;
      switch (type) {
        case 'tenants':
          data = await this.getTenantsData(filters);
          return this.generateExcel(data, 'Tenants');
        case 'support':
          data = await this.getSupportData(filters);
          return this.generateExcel(data, 'Support');
        case 'financial':
          data = await this.getFinancialData(filters);
          return this.generateExcel(data, 'Financial');
        default:
          throw new Error('Type de rapport non supporté');
      }
    } catch (error) {
      console.error('Erreur export Excel:', error);
      throw new Error('Impossible d\'exporter les données en Excel');
    }
  }

  // Récupération des données du tableau de bord
  private async getDashboardReportData(filters: ReportFilters): Promise<DashboardReportData> {
    // TODO: Remplacer par un vrai appel API
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    return {
      period: `${filters.startDate || '2025-01-01'} - ${filters.endDate || '2025-01-31'}`,
      metrics: {
        tenants: {
          total: 127,
          active: 119,
          new: 8,
          growth_rate: 15.2
        },
        revenue: {
          total: 523480,
          monthly_recurring: 45760,
          growth_rate: 23.1,
          by_plan: [
            { plan: 'Starter', amount: 15280, percentage: 33.4 },
            { plan: 'Professional', amount: 22140, percentage: 48.4 },
            { plan: 'Enterprise', amount: 8340, percentage: 18.2 }
          ]
        },
        users: {
          total: 2847,
          active: 2341,
          growth_rate: 18.7
        },
        vehicles: {
          total: 8945,
          active: 8721,
          maintenance_due: 324
        },
        partners: {
          garages: 45,
          ct_centers: 28,
          insurances: 16,
          active_partnerships: 73
        },
        system: {
          uptime: 99.97,
          avg_response_time: 142,
          error_rate: 0.03,
          active_alerts: 2
        }
      },
      charts: {
        revenue_evolution: [
          { month: 'Jan', revenue: 35000, tenants: 98 },
          { month: 'Fév', revenue: 38500, tenants: 104 },
          { month: 'Mar', revenue: 42000, tenants: 112 },
          { month: 'Avr', revenue: 39800, tenants: 108 },
          { month: 'Mai', revenue: 43200, tenants: 119 },
          { month: 'Jun', revenue: 45760, tenants: 127 }
        ],
        tenant_distribution: [
          { plan: 'Starter', count: 52, percentage: 41 },
          { plan: 'Professional', count: 61, percentage: 48 },
          { plan: 'Enterprise', count: 14, percentage: 11 }
        ],
        geographic_distribution: [
          { region: 'Île-de-France', tenants: 34, revenue: 15680 },
          { region: 'Auvergne-Rhône-Alpes', tenants: 28, revenue: 12940 },
          { region: 'Nouvelle-Aquitaine', tenants: 21, revenue: 9820 },
          { region: 'Occitanie', tenants: 18, revenue: 8350 }
        ]
      }
    };
  }

  // Récupération des données support
  private async getSupportReportData(filters: ReportFilters): Promise<SupportReportData> {
    await new Promise(resolve => setTimeout(resolve, 800));
    
    return {
      period: `${filters.startDate || '2025-01-01'} - ${filters.endDate || '2025-01-31'}`,
      tickets: {
        total: 156,
        open: 23,
        resolved: 133,
        avg_resolution_time: 4.2,
        satisfaction_score: 4.6
      },
      categories: [
        { category: 'Technique', count: 67, avg_resolution_time: 5.1 },
        { category: 'Facturation', count: 34, avg_resolution_time: 2.8 },
        { category: 'Formation', count: 28, avg_resolution_time: 3.5 },
        { category: 'Bug', count: 27, avg_resolution_time: 6.2 }
      ],
      agents: [
        { name: 'Sophie Martin', tickets_handled: 42, avg_response_time: 1.2, satisfaction_score: 4.8 },
        { name: 'Thomas Dubois', tickets_handled: 38, avg_response_time: 1.5, satisfaction_score: 4.7 },
        { name: 'Marie Leroy', tickets_handled: 35, avg_response_time: 1.1, satisfaction_score: 4.9 }
      ],
      trends: [
        { date: '2025-01-01', tickets_created: 5, tickets_resolved: 3 },
        { date: '2025-01-02', tickets_created: 8, tickets_resolved: 6 },
        { date: '2025-01-03', tickets_created: 12, tickets_resolved: 9 }
      ]
    };
  }

  // Récupération des données financières
  private async getFinancialReportData(filters: ReportFilters): Promise<FinancialReportData> {
    await new Promise(resolve => setTimeout(resolve, 1200));
    
    return {
      period: `${filters.startDate || '2025-01-01'} - ${filters.endDate || '2025-01-31'}`,
      summary: {
        total_revenue: 45760,
        recurring_revenue: 42340,
        one_time_payments: 3420,
        refunds: 580,
        net_revenue: 45180,
        growth_rate: 23.1
      },
      by_tenant: [
        { tenant_name: 'Transport Express', revenue: 2890, plan: 'Professional', status: 'active', last_payment: '2025-01-15' },
        { tenant_name: 'LogiTech Solutions', revenue: 1490, plan: 'Starter', status: 'active', last_payment: '2025-01-12' }
      ],
      by_plan: [
        { plan_name: 'Starter', subscribers: 52, revenue: 15280, churn_rate: 5.2 },
        { plan_name: 'Professional', subscribers: 61, revenue: 22140, churn_rate: 3.1 },
        { plan_name: 'Enterprise', subscribers: 14, revenue: 8340, churn_rate: 1.8 }
      ],
      transactions: [
        { date: '2025-01-15', tenant: 'Transport Express', amount: 149, type: 'subscription', status: 'completed' },
        { date: '2025-01-12', tenant: 'LogiTech Solutions', amount: 49, type: 'subscription', status: 'completed' }
      ]
    };
  }

  // Génération PDF du tableau de bord
  private async generateDashboardPDF(data: DashboardReportData): Promise<Blob> {
    // Simulation d'un PDF généré
    const content = this.createDashboardPDFContent(data);
    return new Blob([content], { type: 'application/pdf' });
  }

  // Génération PDF support
  private async generateSupportPDF(data: SupportReportData): Promise<Blob> {
    const content = this.createSupportPDFContent(data);
    return new Blob([content], { type: 'application/pdf' });
  }

  // Génération PDF financier
  private async generateFinancialPDF(data: FinancialReportData): Promise<Blob> {
    const content = this.createFinancialPDFContent(data);
    return new Blob([content], { type: 'application/pdf' });
  }

  // Génération Excel
  private async generateExcel(data: any[], sheetName: string): Promise<Blob> {
    // Simulation d'un fichier Excel
    const csvContent = this.convertToCSV(data);
    return new Blob([csvContent], { type: 'application/vnd.ms-excel' });
  }

  // Utilitaires
  private createDashboardPDFContent(data: DashboardReportData): string {
    return `%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj

2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj

3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
/Contents 4 0 R
>>
endobj

4 0 obj
<<
/Length 200
>>
stream
BT
/F1 12 Tf
72 720 Td
(Rapport FlotteQ - Tableau de bord) Tj
0 -20 Td
(Période: ${data.period}) Tj
0 -20 Td
(Tenants actifs: ${data.metrics.tenants.active}) Tj
0 -20 Td
(Revenus: ${data.metrics.revenue.total}€) Tj
ET
endstream
endobj

xref
0 5
0000000000 65535 f 
0000000010 00000 n 
0000000053 00000 n 
0000000109 00000 n 
0000000205 00000 n 
trailer
<<
/Size 5
/Root 1 0 R
>>
startxref
456
%%EOF`;
  }

  private createSupportPDFContent(data: SupportReportData): string {
    return `%PDF-1.4 (Support Report Content for ${data.period})`;
  }

  private createFinancialPDFContent(data: FinancialReportData): string {
    return `%PDF-1.4 (Financial Report Content for ${data.period})`;
  }

  private convertToCSV(data: any[]): string {
    if (!data || data.length === 0) return '';
    
    const headers = Object.keys(data[0]);
    const csvRows = [
      headers.join(','),
      ...data.map(row => headers.map(header => JSON.stringify(row[header] || '')).join(','))
    ];
    
    return csvRows.join('\n');
  }

  // Récupération de données pour Excel
  private async getTenantsData(filters: ReportFilters): Promise<any[]> {
    // Mock data
    return [
      { name: 'Transport Express', domain: 'transport-express.com', users: 12, vehicles: 45, plan: 'Professional' },
      { name: 'LogiTech Solutions', domain: 'logitech.com', users: 8, vehicles: 23, plan: 'Starter' }
    ];
  }

  private async getSupportData(filters: ReportFilters): Promise<any[]> {
    return [
      { ticket_id: 'T001', category: 'Technique', status: 'Résolu', created_at: '2025-01-15', resolved_at: '2025-01-16' },
      { ticket_id: 'T002', category: 'Facturation', status: 'Ouvert', created_at: '2025-01-18', resolved_at: null }
    ];
  }

  private async getFinancialData(filters: ReportFilters): Promise<any[]> {
    return [
      { tenant: 'Transport Express', amount: 149, date: '2025-01-15', type: 'Abonnement', status: 'Payé' },
      { tenant: 'LogiTech Solutions', amount: 49, date: '2025-01-12', type: 'Abonnement', status: 'Payé' }
    ];
  }
}

export const reportService = new ReportService();