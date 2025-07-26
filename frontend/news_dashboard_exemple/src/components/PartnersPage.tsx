
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Shield, Wrench, Users, TrendingUp } from 'lucide-react';
import InsuranceOfferCard from './InsuranceOfferCard';
import GarageMap from './GarageMap';
import { toast } from 'sonner';

const PartnersPage: React.FC = () => {
  const handleRequestQuote = (offerId: string) => {
    toast.success('Demande de devis envoyée ! Vous serez contacté sous 24h.');
  };

  const handleRequestCallback = (offerId: string) => {
    toast.success('Demande de rappel enregistrée ! Vous serez contacté rapidement.');
  };

  // Données des partenaires assurance
  const insurancePartners = [
    {
      id: 'partner-1',
      partnerName: 'AssurAuto Pro',
      coverageType: 'Tous risques',
      advantages: [
        'Garantie vol et incendie',
        'Assistance 24h/24',
        'Véhicule de remplacement',
        'Protection juridique incluse'
      ]
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
    },
    {
      id: 'partner-3',
      partnerName: 'Sécuri Fleet',
      coverageType: 'Flotte entreprise',
      advantages: [
        'Gestion centralisée',
        'Tarifs préférentiels',
        'Suivi en temps réel',
        'Support dédié'
      ]
    }
  ];

  return (
    <div className="flex-1 p-3 sm:p-6 bg-gray-50">
      <div className="max-w-7xl mx-auto">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between mb-6 sm:mb-8 gap-4">
          <h1 className="text-2xl sm:text-3xl font-bold text-gray-900">Partenaires</h1>
          <div className="text-sm text-gray-600">
            Accédez à notre réseau de partenaires de confiance
          </div>
        </div>

        {/* Statistiques des partenaires */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
          <Card>
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Assureurs partenaires</p>
                  <p className="text-xl sm:text-2xl font-bold text-blue-600">12</p>
                </div>
                <Shield className="w-6 sm:w-8 h-6 sm:h-8 text-blue-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Garages partenaires</p>
                  <p className="text-xl sm:text-2xl font-bold text-green-600">47</p>
                </div>
                <Wrench className="w-6 sm:w-8 h-6 sm:h-8 text-green-600" />
              </div>
            </CardContent>
          </Card>
          <Card className="sm:col-span-2 lg:col-span-1">
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Interventions réalisées</p>
                  <p className="text-xl sm:text-2xl font-bold text-purple-600">236</p>
                </div>
                <TrendingUp className="w-6 sm:w-8 h-6 sm:h-8 text-purple-600" />
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Onglets des partenaires */}
        <Tabs defaultValue="insurance" className="w-full">
          <TabsList className="grid w-full grid-cols-2 mb-4 sm:mb-6">
            <TabsTrigger value="insurance" className="flex items-center gap-2 text-sm">
              <Shield className="w-4 h-4" />
              <span className="hidden sm:inline">Assureurs</span>
              <span className="sm:hidden">Assur.</span>
            </TabsTrigger>
            <TabsTrigger value="garages" className="flex items-center gap-2 text-sm">
              <Wrench className="w-4 h-4" />
              <span className="hidden sm:inline">Garages</span>
              <span className="sm:hidden">Garages</span>
            </TabsTrigger>
          </TabsList>

          <TabsContent value="insurance">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2 text-lg sm:text-xl">
                  <Shield className="w-5 h-5 text-blue-600" />
                  Nos partenaires assurance
                </CardTitle>
                <p className="text-sm sm:text-base text-gray-600">
                  Découvrez nos partenaires assurance et trouvez la couverture adaptée à vos véhicules.
                </p>
              </CardHeader>
              <CardContent>
                <div className="grid gap-4 sm:gap-6 grid-cols-1 lg:grid-cols-2 xl:grid-cols-3">
                  {insurancePartners.map((partner) => (
                    <InsuranceOfferCard
                      key={partner.id}
                      offer={partner}
                      onRequestQuote={handleRequestQuote}
                      onRequestCallback={handleRequestCallback}
                    />
                  ))}
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="garages">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2 text-lg sm:text-xl">
                  <Wrench className="w-5 h-5 text-green-600" />
                  Réseau de garages partenaires
                </CardTitle>
                <p className="text-sm sm:text-base text-gray-600">
                  Trouvez un garage partenaire près de chez vous pour l'entretien et la réparation de vos véhicules.
                </p>
              </CardHeader>
              <CardContent>
                <GarageMap location="Paris, France" />
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
};

export default PartnersPage;
