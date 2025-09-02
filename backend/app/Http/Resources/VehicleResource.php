<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'marque' => $this->marque,
            'modele' => $this->modele,
            'immatriculation' => $this->immatriculation,
            'vin' => $this->vin,
            'annee' => $this->annee,
            'couleur' => $this->couleur,
            'kilometrage' => $this->kilometrage,
            'carburant' => $this->carburant,
            'transmission' => $this->transmission,
            'puissance' => $this->puissance,
            'date_achat' => $this->date_achat?->format('Y-m-d'),
            'prix_achat' => $this->prix_achat ? (float) $this->prix_achat : null,
            'statut' => $this->statut,
            'notes' => $this->notes,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'last_ct_date' => $this->last_ct_date,
            'next_ct_date' => $this->next_ct_date,
            'insurance_start_date' => $this->insurance_start_date,
            'insurance_expiry_date' => $this->insurance_expiry_date,
            'insurance_company' => $this->insurance_company,
            'insurance_policy_number' => $this->insurance_policy_number,
            
            // Relationships
            'owner' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => "{$this->user->first_name} {$this->user->last_name}",
            ]),
            
            'factures_count' => $this->whenCounted('factures'),
            'maintenances_count' => $this->whenCounted('maintenances'),
            
            // Recent data when loaded
            'recent_factures' => $this->whenLoaded('factures'),
            'recent_maintenances' => $this->whenLoaded('maintenances'),
            'latest_controle_technique' => $this->whenLoaded('controleTechniques', 
                fn() => $this->controleTechniques->first()
            ),
        ];
    }
}
