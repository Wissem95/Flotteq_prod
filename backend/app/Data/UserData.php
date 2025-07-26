<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UserData extends Data
{
    public function __construct(
        #[Required, Email, Max(255)]
        public string $email,
        
        #[Required, Max(100)]
        public string $username,
        
        #[Required, Max(100)]
        public string $first_name,
        
        #[Required, Max(100)]
        public string $last_name,
        
        #[Required, Min(8), Max(255), Confirmed]
        public Optional|string $password,
        
        #[Max(50)]
        public Optional|string $role,
        
        public Optional|int $tenant_id,
        public Optional|bool $is_active,
    ) {}
}
