<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'invoice_number' => 'INV-' . date('Y') . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'invoice_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'amount' => $this->faker->randomFloat(2, 50, 2000),
            'expense_type' => $this->faker->randomElement(['fuel', 'repair', 'maintenance', 'insurance', 'technical_inspection', 'other']),
            'description' => $this->faker->randomElement([
                'Oil change',
                'Tire replacement',
                'General service',
                'Brake repair',
                'Technical inspection',
                'Belt replacement',
                'Clutch repair',
                'AC maintenance'
            ]),
            'supplier' => $this->faker->company() . ' Garage',
        ];
    }

    /**
     * Create a facture for a specific vehicle
     */
    public function forVehicle(Vehicle $vehicle): static
    {
        return $this->state(fn (array $attributes) => [
            'vehicle_id' => $vehicle->id,
        ]);
    }

    /**
     * Create an expensive facture
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'montant' => $this->faker->randomFloat(2, 1500, 5000),
            'description' => $this->faker->randomElement([
                'Réparation moteur',
                'Changement boîte de vitesse',
                'Réparation carrosserie'
            ]),
        ]);
    }
}
