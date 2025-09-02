<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'record_type',
        'revenue_category',
        'tenant_id',
        'user_id',
        'subscription_id',
        'transaction_id',
        'partner_id',
        'amount',
        'currency',
        'exchange_rate',
        'amount_eur',
        'payment_method',
        'payment_gateway',
        'gateway_transaction_id',
        'gateway_fee',
        'net_amount',
        'tax_amount',
        'tax_rate',
        'description',
        'billing_period_start',
        'billing_period_end',
        'invoice_number',
        'invoice_date',
        'due_date',
        'paid_at',
        'status',
        'refund_amount',
        'refunded_at',
        'refund_reason',
        'recurring',
        'recurrence_interval',
        'next_billing_date',
        'source',
        'campaign',
        'referral_code',
        'discount_amount',
        'discount_code',
        'metadata',
        'notes',
        'reconciled',
        'reconciled_at',
        'exported',
        'exported_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'amount_eur' => 'decimal:2',
        'gateway_fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'recurring' => 'boolean',
        'reconciled' => 'boolean',
        'exported' => 'boolean',
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'next_billing_date' => 'date',
        'reconciled_at' => 'datetime',
        'exported_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'subscription_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                     ->where('due_date', '<', now());
    }

    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('invoice_date', [$startDate, $endDate]);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('revenue_category', $category);
    }

    public function scopeReconciled($query)
    {
        return $query->where('reconciled', true);
    }

    public function scopeUnreconciled($query)
    {
        return $query->where('reconciled', false);
    }

    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);
    }

    public function markAsRefunded($amount, $reason = null)
    {
        $this->update([
            'status' => 'refunded',
            'refund_amount' => $amount,
            'refunded_at' => now(),
            'refund_reason' => $reason
        ]);
    }

    public function calculateNetAmount()
    {
        return $this->amount - $this->gateway_fee - $this->tax_amount + $this->discount_amount;
    }

    public static function getTotalRevenue($startDate = null, $endDate = null)
    {
        $query = self::paid();
        
        if ($startDate && $endDate) {
            $query->byPeriod($startDate, $endDate);
        }
        
        return $query->sum('net_amount');
    }

    public static function getRevenueByCategory($startDate = null, $endDate = null)
    {
        $query = self::paid();
        
        if ($startDate && $endDate) {
            $query->byPeriod($startDate, $endDate);
        }
        
        return $query->groupBy('revenue_category')
                     ->selectRaw('revenue_category, SUM(net_amount) as total')
                     ->pluck('total', 'revenue_category');
    }
}