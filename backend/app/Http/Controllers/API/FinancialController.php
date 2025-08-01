<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\UserSubscription;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialController extends Controller
{
    /**
     * Get revenue overview and statistics
     */
    public function getRevenue(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month'); // month, quarter, year
        
        $startDate = match($period) {
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        // Total revenue from subscriptions
        $subscriptionRevenue = UserSubscription::join('subscriptions', 'user_subscriptions.subscription_id', '=', 'subscriptions.id')
            ->where('user_subscriptions.is_active', true)
            ->where('user_subscriptions.start_date', '>=', $startDate)
            ->sum('subscriptions.price');

        // Revenue from transactions (commissions, fees, etc.)
        $transactionRevenue = Transaction::where('type', 'income')
            ->where('created_at', '>=', $startDate)
            ->sum('amount');

        // Monthly recurring revenue (MRR)
        $mrr = UserSubscription::join('subscriptions', 'user_subscriptions.subscription_id', '=', 'subscriptions.id')
            ->where('user_subscriptions.is_active', true)
            ->where('subscriptions.billing_cycle', 'monthly')
            ->sum('subscriptions.price');

        // Annual recurring revenue (ARR)
        $arr = $mrr * 12;

        // Revenue by month for charts
        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue = UserSubscription::join('subscriptions', 'user_subscriptions.subscription_id', '=', 'subscriptions.id')
                ->whereBetween('user_subscriptions.created_at', [$month->startOfMonth(), $month->endOfMonth()])
                ->sum('subscriptions.price');
            
            $monthlyRevenue[] = [
                'month' => $month->format('M Y'),
                'revenue' => round($revenue, 2)
            ];
        }

        // Revenue by subscription plan
        $revenueByPlan = UserSubscription::join('subscriptions', 'user_subscriptions.subscription_id', '=', 'subscriptions.id')
            ->select('subscriptions.name', DB::raw('SUM(subscriptions.price) as revenue'), DB::raw('COUNT(*) as subscribers'))
            ->where('user_subscriptions.is_active', true)
            ->groupBy('subscriptions.name')
            ->get();

        return response()->json([
            'total_revenue' => round($subscriptionRevenue + $transactionRevenue, 2),
            'subscription_revenue' => round($subscriptionRevenue, 2),
            'transaction_revenue' => round($transactionRevenue, 2),
            'mrr' => round($mrr, 2),
            'arr' => round($arr, 2),
            'monthly_revenue' => $monthlyRevenue,
            'revenue_by_plan' => $revenueByPlan,
            'period' => $period
        ]);
    }

    /**
     * Get commissions overview
     */
    public function getCommissions(): JsonResponse
    {
        // Partner commissions (garages, insurance, etc.)
        $partnerCommissions = Transaction::where('type', 'commission')
            ->where('category', 'partner')
            ->sum('amount');

        // Employee commissions
        $employeeCommissions = Transaction::where('type', 'commission')
            ->where('category', 'employee')
            ->sum('amount');

        // Referral commissions
        $referralCommissions = Transaction::where('type', 'commission')
            ->where('category', 'referral')
            ->sum('amount');

        // Commission trends
        $commissionTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $commission = Transaction::where('type', 'commission')
                ->whereBetween('created_at', [$month->startOfMonth(), $month->endOfMonth()])
                ->sum('amount');
            
            $commissionTrends[] = [
                'month' => $month->format('M Y'),
                'commission' => round($commission, 2)
            ];
        }

        // Top earning partners
        $topPartners = Transaction::select('recipient_name', DB::raw('SUM(amount) as total_commission'))
            ->where('type', 'commission')
            ->where('category', 'partner')
            ->groupBy('recipient_name')
            ->orderBy('total_commission', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'total_commissions' => round($partnerCommissions + $employeeCommissions + $referralCommissions, 2),
            'partner_commissions' => round($partnerCommissions, 2),
            'employee_commissions' => round($employeeCommissions, 2),
            'referral_commissions' => round($referralCommissions, 2),
            'commission_trends' => $commissionTrends,
            'top_partners' => $topPartners
        ]);
    }

    /**
     * Get financial reports list
     */
    public function getReports(): JsonResponse
    {
        // Mock data for financial reports
        $reports = [
            [
                'id' => 1,
                'name' => 'Monthly Revenue Report',
                'type' => 'revenue',
                'period' => 'monthly',
                'last_generated' => now()->subDays(1)->toISOString(),
                'status' => 'completed'
            ],
            [
                'id' => 2,
                'name' => 'Partner Commission Report',
                'type' => 'commission',
                'period' => 'monthly',
                'last_generated' => now()->subDays(3)->toISOString(),
                'status' => 'completed'
            ],
            [
                'id' => 3,
                'name' => 'Subscription Analytics Report',
                'type' => 'subscription',
                'period' => 'quarterly',
                'last_generated' => now()->subWeeks(2)->toISOString(),
                'status' => 'completed'
            ],
            [
                'id' => 4,
                'name' => 'Financial Summary Report',
                'type' => 'summary',
                'period' => 'yearly',
                'last_generated' => now()->subMonth()->toISOString(),
                'status' => 'pending'
            ]
        ];

        return response()->json($reports);
    }

    /**
     * Generate a new financial report
     */
    public function generateReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:revenue,commission,subscription,summary',
            'period' => 'required|in:monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);

        // Mock report generation (in real implementation, this would queue a job)
        $reportId = rand(1000, 9999);

        return response()->json([
            'message' => 'Financial report generation started',
            'report_id' => $reportId,
            'estimated_completion' => now()->addMinutes(5)->toISOString(),
            'download_url' => "/api/internal/financial/reports/{$reportId}/download"
        ]);
    }
}