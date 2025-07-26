<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class VehicleData extends Data
{
    public function __construct(
        #[Required, Max(100)]
        public string $marque,
        
        #[Required, Max(100)]
        public string $modele,
        
        #[Required, Regex('/^[A-Z]{2}-[0-9]{3}-[A-Z]{2}$/')]
        public string $immatriculation,
        
        #[Max(17)]
        public Optional|string $vin,
        
        #[Required, Min(1900)]
        public int $annee,
        
        #[Max(50)]
        public Optional|string $couleur,
        
        #[Required, Min(0)]
        public int $kilometrage,
        
        #[Required, Rule('in:essence,diesel,electrique,hybride,gpl')]
        public string $carburant,
        
        #[Required, Rule('in:manuelle,automatique')]
        public string $transmission,
        
        #[Min(0)]
        public Optional|int $puissance,
        
        public Optional|string $purchase_date,
        
        #[Min(0)]
        public Optional|float $purchase_price,
        
        #[Rule('in:active,vendu,en_reparation,hors_service')]
        public Optional|string $status,
        
        public Optional|string $notes,
        
        public Optional|int $user_id,
        public Optional|int $tenant_id,
    ) {}
}
