<?php

namespace Database\Factories;

use App\Models\Repair;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Repair>
 */
class RepairFactory extends Factory
{
    protected $model = Repair::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'repair_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'description' => $this->faker->randomElement([
                'Brake system repair',
                'Clutch replacement',
                'Power steering repair',
                'Alternator replacement',
                'Cooling system repair',
                'Shock absorber replacement',
                'Gearbox repair',
                'Fuel pump replacement'
            ]),
            'total_cost' => $this->faker->randomFloat(2, 200, 2000),
            'workshop' => $this->faker->company() . ' Mechanics',
            'mileage' => $this->faker->numberBetween(20000, 200000),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed', 'warranty']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Create repair for a specific vehicle
     */
    public function forVehicle(Vehicle $vehicle): static
    {
        return $this->state(fn (array $attributes) => [
            'vehicle_id' => $vehicle->id,
        ]);
    }

    /**
     * Create completed repair
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Create expensive repair
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_cost' => $this->faker->randomFloat(2, 2500, 8000),
            'description' => $this->faker->randomElement([
                'Engine repair - head gasket replacement',
                'Complete gearbox replacement',
                'Body repair after accident'
            ]),
        ]);
    }
}
