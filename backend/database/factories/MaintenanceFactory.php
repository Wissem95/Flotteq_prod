<?php

namespace Database\Factories;

use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Maintenance>
 */
class MaintenanceFactory extends Factory
{
    protected $model = Maintenance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $maintenanceDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $nextMaintenance = (clone $maintenanceDate)->modify('+6 months');

        return [
            'vehicle_id' => Vehicle::factory(),
            'maintenance_type' => $this->faker->randomElement([
                'oil_change',
                'revision',
                'tires',
                'brakes',
                'belt',
                'filters'
            ]),
            'description' => $this->faker->randomElement([
                'Oil change + oil filter',
                '20,000 km service',
                'Front tire replacement',
                'Brake inspection and cleaning',
                'Timing belt replacement',
                'Air and cabin filter change'
            ]),
            'maintenance_date' => $maintenanceDate,
            'mileage' => $this->faker->numberBetween(10000, 150000),
            'cost' => $this->faker->randomFloat(2, 80, 800),
            'workshop' => $this->faker->company() . ' Auto',
            'next_maintenance' => $nextMaintenance,
            'status' => $this->faker->randomElement(['scheduled', 'in_progress', 'completed', 'cancelled']),
        ];
    }

    /**
     * Create maintenance for a specific vehicle
     */
    public function forVehicle(Vehicle $vehicle): static
    {
        return $this->state(fn (array $attributes) => [
            'vehicle_id' => $vehicle->id,
        ]);
    }

    /**
     * Create overdue maintenance
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'terminee',
            'prochaine_maintenance' => $this->faker->dateTimeBetween('-3 months', '-1 month'),
        ]);
    }

    /**
     * Create completed maintenance
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'terminee',
        ]);
    }
}
