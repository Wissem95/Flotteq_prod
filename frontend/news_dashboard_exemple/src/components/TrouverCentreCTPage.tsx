
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ClipboardCheck, Users, TrendingUp, MapPin, Search } from 'lucide-react';
import OpenStreetMapComponent from './OpenStreetMapComponent';
import { useGeolocation } from '@/hooks/useGeolocation';
import { toast } from 'sonner';

interface CTCenter {
  id: string;
  name: string;
  address: string;
  distance: number;
  coordinates: { lat: number; lng: number };
  rating: number;
  priceForService: number;
  phone: string;
}

const TrouverCentreCTPage: React.FC = () => {
  const [searchLocation, setSearchLocation] = useState('Paris, France');
  const [mapCenter, setMapCenter] = useState({ lat: 48.8566, lng: 2.3522 });
  const { coordinates, getCurrentLocation, isLoading } = useGeolocation();

  // Données exemple des centres de contrôle technique
  const ctCenters: CTCenter[] = [
    {
      id: 'ct-1',
      name: 'Centre CT Paris République',
      address: '12 Avenue de la République, 75011 Paris',
      distance: 2.1,
      coordinates: { lat: 48.8634, lng: 2.3708 },
      rating: 4.7,
      priceForService: 78,
      phone: '01 23 45 67 89'
    },
    {
      id: 'ct-2',
      name: 'AutoContrôle Bastille',
      address: '5 Rue de la Bastille, 75012 Paris',
      distance: 3.4,
      coordinates: { lat: 48.8531, lng: 2.3692 },
      rating: 4.5,
      priceForService: 85,
      phone: '01 34 56 78 90'
    },
    {
      id: 'ct-3',
      name: 'CT Express Montparnasse',
      address: '18 Boulevard Montparnasse, 75014 Paris',
      distance: 4.2,
      coordinates: { lat: 48.8422, lng: 2.3211 },
      rating: 4.6,
      priceForService: 72,
      phone: '01 45 67 89 01'
    },
    {
      id: 'ct-4',
      name: 'Centre Technique Belleville',
      address: '25 Rue de Belleville, 75020 Paris',
      distance: 5.1,
      coordinates: { lat: 48.8722, lng: 2.3806 },
      rating: 4.4,
      priceForService: 80,
      phone: '01 56 78 90 12'
    },
    {
      id: 'ct-5',
      name: 'AutoSécurité Châtelet',
      address: '8 Rue de Rivoli, 75001 Paris',
      distance: 1.8,
      coordinates: { lat: 48.8584, lng: 2.3470 },
      rating: 4.8,
      priceForService: 88,
      phone: '01 67 89 01 23'
    }
  ];

  const handleSearch = () => {
    toast.success(`Recherche mise à jour pour ${searchLocation}`);
  };

  const handleUseCurrentLocation = () => {
    if (coordinates) {
      setMapCenter(coordinates);
      setSearchLocation('Ma position actuelle');
      toast.success('Position actuelle utilisée');
    } else {
      getCurrentLocation();
    }
  };

  const handleReserveCT = (center: CTCenter) => {
    toast.success(`Demande de réservation envoyée à ${center.name} !`);
  };

  const handleMapMove = (center: { lat: number; lng: number }, zoom: number) => {
    setMapCenter(center);
  };

  return (
    <div className="flex-1 p-3 sm:p-6 bg-gray-50">
      <div className="max-w-7xl mx-auto">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between mb-6 sm:mb-8 gap-4">
          <h1 className="text-2xl sm:text-3xl font-bold text-gray-900">Trouver un Centre de Contrôle Technique</h1>
          <div className="text-sm text-gray-600">
            Accédez à notre réseau de centres de contrôle technique partenaires
          </div>
        </div>

        {/* Statistiques des centres CT */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
          <Card>
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Centres partenaires</p>
                  <p className="text-xl sm:text-2xl font-bold text-blue-600">32</p>
                </div>
                <ClipboardCheck className="w-6 sm:w-8 h-6 sm:h-8 text-blue-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Contrôles ce mois</p>
                  <p className="text-xl sm:text-2xl font-bold text-green-600">89</p>
                </div>
                <Users className="w-6 sm:w-8 h-6 sm:h-8 text-green-600" />
              </div>
            </CardContent>
          </Card>
          <Card className="sm:col-span-2 lg:col-span-1">
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Taux de réussite</p>
                  <p className="text-xl sm:text-2xl font-bold text-purple-600">92%</p>
                </div>
                <TrendingUp className="w-6 sm:w-8 h-6 sm:h-8 text-purple-600" />
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Barre de recherche */}
        <Card className="mb-6">
          <CardContent className="p-4">
            <div className="flex flex-col sm:flex-row gap-4">
              <div className="flex-1">
                <Input
                  type="text"
                  placeholder="Rechercher par ville, code postal..."
                  value={searchLocation}
                  onChange={(e) => setSearchLocation(e.target.value)}
                  className="w-full"
                />
              </div>
              <div className="flex gap-2">
                <Button onClick={handleSearch} className="flex items-center gap-2">
                  <Search className="w-4 h-4" />
                  Rechercher
                </Button>
                <Button 
                  onClick={handleUseCurrentLocation}
                  variant="outline"
                  className="flex items-center gap-2"
                  disabled={isLoading}
                >
                  <MapPin className="w-4 h-4" />
                  {isLoading ? 'Localisation...' : 'Ma position'}
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Carte interactive OpenStreetMap */}
        <Card className="mb-6">
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-lg sm:text-xl">
              <ClipboardCheck className="w-5 h-5 text-blue-600" />
              Centres de contrôle technique à proximité
            </CardTitle>
            <p className="text-sm sm:text-base text-gray-600">
              Trouvez le centre le plus proche et comparez les tarifs
            </p>
          </CardHeader>
          <CardContent>
            <OpenStreetMapComponent
              garages={ctCenters}
              center={mapCenter}
              selectedService="Contrôle technique"
              onReserve={handleReserveCT}
              onMapMove={handleMapMove}
            />
          </CardContent>
        </Card>

        {/* Liste des centres */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-lg sm:text-xl">
              <ClipboardCheck className="w-5 h-5 text-blue-600" />
              Liste des centres partenaires
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4">
              {ctCenters.map((center) => (
                <div key={center.id} className="border rounded-lg p-4 hover:shadow-md transition-shadow">
                  <div className="flex justify-between items-start mb-3">
                    <div className="flex-1">
                      <h3 className="text-lg font-semibold text-gray-900">{center.name}</h3>
                      <p className="text-sm text-gray-600 flex items-center gap-1 mt-1">
                        <MapPin className="w-4 h-4" />
                        {center.address} • {center.distance} km
                      </p>
                    </div>
                    <div className="text-right">
                      <div className="text-lg font-bold text-green-600">{center.priceForService}€</div>
                      <div className="text-sm text-gray-500">Note: {center.rating}/5</div>
                    </div>
                  </div>
                  <div className="flex gap-2">
                    <Button 
                      onClick={() => handleReserveCT(center)}
                      className="flex-1"
                      size="sm"
                    >
                      Réserver
                    </Button>
                    <Button 
                      variant="outline"
                      size="sm"
                      onClick={() => window.open(`tel:${center.phone}`, '_self')}
                    >
                      Téléphoner
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default TrouverCentreCTPage;
