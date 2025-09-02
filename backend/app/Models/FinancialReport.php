<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_type',
        'report_name',
        'period_type',
        'period_start',
        'period_end',
        'tenant_id',
        'generated_by',
        'total_revenue',
        'total_expenses',
        'net_profit',
        'gross_margin',
        'operating_margin',
        'revenue_breakdown',
        'expense_breakdown',
        'revenue_by_category',
        'revenue_by_tenant',
        'revenue_by_subscription',
        'commissions_paid',
        'taxes_collected',
        'refunds_processed',
        'outstanding_receivables',
        'cash_flow',
        'financial_metrics',
        'comparative_data',
        'year_over_year_growth',
        'month_over_month_growth',
        'forecast_data',
        'budget_variance',
        'key_insights',
        'recommendations',
        'export_format',
        'file_path',
        'file_size',
        'generated_at',
        'status',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'notes',
        'metadata'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_revenue' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'gross_margin' => 'decimal:2',
        'operating_margin' => 'decimal:2',
        'commissions_paid' => 'decimal:2',
        'taxes_collected' => 'decimal:2',
        'refunds_processed' => 'decimal:2',
        'outstanding_receivables' => 'decimal:2',
        'cash_flow' => 'decimal:2',
        'year_over_year_growth' => 'decimal:2',
        'month_over_month_growth' => 'decimal:2',
        'budget_variance' => 'decimal:2',
        'revenue_breakdown' => 'array',
        'expense_breakdown' => 'array',
        'revenue_by_category' => 'array',
        'revenue_by_tenant' => 'array',
        'revenue_by_subscription' => 'array',
        'financial_metrics' => 'array',
        'comparative_data' => 'array',
        'forecast_data' => 'array',
        'key_insights' => 'array',
        'recommendations' => 'array',
        'metadata' => 'array',
        'generated_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(InternalEmployee::class, 'generated_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(InternalEmployee::class, 'reviewed_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(InternalEmployee::class, 'approved_by');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->where('period_start', '>=', $startDate)
                     ->where('period_end', '<=', $endDate);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function markAsReviewed($employeeId)
    {
        $this->update([
            'status' => 'reviewed',
            'reviewed_by' => $employeeId,
            'reviewed_at' => now()
        ]);
    }

    public function markAsApproved($employeeId)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $employeeId,
            'approved_at' => now()
        ]);
    }

    public function calculateProfitMargin()
    {
        if ($this->total_revenue == 0) {
            return 0;
        }

        return (($this->total_revenue - $this->total_expenses) / $this->total_revenue) * 100;
    }

    public function calculateGrowth($previousReport)
    {
        if (!$previousReport || $previousReport->total_revenue == 0) {
            return 0;
        }

        return (($this->total_revenue - $previousReport->total_revenue) / $previousReport->total_revenue) * 100;
    }

    public function generateInsights()
    {
        $insights = [];

        // Analyse de la rentabilité
        $profitMargin = $this->calculateProfitMargin();
        if ($profitMargin < 10) {
            $insights[] = [
                'type' => 'warning',
                'message' => 'La marge bénéficiaire est faible (' . round($profitMargin, 2) . '%)'
            ];
        } elseif ($profitMargin > 30) {
            $insights[] = [
                'type' => 'success',
                'message' => 'Excellente marge bénéficiaire (' . round($profitMargin, 2) . '%)'
            ];
        }

        // Analyse de la croissance
        if ($this->year_over_year_growth < 0) {
            $insights[] = [
                'type' => 'danger',
                'message' => 'Baisse du chiffre d\'affaires par rapport à l\'année précédente'
            ];
        } elseif ($this->year_over_year_growth > 20) {
            $insights[] = [
                'type' => 'success',
                'message' => 'Forte croissance annuelle (' . round($this->year_over_year_growth, 2) . '%)'
            ];
        }

        $this->update(['key_insights' => $insights]);

        return $insights;
    }

    public static function generateMonthlyReport($month, $year)
    {
        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Calculer les métriques
        $totalRevenue = RevenueRecord::byPeriod($startDate, $endDate)->paid()->sum('net_amount');
        $commissionsPaid = CommissionRecord::byPeriod($startDate, $endDate)->paid()->sum('paid_amount');
        
        return self::create([
            'report_type' => 'monthly',
            'report_name' => 'Rapport Mensuel ' . $startDate->format('F Y'),
            'period_type' => 'monthly',
            'period_start' => $startDate,
            'period_end' => $endDate,
            'total_revenue' => $totalRevenue,
            'commissions_paid' => $commissionsPaid,
            'net_profit' => $totalRevenue - $commissionsPaid,
            'generated_at' => now(),
            'status' => 'pending'
        ]);
    }
}