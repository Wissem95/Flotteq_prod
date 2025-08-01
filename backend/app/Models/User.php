<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'username',
        'first_name',
        'last_name',
        'password',
        'google_id',
        'avatar',
        'role',
        'tenant_id',
        'is_active',
        'is_internal',
        'role_interne',
        'phone',
        'birthdate',
        'gender',
        'address',
        'postalCode',
        'city',
        'country',
        'company',
        'fleet_role',
        'license_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the password field for authentication.
     */
    public function getAuthPassword(): string
    {
        return $this->password;
    }

    /**
     * Automatically hash passwords when setting them (only if not already hashed).
     */
    public function setPasswordAttribute($value): void
    {
        // Only hash if it's not already a bcrypt hash
        if (!is_null($value) && !str_starts_with($value, '$2y$')) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    /**
     * Get the tenant that owns the user.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get all vehicles for this user.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Get all subscriptions for this user.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if user is internal (employé FlotteQ).
     */
    public function isInternal(): bool
    {
        return (bool) $this->is_internal;
    }

    /**
     * Check if user is super admin interne.
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_internal && in_array($this->role_interne, ['super_admin', 'admin']);
    }

    /**
     * Vérifier si le profil utilisateur est incomplet.
     *
     * @return bool
     */
    public function hasIncompleteProfile(): bool
    {
        // Champs obligatoires pour un profil complet
        $requiredFields = [
            'birthdate',
            'gender',
            'address',
            'city',
            'country',
        ];

        // Vérifier si au moins un champ obligatoire est vide
        foreach ($requiredFields as $field) {
            if (empty($this->$field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtenir la liste des champs de profil manquants.
     *
     * @return array
     */
    public function getMissingProfileFields(): array
    {
        $requiredFields = [
            'birthdate' => 'Date de naissance',
            'gender' => 'Genre',
            'address' => 'Adresse',
            'city' => 'Ville',
            'country' => 'Pays',
        ];

        $missingFields = [];
        foreach ($requiredFields as $field => $label) {
            if (empty($this->$field)) {
                $missingFields[$field] = $label;
            }
        }

        return $missingFields;
    }
}
