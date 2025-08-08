
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@flotteq/shared';
import { Button } from '@flotteq/shared';
import { Badge } from '@flotteq/shared';
import { Input } from '@flotteq/shared';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@flotteq/shared';
import { MapPin, Wrench, Clock, Phone, Star, Filter } from 'lucide-react';
import { toast } from 'sonner';

interface Garage {
  id: string;
  name: string;
  address: string;
  distance: number;
  specialties: string[];
  rating: number;
  availability: 'Disponible' | 'Complet' | 'Limité';
  phone: string;
  services: {
    name: string;
    price?: number;
  }[];
}

interface GarageMapProps {
  vehicleId?: string;
  alertType?: string;
  location?: string;
}

const GarageMap: React.FC<GarageMapProps> = ({ vehicleId, alertType, location }) => {
  const [selectedGarage, setSelectedGarage] = useState<Garage | null>(null);
  const [distanceFilter, setDistanceFilter] = useState<string>('all');
  const [specialtyFilter, setSpecialtyFilter] = useState<string>('all');

  // Données exemple des garages partenaires
  const partnerGarages: Garage[] = [
    {
      id: 'garage-1',
      name: 'Garage Central Auto',
      address: '15 Avenue de la République, 75011 Paris',
      distance: 2.3,
      specialties: ['Freinage', 'Vidange', 'Contrôle technique'],
      rating: 4.8,
      availability: 'Disponible',
      phone: '01 23 45 67 89',
      services: [
        { name: 'Changement plaquettes', price: 120 },
        { name: 'Vidange complète', price: 85 },
        { name: 'Contrôle technique', price: 78 }
      ]
    },
    {
      id: 'garage-2',
      name: 'AutoService Pro',
      address: '8 Rue des Artisans, 75012 Paris',
      distance: 3.1,
      specialties: ['Électronique', 'Moteur', 'Climatisation'],
      rating: 4.6,
      availability: 'Limité',
      phone: '01 34 56 78 90',
      services: [
        { name: 'Diagnostic électronique', price: 95 },
        { name: 'Réparation moteur', price: 350 },
        { name: 'Recharge climatisation', price: 120 }
      ]
    },
    {
      id: 'garage-3',
      name: 'Mécanique Express',
      address: '22 Boulevard Saint-Michel, 75005 Paris',
      distance: 4.7,
      specialties: ['Freinage', 'Échappement', 'Pneumatiques'],
      rating: 4.5,
      availability: 'Disponible',
      phone: '01 45 67 89 01',
      services: [
        { name: 'Changement d\'échappement', price: 280 },
        { name: 'Montage pneus', price: 45 },
        { name: 'Équilibrage', price: 35 }
      ]
    }
  ];

  const filteredGarages = partnerGarages.filter(garage => {
    const distanceMatch = distanceFilter === 'all' || 
      (distanceFilter === '5' && garage.distance <= 5) ||
      (distanceFilter === '10' && garage.distance <= 10);
    
    const specialtyMatch = specialtyFilter === 'all' || 
      garage.specialties.some(specialty => 
        specialty.toLowerCase().includes(specialtyFilter.toLowerCase())
      );

    return distanceMatch && specialtyMatch;
  });

  const handleContactGarage = (garage: Garage) => {
    toast.success(`Contact envoyé à ${garage.name} ! Vous serez rappelé rapidement.`);
  };

  const handleBookService = (garage: Garage) => {
    toast.success(`Demande de réservation envoyée à ${garage.name} !`);
  };

  const getAvailabilityColor = (availability: string) => {
    switch (availability) {
      case 'Disponible': return 'bg-green-100 text-green-800';
      case 'Limité': return 'bg-yellow-100 text-yellow-800';
      case 'Complet': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <div className="space-y-6">
      {/* Carte interactive (placeholder visuel) */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <MapPin className="w-5 h-5 text-blue-600" />
            Garages partenaires près de {location || 'votre position'}
          </CardTitle>
          {alertType && (
            <p className="text-sm text-gray-600">
              Spécialisés pour : <Badge variant="outline">{alertType}</Badge>
            </p>
          )}
        </CardHeader>
        <CardContent>
          {/* Simulation d'une carte interactive */}
          <div className="h-64 bg-gradient-to-br from-blue-100 to-green-100 rounded-lg relative overflow-hidden">
            <div className="absolute inset-0 flex items-center justify-center">
              <div className="text-center">
                <MapPin className="w-12 h-12 text-blue-600 mx-auto mb-2" />
                <p className="text-gray-600 font-medium">Carte interactive des garages</p>
                <p className="text-sm text-gray-500">
                  {filteredGarages.length} garage(s) partenaire(s) trouvé(s)
                </p>
              </div>
            </div>
            {/* Points simulés sur la carte */}
            <div className="absolute top-1/4 left-1/3 w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
            <div className="absolute top-2/3 right-1/3 w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
            <div className="absolute bottom-1/4 left-1/2 w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
          </div>
        </CardContent>
      </Card>

      {/* Filtres */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Filter className="w-5 h-5" />
            Filtres
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label className="text-sm font-medium mb-1 block">Distance</label>
              <Select value={distanceFilter} onValueChange={setDistanceFilter}>
                <SelectTrigger>
                  <SelectValue placeholder="Toutes distances" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Toutes distances</SelectItem>
                  <SelectItem value="5">Moins de 5 km</SelectItem>
                  <SelectItem value="10">Moins de 10 km</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div>
              <label className="text-sm font-medium mb-1 block">Spécialité</label>
              <Select value={specialtyFilter} onValueChange={setSpecialtyFilter}>
                <SelectTrigger>
                  <SelectValue placeholder="Toutes spécialités" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Toutes spécialités</SelectItem>
                  <SelectItem value="freinage">Freinage</SelectItem>
                  <SelectItem value="vidange">Vidange</SelectItem>
                  <SelectItem value="contrôle">Contrôle technique</SelectItem>
                  <SelectItem value="électronique">Électronique</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Liste des garages */}
      <div className="grid gap-4">
        {filteredGarages.map((garage) => (
          <Card key={garage.id} className="hover:shadow-md transition-shadow">
            <CardContent className="p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="text-lg font-semibold text-gray-900">{garage.name}</h3>
                  <p className="text-sm text-gray-600 flex items-center gap-1 mt-1">
                    <MapPin className="w-4 h-4" />
                    {garage.address} • {garage.distance} km
                  </p>
                </div>
                <div className="text-right">
                  <div className="flex items-center gap-1 mb-1">
                    <Star className="w-4 h-4 text-yellow-500 fill-current" />
                    <span className="text-sm font-medium">{garage.rating}</span>
                  </div>
                  <Badge className={getAvailabilityColor(garage.availability)}>
                    {garage.availability}
                  </Badge>
                </div>
              </div>

              <div className="space-y-3">
                <div>
                  <h4 className="text-sm font-medium text-gray-900 mb-2">🛠️ Spécialités :</h4>
                  <div className="flex flex-wrap gap-1">
                    {garage.specialties.map((specialty, index) => (
                      <Badge key={index} variant="secondary" className="text-xs">
                        {specialty}
                      </Badge>
                    ))}
                  </div>
                </div>

                <div>
                  <h4 className="text-sm font-medium text-gray-900 mb-2">💰 Prestations :</h4>
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-2 text-xs">
                    {garage.services.slice(0, 3).map((service, index) => (
                      <div key={index} className="flex justify-between bg-gray-50 p-2 rounded">
                        <span>{service.name}</span>
                        {service.price && <span className="font-medium">{service.price}€</span>}
                      </div>
                    ))}
                  </div>
                </div>

                <div className="flex gap-2 pt-2">
                  <Button 
                    onClick={() => handleContactGarage(garage)}
                    className="flex-1"
                    size="sm"
                  >
                    <Phone className="w-4 h-4 mr-2" />
                    Contacter
                  </Button>
                  <Button 
                    onClick={() => handleBookService(garage)}
                    variant="outline"
                    size="sm"
                    className="flex-1"
                  >
                    <Wrench className="w-4 h-4 mr-2" />
                    Réserver
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      {filteredGarages.length === 0 && (
        <Card>
          <CardContent className="p-8 text-center">
            <Wrench className="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <p className="text-gray-600">Aucun garage trouvé avec ces filtres</p>
            <Button 
              variant="outline" 
              className="mt-4"
              onClick={() => {
                setDistanceFilter('all');
                setSpecialtyFilter('all');
              }}
            >
              Réinitialiser les filtres
            </Button>
          </CardContent>
        </Card>
      )}
    </div>
  );
};

export default GarageMap;
