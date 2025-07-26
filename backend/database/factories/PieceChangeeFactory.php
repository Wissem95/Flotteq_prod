<?php

namespace Database\Factories;

use App\Models\PieceChangee;
use App\Models\Repair;
use App\Models\Piece;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PieceChangee>
 */
class PieceChangeeFactory extends Factory
{
    protected $model = PieceChangee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $piece = Piece::factory()->create();
        $quantite = $this->faker->numberBetween(1, 4);
        
        return [
            'repair_id' => Repair::factory(),
            'piece_id' => $piece->id,
            'quantite' => $quantite,
            'prix_unitaire' => $piece->prix_unitaire,
            'notes' => $this->faker->optional(0.3)->randomElement([
                'Pièce d\'origine',
                'Pièce compatible',
                'Garantie 2 ans',
                'Urgence - pièce express'
            ]),
        ];
    }

    /**
     * Create piece changee for a specific repair
     */
    public function forRepair(Repair $repair): static
    {
        return $this->state(fn (array $attributes) => [
            'repair_id' => $repair->id,
        ]);
    }

    /**
     * Create piece changee with a specific piece
     */
    public function withPiece(Piece $piece): static
    {
        return $this->state(fn (array $attributes) => [
            'piece_id' => $piece->id,
            'prix_unitaire' => $piece->prix_unitaire,
        ]);
    }

    /**
     * Create multiple quantities
     */
    public function multiple(int $quantity = null): static
    {
        $qty = $quantity ?? $this->faker->numberBetween(2, 6);
        
        return $this->state(fn (array $attributes) => [
            'quantite' => $qty,
        ]);
    }

    /**
     * Create with custom price (different from piece price)
     */
    public function withCustomPrice(float $price): static
    {
        return $this->state(fn (array $attributes) => [
            'prix_unitaire' => $price,
            'notes' => 'Prix négocié',
        ]);
    }

    /**
     * Calculate total cost
     */
    public function getTotalCost(): float
    {
        return $this->quantite * $this->prix_unitaire;
    }
}
