// analyticsService.ts - Service d'analytics et monitoring FlotteQ
import { api } from "@/lib/api";

// === INTERFACES ===

export interface PlatformMetrics {
  total_tenants: number;
  active_tenants: number;
  total_vehicles: number;
  active_vehicles: number;
  total_users: number;
  active_users: number;
  total_revenue: number;
  monthly_revenue: number;
  growth_rate: number;
  uptime_percentage: number;
  api_requests_today: number;
  api_response_time: number;
}

export interface UsageAnalytics {
  daily_active_users: number;
  weekly_active_users: number;
  monthly_active_users: number;
  session_duration_avg: number;
  page_views_today: number;
  feature_usage: Array<{
    feature_name: string;
    usage_count: number;
    unique_users: number;
    adoption_rate: number;
  }>;
  top_pages: Array<{
    page: string;
    views: number;
    unique_visitors: number;
    avg_time_on_page: number;
  }>;
}

export interface PerformanceMetrics {
  api_response_times: Array<{
    endpoint: string;
    avg_response_time: number;
    requests_count: number;
    error_rate: number;
  }>;
  database_performance: {
    query_time_avg: number;
    slow_queries_count: number;
    connection_pool_usage: number;
  };
  server_metrics: {
    cpu_usage: number;
    memory_usage: number;
    disk_usage: number;
    network_io: number;
  };
  error_rates: Array<{
    error_type: string;
    count: number;
    percentage: number;
  }>;
}

export interface BehaviorAnalytics {
  user_journeys: Array<{
    journey_name: string;
    completion_rate: number;
    avg_steps: number;
    drop_off_points: Array<{
      step: string;
      drop_off_rate: number;
    }>;
  }>;
  feature_adoption: Array<{
    feature: string;
    adoption_rate: number;
    time_to_adopt_days: number;
    user_satisfaction: number;
  }>;
  user_retention: {
    day_1: number;
    day_7: number;
    day_30: number;
    day_90: number;
  };
  churn_analysis: {
    churn_rate: number;
    churn_reasons: Array<{
      reason: string;
      percentage: number;
    }>;
    at_risk_users: number;
  };
}

export interface RevenueAnalytics {
  mrr: number; // Monthly Recurring Revenue
  arr: number; // Annual Recurring Revenue
  arpu: number; // Average Revenue Per User
  ltv: number; // Lifetime Value
  cac: number; // Customer Acquisition Cost
  revenue_by_plan: Array<{
    plan_name: string;
    revenue: number;
    subscribers: number;
    percentage: number;
  }>;
  revenue_trends: Array<{
    period: string;
    revenue: number;
    new_revenue: number;
    churn_revenue: number;
    expansion_revenue: number;
  }>;
  cohort_analysis: Array<{
    cohort: string;
    month_0: number;
    month_1: number;
    month_3: number;
    month_6: number;
    month_12: number;
  }>;
}

export interface GeographicAnalytics {
  users_by_country: Array<{
    country: string;
    users: number;
    revenue: number;
    growth_rate: number;
  }>;
  users_by_region: Array<{
    region: string;
    users: number;
    vehicles: number;
    activity_score: number;
  }>;
  market_penetration: Array<{
    market: string;
    penetration_rate: number;
    opportunity_score: number;
  }>;
}

export interface TechnicalAnalytics {
  system_health: {
    status: 'healthy' | 'warning' | 'critical';
    uptime: number;
    last_incident: string | null;
    active_alerts: number;
  };
  api_analytics: {
    total_requests: number;
    successful_requests: number;
    failed_requests: number;
    avg_response_time: number;
    rate_limited_requests: number;
  };
  security_metrics: {
    login_attempts: number;
    failed_logins: number;
    suspicious_activities: number;
    blocked_ips: number;
  };
  infrastructure_costs: {
    monthly_cost: number;
    cost_per_user: number;
    cost_trends: Array<{
      month: string;
      cost: number;
      usage: number;
    }>;
  };
}

export interface AnalyticsFilters {
  date_range?: {
    start_date: string;
    end_date: string;
  };
  tenant_id?: number;
  country?: string;
  plan_type?: string;
  user_segment?: string;
}

export interface RealtimeMetrics {
  current_online_users: number;
  active_sessions: number;
  current_api_rps: number; // requests per second
  system_load: number;
  memory_usage_percentage: number;
  cpu_usage_percentage: number;
  recent_errors: Array<{
    timestamp: string;
    error_type: string;
    message: string;
    affected_users: number;
  }>;
}

// === SERVICE ===

