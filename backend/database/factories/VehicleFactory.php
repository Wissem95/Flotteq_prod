<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $marques = ['Peugeot', 'Renault', 'Citroën', 'Ford', 'Volkswagen', 'Toyota', 'Mercedes', 'BMW'];
        $modeles = ['208', '308', 'Clio', 'Megane', 'C3', 'Focus', 'Golf', 'Corolla', 'A-Class', 'Serie 3'];
        $carburants = ['essence', 'diesel', 'electrique', 'hybride'];
        $transmissions = ['manuelle', 'automatique'];
        $couleurs = ['Blanc', 'Noir', 'Gris', 'Rouge', 'Bleu', 'Argent'];

        return [
            'user_id' => User::factory(),
            'tenant_id' => Tenant::factory(),
            'marque' => $this->faker->randomElement($marques),
            'modele' => $this->faker->randomElement($modeles),
            'immatriculation' => strtoupper($this->faker->regexify('[A-Z]{2}-[0-9]{3}-[A-Z]{2}')),
            'vin' => strtoupper($this->faker->regexify('[A-HJ-NPR-Z0-9]{17}')),
            'annee' => $this->faker->numberBetween(2010, 2024),
            'couleur' => $this->faker->randomElement($couleurs),
            'kilometrage' => $this->faker->numberBetween(1000, 200000),
            'carburant' => $this->faker->randomElement($carburants),
            'transmission' => $this->faker->randomElement($transmissions),
            'puissance' => $this->faker->numberBetween(70, 300),
            'purchase_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'purchase_price' => $this->faker->randomFloat(2, 5000, 50000),
            'status' => 'active',
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Create a vehicle with specific status
     */
    public function withStatus(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    /**
     * Create an electric vehicle
     */
    public function electric(): static
    {
        return $this->state(fn (array $attributes) => [
            'carburant' => 'electrique',
            'marque' => $this->faker->randomElement(['Tesla', 'Renault', 'Nissan', 'BMW']),
            'modele' => $this->faker->randomElement(['Model 3', 'Zoe', 'Leaf', 'i3']),
        ]);
    }

    /**
     * Create a vehicle for a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
        ]);
    }
} 