
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Wrench, Users, TrendingUp } from 'lucide-react';
import GarageMap from './GarageMap';

const TrouverGaragePage: React.FC = () => {
  return (
    <div className="flex-1 p-3 sm:p-6 bg-gray-50">
      <div className="max-w-7xl mx-auto">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between mb-6 sm:mb-8 gap-4">
          <h1 className="text-2xl sm:text-3xl font-bold text-gray-900">Trouver un garage</h1>
          <div className="text-sm text-gray-600">
            Accédez à notre réseau de garages partenaires
          </div>
        </div>

        {/* Statistiques des garages */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
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
          <Card>
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Interventions ce mois</p>
                  <p className="text-xl sm:text-2xl font-bold text-blue-600">156</p>
                </div>
                <Users className="w-6 sm:w-8 h-6 sm:h-8 text-blue-600" />
              </div>
            </CardContent>
          </Card>
          <Card className="sm:col-span-2 lg:col-span-1">
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Satisfaction client</p>
                  <p className="text-xl sm:text-2xl font-bold text-purple-600">4.8/5</p>
                </div>
                <TrendingUp className="w-6 sm:w-8 h-6 sm:h-8 text-purple-600" />
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Carte des garages */}
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
      </div>
    </div>
  );
};

export default TrouverGaragePage;
