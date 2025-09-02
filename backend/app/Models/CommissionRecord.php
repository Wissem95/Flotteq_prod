<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'tenant_id',
        'transaction_id',
        'revenue_record_id',
        'commission_type',
        'base_amount',
        'commission_rate',
        'commission_amount',
        'currency',
        'tier_level',
        'bonus_rate',
        'bonus_amount',
        'total_amount',
        'calculation_method',
        'calculation_details',
        'period_start',
        'period_end',
        'transactions_count',
        'status',
        'approved_by',
        'approved_at',
        'paid_amount',
        'paid_at',
        'payment_method',
        'payment_reference',
        'invoice_number',
        'invoice_date',
        'due_date',
        'notes',
        'adjustments',
        'adjustment_reason',
        'dispute_status',
        'dispute_notes',
        'metadata'
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'commission_rate' => 'decimal:4',
        'commission_amount' => 'decimal:2',
        'bonus_rate' => 'decimal:4',
        'bonus_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'adjustments' => 'decimal:2',
        'calculation_details' => 'array',
        'metadata' => 'array',
        'period_start' => 'date',
        'period_end' => 'date',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'invoice_date' => 'date',
        'due_date' => 'date',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function revenueRecord()
    {
        return $this->belongsTo(RevenueRecord::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(InternalEmployee::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['pending', 'approved']);
    }

    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('period_start', [$startDate, $endDate]);
    }

    public function scopeByPartner($query, $partnerId)
    {
        return $query->where('partner_id', $partnerId);
    }

    public function approve($employeeId)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $employeeId,
            'approved_at' => now()
        ]);
    }

    public function markAsPaid($paymentMethod, $paymentReference = null)
    {
        $this->update([
            'status' => 'paid',
            'paid_amount' => $this->total_amount,
            'paid_at' => now(),
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference
        ]);
    }

    public function calculateCommission()
    {
        $commission = $this->base_amount * ($this->commission_rate / 100);
        $bonus = 0;

        if ($this->bonus_rate) {
            $bonus = $this->base_amount * ($this->bonus_rate / 100);
        }

        $total = $commission + $bonus + ($this->adjustments ?? 0);

        $this->update([
            'commission_amount' => $commission,
            'bonus_amount' => $bonus,
            'total_amount' => $total
        ]);

        return $total;
    }

    public function addAdjustment($amount, $reason)
    {
        $currentAdjustments = $this->adjustments ?? 0;
        $newAdjustments = $currentAdjustments + $amount;

        $this->update([
            'adjustments' => $newAdjustments,
            'adjustment_reason' => $reason,
            'total_amount' => $this->commission_amount + $this->bonus_amount + $newAdjustments
        ]);
    }

    public static function getTotalCommissionsByPartner($partnerId, $startDate = null, $endDate = null)
    {
        $query = self::byPartner($partnerId)->paid();

        if ($startDate && $endDate) {
            $query->byPeriod($startDate, $endDate);
        }

        return $query->sum('paid_amount');
    }

    public static function getPendingCommissions()
    {
        return self::unpaid()->sum('total_amount');
    }
}