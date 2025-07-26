<?php

namespace Database\Factories;

use App\Models\TechnicalInspection;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TechnicalInspection>
 */
class TechnicalInspectionFactory extends Factory
{
    protected $model = TechnicalInspection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $inspectionDate = $this->faker->dateTimeBetween('-2 years', 'now');
        $expirationDate = (clone $inspectionDate)->modify('+2 years');

        return [
            'vehicle_id' => Vehicle::factory(),
            'inspection_date' => $inspectionDate,
            'expiration_date' => $expirationDate,
            'result' => $this->faker->randomElement(['favorable', 'favorable_with_minor_defects', 'unfavorable']),
            'report_number' => 'TI-' . $this->faker->unique()->numberBetween(100000, 999999),
            'organization' => $this->faker->company() . ' Technical Inspection',
            'observations' => $this->faker->optional()->sentence(),
            'cost' => $this->faker->randomFloat(2, 70, 120),
        ];
    }

    /**
     * Create control for a specific vehicle
     */
    public function forVehicle(Vehicle $vehicle): static
    {
        return $this->state(fn (array $attributes) => [
            'vehicle_id' => $vehicle->id,
        ]);
    }

    /**
     * Create favorable control
     */
    public function favorable(): static
    {
        return $this->state(fn (array $attributes) => [
            'resultat' => 'favorable',
            'defauts_constates' => null,
            'observations' => 'Véhicule en bon état général',
        ]);
    }

    /**
     * Create control with minor defects
     */
    public function withMinorDefects(): static
    {
        return $this->state(fn (array $attributes) => [
            'resultat' => 'defaillance_mineure',
            'defauts_constates' => $this->faker->randomElement([
                'Éclairage défaillant',
                'Usure légère des pneus',
                'Rétroviseur fissuré'
            ]),
        ]);
    }

    /**
     * Create control with major defects
     */
    public function withMajorDefects(): static
    {
        return $this->state(fn (array $attributes) => [
            'resultat' => 'defaillance_majeure',
            'defauts_constates' => $this->faker->randomElement([
                'Freinage insuffisant - danger immédiat',
                'Direction défaillante',
                'Pollution excessive - non conforme'
            ]),
        ]);
    }

    /**
     * Create expired control
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'date_expiration' => $this->faker->dateTimeBetween('-6 months', '-1 month'),
        ]);
    }
}
