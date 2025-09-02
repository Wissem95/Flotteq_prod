<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'provider',
        'gateway',
        'gateway_config',
        'api_keys',
        'webhook_url',
        'webhook_secret',
        'supported_currencies',
        'supported_countries',
        'min_amount',
        'max_amount',
        'transaction_fee_fixed',
        'transaction_fee_percentage',
        'settlement_delay_days',
        'instant_payment',
        'recurring_payment',
        'refund_supported',
        'partial_refund_supported',
        'auto_capture',
        'requires_3ds',
        'requires_verification',
        'verification_fields',
        'display_name',
        'description',
        'icon_url',
        'position',
        'is_default',
        'is_active',
        'is_test_mode',
        'test_credentials',
        'sandbox_url',
        'production_url',
        'success_url',
        'cancel_url',
        'notification_url',
        'available_for_tenants',
        'available_for_plans',
        'excluded_countries',
        'risk_level',
        'fraud_detection',
        'compliance_requirements',
        'documentation_url',
        'support_email',
        'integration_status',
        'last_tested_at',
        'metadata'
    ];

    protected $casts = [
        'gateway_config' => 'array',
        'api_keys' => 'encrypted:array',
        'supported_currencies' => 'array',
        'supported_countries' => 'array',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'transaction_fee_fixed' => 'decimal:2',
        'transaction_fee_percentage' => 'decimal:4',
        'instant_payment' => 'boolean',
        'recurring_payment' => 'boolean',
        'refund_supported' => 'boolean',
        'partial_refund_supported' => 'boolean',
        'auto_capture' => 'boolean',
        'requires_3ds' => 'boolean',
        'requires_verification' => 'boolean',
        'verification_fields' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'is_test_mode' => 'boolean',
        'test_credentials' => 'encrypted:array',
        'available_for_tenants' => 'array',
        'available_for_plans' => 'array',
        'excluded_countries' => 'array',
        'fraud_detection' => 'boolean',
        'compliance_requirements' => 'array',
        'last_tested_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'payment_method_id');
    }

    public function revenues()
    {
        return $this->hasMany(RevenueRecord::class, 'payment_method', 'code');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForProduction($query)
    {
        return $query->where('is_test_mode', false);
    }

    public function scopeSupportsRecurring($query)
    {
        return $query->where('recurring_payment', true);
    }

    public function scopeSupportsRefund($query)
    {
        return $query->where('refund_supported', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    public function isAvailableForCountry($countryCode)
    {
        if ($this->excluded_countries && in_array($countryCode, $this->excluded_countries)) {
            return false;
        }

        if ($this->supported_countries && !in_array($countryCode, $this->supported_countries)) {
            return false;
        }

        return true;
    }

    public function isAvailableForCurrency($currency)
    {
        if (!$this->supported_currencies) {
            return true;
        }

        return in_array($currency, $this->supported_currencies);
    }

    public function isAvailableForAmount($amount, $currency = 'EUR')
    {
        if ($this->min_amount && $amount < $this->min_amount) {
            return false;
        }

        if ($this->max_amount && $amount > $this->max_amount) {
            return false;
        }

        return $this->isAvailableForCurrency($currency);
    }

    public function calculateFee($amount)
    {
        $fixedFee = $this->transaction_fee_fixed ?? 0;
        $percentageFee = ($amount * ($this->transaction_fee_percentage ?? 0)) / 100;
        
        return $fixedFee + $percentageFee;
    }

    public function getNetAmount($amount)
    {
        return $amount - $this->calculateFee($amount);
    }

    public function testConnection()
    {
        // Logique pour tester la connexion avec le gateway
        try {
            // Implémenter le test selon le provider
            $this->update(['last_tested_at' => now()]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getCredentials()
    {
        if ($this->is_test_mode) {
            return $this->test_credentials;
        }

        return $this->api_keys;
    }

    public function getApiUrl()
    {
        if ($this->is_test_mode) {
            return $this->sandbox_url;
        }

        return $this->production_url;
    }

    public static function getDefaultMethod()
    {
        return self::where('is_default', true)->active()->first();
    }

    public static function getAvailableMethods($tenantId = null, $planId = null)
    {
        $query = self::active();

        if ($tenantId) {
            $query->where(function ($q) use ($tenantId) {
                $q->whereNull('available_for_tenants')
                  ->orWhereJsonContains('available_for_tenants', $tenantId);
            });
        }

        if ($planId) {
            $query->where(function ($q) use ($planId) {
                $q->whereNull('available_for_plans')
                  ->orWhereJsonContains('available_for_plans', $planId);
            });
        }

        return $query->orderBy('position')->get();
    }
}