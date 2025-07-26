<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'domain' => $this->faker->unique()->domainWord() . '.flotteq.local',
            'database' => 'tenant_' . $this->faker->unique()->slug(),
        ];
    }

    /**
     * Create a tenant with a specific domain
     */
    public function withDomain(string $domain): static
    {
        return $this->state(fn (array $attributes) => [
            'domain' => $domain,
            'database' => 'tenant_' . str_replace('.', '_', $domain),
        ]);
    }

    /**
     * Create a demo tenant
     */
    public function demo(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Demo Corporation',
            'domain' => 'demo.flotteq.local',
            'database' => 'tenant_demo',
        ]);
    }
}