export const analyticsService = {
  // === PLATFORM METRICS ===
  
  async getPlatformMetrics(): Promise<PlatformMetrics> {
    const response = await api.get('/analytics/platform-metrics');
    return response.data;
  },

  async getUsageAnalytics(filters?: AnalyticsFilters): Promise<UsageAnalytics> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          if (typeof value === 'object') {
            params.append(key, JSON.stringify(value));
          } else {
            params.append(key, value.toString());
          }
        }
      });
    }

    const response = await api.get(`/analytics/usage?${params}`);
    return response.data;
  },

  async getPerformanceMetrics(): Promise<PerformanceMetrics> {
    const response = await api.get('/analytics/performance');
    return response.data;
  },

  async getBehaviorAnalytics(filters?: AnalyticsFilters): Promise<BehaviorAnalytics> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          if (typeof value === 'object') {
            params.append(key, JSON.stringify(value));
          } else {
            params.append(key, value.toString());
          }
        }
      });
    }

    const response = await api.get(`/analytics/behavior?${params}`);
    return response.data;
  },

  // === REVENUE ANALYTICS ===
  
  async getRevenueAnalytics(filters?: AnalyticsFilters): Promise<RevenueAnalytics> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          if (typeof value === 'object') {
            params.append(key, JSON.stringify(value));
          } else {
            params.append(key, value.toString());
          }
        }
      });
    }

    const response = await api.get(`/analytics/revenue?${params}`);
    return response.data;
  },

  async getCohortAnalysis(startDate: string, endDate: string) {
    const response = await api.get('/analytics/cohorts', {
      params: { start_date: startDate, end_date: endDate }
    });
    return response.data;
  },

  // === GEOGRAPHIC ANALYTICS ===
  
  async getGeographicAnalytics(): Promise<GeographicAnalytics> {
    const response = await api.get('/analytics/geographic');
    return response.data;
  },

  // === TECHNICAL ANALYTICS ===
  
  async getTechnicalAnalytics(): Promise<TechnicalAnalytics> {
    const response = await api.get('/analytics/technical');
    return response.data;
  },

  async getRealtimeMetrics(): Promise<RealtimeMetrics> {
    const response = await api.get('/analytics/realtime');
    return response.data;
  },

  // === CUSTOM REPORTS ===
  
  async generateCustomReport(reportConfig: {
    metrics: string[];
    filters: AnalyticsFilters;
    groupBy?: string;
    timeGranularity?: 'hour' | 'day' | 'week' | 'month';
  }) {
    const response = await api.post('/analytics/custom-report', reportConfig);
    return response.data;
  },

  async getAvailableMetrics() {
    const response = await api.get('/analytics/available-metrics');
    return response.data;
  },

  // === ALERTS & MONITORING ===
  
  async getSystemAlerts() {
    const response = await api.get('/analytics/alerts');
    return response.data;
  },

  async createAlert(alertConfig: {
    name: string;
    metric: string;
    condition: string;
    threshold: number;
    notification_channels: string[];
  }) {
    const response = await api.post('/analytics/alerts', alertConfig);
    return response.data;
  },

  async getSystemLogs(page: number = 1, filters?: {
    level?: string;
    component?: string;
    search?: string;
    start_date?: string;
    end_date?: string;
  }) {
    const params = new URLSearchParams({
      page: page.toString(),
    });

    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }

    const response = await api.get(`/analytics/logs?${params}`);
    return response.data;
  },

  // === EXPORT & SHARING ===
  
  async exportReport(reportType: string, format: 'pdf' | 'excel' | 'csv', filters?: AnalyticsFilters): Promise<Blob> {
    const params = new URLSearchParams({
      report_type: reportType,
      format,
    });

    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          if (typeof value === 'object') {
            params.append(key, JSON.stringify(value));
          } else {
            params.append(key, value.toString());
          }
        }
      });
    }

    const response = await api.get(`/analytics/export?${params}`, {
      responseType: 'blob'
    });
    return response.data;
  },

  async scheduleDashboardReport(config: {
    dashboard_id: string;
    recipients: string[];
    frequency: 'daily' | 'weekly' | 'monthly';
    format: 'pdf' | 'excel';
  }) {
    const response = await api.post('/analytics/schedule-report', config);
    return response.data;
  },

  // === BENCHMARKING ===
  
  async getIndustryBenchmarks(industry?: string) {
    const params = industry ? { industry } : {};
    const response = await api.get('/analytics/benchmarks', { params });
    return response.data;
  },

  async compareMetrics(compareWith: 'previous_period' | 'industry_average' | 'top_performers') {
    const response = await api.get('/analytics/compare', {
      params: { compare_with: compareWith }
    });
    return response.data;
  }
}; 