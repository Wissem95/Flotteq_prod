<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'discount_type',
        'discount_value',
        'discount_percentage',
        'max_discount_amount',
        'min_purchase_amount',
        'applies_to',
        'applicable_plans',
        'applicable_tenants',
        'applicable_categories',
        'excluded_items',
        'start_date',
        'end_date',
        'usage_limit_total',
        'usage_limit_per_user',
        'usage_limit_per_tenant',
        'current_usage_count',
        'requires_code',
        'auto_apply',
        'stackable',
        'priority',
        'conditions',
        'target_audience',
        'new_users_only',
        'existing_users_only',
        'first_purchase_only',
        'recurring_discount',
        'recurring_duration_months',
        'trial_extension_days',
        'referral_required',
        'referral_bonus',
        'display_banner',
        'banner_text',
        'banner_image',
        'email_template',
        'terms_and_conditions',
        'is_active',
        'is_featured',
        'created_by',
        'approved_by',
        'approved_at',
        'analytics_data',
        'conversion_rate',
        'total_revenue_generated',
        'total_discount_given',
        'metadata'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'applicable_plans' => 'array',
        'applicable_tenants' => 'array',
        'applicable_categories' => 'array',
        'excluded_items' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'requires_code' => 'boolean',
        'auto_apply' => 'boolean',
        'stackable' => 'boolean',
        'new_users_only' => 'boolean',
        'existing_users_only' => 'boolean',
        'first_purchase_only' => 'boolean',
        'recurring_discount' => 'boolean',
        'referral_required' => 'boolean',
        'display_banner' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'approved_at' => 'datetime',
        'conditions' => 'array',
        'target_audience' => 'array',
        'analytics_data' => 'array',
        'conversion_rate' => 'decimal:2',
        'total_revenue_generated' => 'decimal:2',
        'total_discount_given' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function rules()
    {
        return $this->hasMany(PromotionRule::class);
    }

    public function usages()
    {
        return $this->hasMany(PromotionUsage::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(InternalEmployee::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(InternalEmployee::class, 'approved_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('start_date', '<=', now())
                     ->where(function ($q) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', now());
                     });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    public function scopeAutoApplicable($query)
    {
        return $query->where('auto_apply', true);
    }

    public function isValid()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->start_date && $this->start_date > now()) {
            return false;
        }

        if ($this->end_date && $this->end_date < now()) {
            return false;
        }

        if ($this->usage_limit_total && $this->current_usage_count >= $this->usage_limit_total) {
            return false;
        }

        return true;
    }

    public function canBeUsedBy($userId, $tenantId = null)
    {
        if (!$this->isValid()) {
            return false;
        }

        // Vérifier les limites d'usage par utilisateur
        if ($this->usage_limit_per_user) {
            $userUsageCount = $this->usages()
                ->where('user_id', $userId)
                ->count();
            
            if ($userUsageCount >= $this->usage_limit_per_user) {
                return false;
            }
        }

        // Vérifier les limites d'usage par tenant
        if ($tenantId && $this->usage_limit_per_tenant) {
            $tenantUsageCount = $this->usages()
                ->where('tenant_id', $tenantId)
                ->count();
            
            if ($tenantUsageCount >= $this->usage_limit_per_tenant) {
                return false;
            }
        }

        // Vérifier les conditions spécifiques
        if ($this->new_users_only) {
            $user = User::find($userId);
            if ($user && $user->created_at < now()->subDays(7)) {
                return false;
            }
        }

        return true;
    }

    public function calculateDiscount($amount, $planId = null)
    {
        if (!$this->isValid()) {
            return 0;
        }

        // Vérifier le montant minimum
        if ($this->min_purchase_amount && $amount < $this->min_purchase_amount) {
            return 0;
        }

        // Vérifier si le plan est applicable
        if ($planId && $this->applicable_plans && !in_array($planId, $this->applicable_plans)) {
            return 0;
        }

        $discount = 0;

        if ($this->discount_type === 'fixed') {
            $discount = $this->discount_value;
        } elseif ($this->discount_type === 'percentage') {
            $discount = ($amount * $this->discount_percentage) / 100;
            
            if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
                $discount = $this->max_discount_amount;
            }
        }

        return min($discount, $amount);
    }

    public function recordUsage($userId, $tenantId, $amount, $discount)
    {
        $this->increment('current_usage_count');
        $this->increment('total_revenue_generated', $amount);
        $this->increment('total_discount_given', $discount);

        PromotionUsage::create([
            'promotion_id' => $this->id,
            'user_id' => $userId,
            'tenant_id' => $tenantId,
            'amount' => $amount,
            'discount' => $discount,
            'used_at' => now()
        ]);

        // Calculer le taux de conversion
        $this->updateConversionRate();
    }

    private function updateConversionRate()
    {
        // Logique pour calculer le taux de conversion
        $totalViews = $this->analytics_data['total_views'] ?? 1;
        $totalUsages = $this->current_usage_count;
        
        $this->update([
            'conversion_rate' => ($totalUsages / $totalViews) * 100
        ]);
    }

    public static function getApplicablePromotions($userId, $tenantId, $amount, $planId = null)
    {
        return self::active()
            ->get()
            ->filter(function ($promotion) use ($userId, $tenantId, $amount, $planId) {
                return $promotion->canBeUsedBy($userId, $tenantId) 
                    && $promotion->calculateDiscount($amount, $planId) > 0;
            })
            ->sortByDesc('priority');
    }
}