<?php

namespace Database\Factories;

use App\Models\Piece;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Piece>
 */
class PieceFactory extends Factory
{
    protected $model = Piece::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pieces = [
            ['nom' => 'Filtre à huile', 'prix' => [15, 35]],
            ['nom' => 'Plaquettes de frein', 'prix' => [45, 120]],
            ['nom' => 'Disque de frein', 'prix' => [80, 200]],
            ['nom' => 'Amortisseur', 'prix' => [120, 300]],
            ['nom' => 'Courroie de distribution', 'prix' => [35, 80]],
            ['nom' => 'Alternateur', 'prix' => [200, 500]],
            ['nom' => 'Démarreur', 'prix' => [150, 400]],
            ['nom' => 'Batterie', 'prix' => [80, 180]],
            ['nom' => 'Pneu', 'prix' => [60, 250]],
            ['nom' => 'Filtre à air', 'prix' => [20, 45]],
            ['nom' => 'Bougies d\'allumage', 'prix' => [8, 25]],
            ['nom' => 'Radiateur', 'prix' => [150, 400]],
            ['nom' => 'Pot d\'échappement', 'prix' => [100, 300]],
            ['nom' => 'Embrayage', 'prix' => [300, 800]],
            ['nom' => 'Pompe à carburant', 'prix' => [120, 350]]
        ];

        $piece = $this->faker->randomElement($pieces);

        return [
            'nom' => $piece['nom'],
            'reference' => strtoupper($this->faker->bothify('??####')),
            'prix_unitaire' => $this->faker->randomFloat(2, $piece['prix'][0], $piece['prix'][1]),
            'categorie' => $this->faker->randomElement([
                'Moteur',
                'Freinage', 
                'Suspension',
                'Électricité',
                'Carrosserie',
                'Transmission',
                'Échappement',
                'Pneumatiques'
            ]),
        ];
    }

    /**
     * Create a specific piece by name
     */
    public function named(string $nom): static
    {
        return $this->state(fn (array $attributes) => [
            'nom' => $nom,
        ]);
    }

    /**
     * Create an expensive piece
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'nom' => $this->faker->randomElement([
                'Moteur complet',
                'Boîte de vitesse',
                'Turbocompresseur',
                'Système GPS'
            ]),
            'prix_unitaire' => $this->faker->randomFloat(2, 800, 5000),
        ]);
    }

    /**
     * Create a cheap piece
     */
    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'nom' => $this->faker->randomElement([
                'Joint',
                'Vis',
                'Rondelle',
                'Clip'
            ]),
            'prix_unitaire' => $this->faker->randomFloat(2, 1, 15),
        ]);
    }
}
