
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@flotteq/shared';
import { Button } from '@flotteq/shared';
import { Target, ChevronDown, ChevronUp } from 'lucide-react';
import InsuranceOfferCard from './InsuranceOfferCard';
import { toast } from 'sonner';

interface InsuranceProposalProps {
  vehicleId?: string;
  vehicleBrand?: string;
  vehicleModel?: string;
  context: 'add-vehicle' | 'alert' | 'transaction';
}

const InsuranceProposal: React.FC<InsuranceProposalProps> = ({
  vehicleId,
  vehicleBrand,
  vehicleModel,
  context
}) => {
  const [isExpanded, setIsExpanded] = useState(context === 'alert');

  // Données exemple des offres partenaires
  const insuranceOffers = [
    {
      id: 'partner-1',
      partnerName: 'AssurAuto Pro',
      coverageType: 'Tous risques',
      advantages: [
        'Garantie vol et incendie',
        'Assistance 24h/24',
        'Véhicule de remplacement',
        'Protection juridique incluse'
      ],
      expiryDate: context === 'alert' ? '2024-07-15' : undefined
    },
    {
      id: 'partner-2',
      partnerName: 'Protec Véhicules',
      coverageType: 'Tiers étendu',
      advantages: [
        'Bris de glace inclus',
        'Dépannage gratuit',
        'Remboursement à neuf',
        'Franchise réduite'
      ]
    }
  ];

  const handleRequestQuote = (offerId: string) => {
    toast.success('Demande de devis envoyée ! Vous serez contacté sous 24h.');
  };

  const handleRequestCallback = (offerId: string) => {
    toast.success('Demande de rappel enregistrée ! Vous serez contacté rapidement.');
  };

  if (context === 'add-vehicle') {
    return (
      <Card className="border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50">
        <CardHeader>
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <Target className="w-6 h-6 text-blue-600" />
              <CardTitle className="text-blue-900">
                🎯 Proposer une assurance pour ce véhicule ?
              </CardTitle>
            </div>
            <Button
              variant="ghost"
              size="sm"
              onClick={() => setIsExpanded(!isExpanded)}
            >
              {isExpanded ? <ChevronUp className="w-4 h-4" /> : <ChevronDown className="w-4 h-4" />}
            </Button>
          </div>
          {vehicleBrand && vehicleModel && (
            <p className="text-sm text-gray-600">
              Offres disponibles pour {vehicleBrand} {vehicleModel}
            </p>
          )}
        </CardHeader>
        {isExpanded && (
          <CardContent>
            <div className="grid gap-4 md:grid-cols-2">
              {insuranceOffers.map((offer) => (
                <InsuranceOfferCard
                  key={offer.id}
                  offer={offer}
                  vehicleId={vehicleId}
                  onRequestQuote={handleRequestQuote}
                  onRequestCallback={handleRequestCallback}
                />
              ))}
            </div>
          </CardContent>
        )}
      </Card>
    );
  }

  if (context === 'alert') {
    return (
      <Card className="border-orange-200 bg-orange-50">
        <CardHeader>
          <CardTitle className="text-orange-900 flex items-center gap-2">
            📋 Assurances disponibles
          </CardTitle>
          <p className="text-sm text-orange-700">
            Votre assurance arrive à échéance. Comparez nos offres partenaires.
          </p>
        </CardHeader>
        <CardContent>
          <div className="grid gap-4 md:grid-cols-2">
            {insuranceOffers.map((offer) => (
              <InsuranceOfferCard
                key={offer.id}
                offer={offer}
                vehicleId={vehicleId}
                onRequestQuote={handleRequestQuote}
                onRequestCallback={handleRequestCallback}
              />
            ))}
          </div>
          <div className="mt-4 pt-4 border-t">
            <Button variant="outline" className="w-full">
              Comparer toutes les offres
            </Button>
          </div>
        </CardContent>
      </Card>
    );
  }

  return null;
};

export default InsuranceProposal;
