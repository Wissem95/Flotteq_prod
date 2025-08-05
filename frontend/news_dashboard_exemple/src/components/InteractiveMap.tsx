
import React, { useEffect, useState, useRef } from 'react';
import { MapContainer, TileLayer, Marker, Popup, useMap, useMapEvents } from 'react-leaflet';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { Button } from '@/components/ui/button';
import { MapPin, Star, Euro, Navigation } from 'lucide-react';

// Fix pour les icônes Leaflet
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

interface Garage {
  id: string;
  name: string;
  address: string;
  distance: number;
  coordinates: { lat: number; lng: number };
  rating: number;
  priceForService: number;
  phone: string;
}

interface InteractiveMapProps {
  garages: Garage[];
  center: { lat: number; lng: number };
  selectedService: string;
  onReserve: (garage: Garage) => void;
  onMapMove?: (center: { lat: number; lng: number }, zoom: number) => void;
}

// Composant pour créer une icône personnalisée avec prix
const createPriceIcon = (price: number) => {
  return L.divIcon({
    html: `<div style="background: #ef4444; color: white; padding: 4px 8px; border-radius: 12px; font-weight: bold; font-size: 12px; white-space: nowrap; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">${price}€</div>`,
    className: 'custom-price-marker',
    iconSize: [40, 24],
    iconAnchor: [20, 24],
  });
};

// Composant pour gérer les événements de la carte
const MapEventHandler: React.FC<{ onMapMove?: (center: { lat: number; lng: number }, zoom: number) => void }> = ({ onMapMove }) => {
  const map = useMapEvents({
    moveend: () => {
      if (onMapMove) {
        const center = map.getCenter();
        const zoom = map.getZoom();
        onMapMove({ lat: center.lat, lng: center.lng }, zoom);
      }
    },
  });
  return null;
};

// Composant pour recentrer la carte
const MapController: React.FC<{ center: { lat: number; lng: number } }> = ({ center }) => {
  const map = useMap();
  
  useEffect(() => {
    map.setView([center.lat, center.lng], map.getZoom());
  }, [center, map]);
  
  return null;
};

const InteractiveMap: React.FC<InteractiveMapProps> = ({
  garages,
  center,
  selectedService,
  onReserve,
  onMapMove
}) => {
  const [userLocation, setUserLocation] = useState<{ lat: number; lng: number } | null>(null);

  useEffect(() => {
    // Demander la géolocalisation
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          setUserLocation({
            lat: position.coords.latitude,
            lng: position.coords.longitude
          });
        },
        (error) => {
        }
      );
    }
  }, []);

  return (
    <div className="w-full h-96 rounded-lg overflow-hidden border">
      <MapContainer
        center={[center.lat, center.lng]}
        zoom={13}
        style={{ height: '100%', width: '100%' }}
        scrollWheelZoom={true}
      >
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        
        <MapController center={center} />
        <MapEventHandler onMapMove={onMapMove} />
        
        {/* Marker pour la position de l'utilisateur */}
        {userLocation && (
          <Marker
            position={[userLocation.lat, userLocation.lng]}
            icon={L.divIcon({
              html: `<div style="background: #3b82f6; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
              className: 'user-location-marker',
              iconSize: [16, 16],
              iconAnchor: [8, 8],
            })}
          >
            <Popup>
              <div className="text-center">
                <Navigation className="w-4 h-4 mx-auto mb-1 text-blue-600" />
                <p className="font-medium text-sm">Votre position</p>
              </div>
            </Popup>
          </Marker>
        )}
        
        {/* Markers pour les garages */}
        {garages.map((garage) => (
          <Marker
            key={garage.id}
            position={[garage.coordinates.lat, garage.coordinates.lng]}
            icon={createPriceIcon(garage.priceForService)}
          >
            <Popup maxWidth={280}>
              <div className="space-y-3 p-2">
                <div>
                  <h3 className="font-semibold text-base text-gray-900 mb-1">
                    {garage.name}
                  </h3>
                  <p className="text-sm text-gray-600 flex items-center gap-1 mb-1">
                    <MapPin className="w-3 h-3" />
                    {garage.address}
                  </p>
                  <div className="flex items-center gap-2 text-sm">
                    <div className="flex items-center gap-1">
                      <Star className="w-3 h-3 text-yellow-500 fill-current" />
                      <span className="font-medium">{garage.rating}</span>
                    </div>
                    <span className="text-gray-500">•</span>
                    <span className="text-gray-600">{garage.distance} km</span>
                  </div>
                </div>
                
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-1 text-lg font-bold text-green-600">
                    <Euro className="w-4 h-4" />
                    {garage.priceForService}
                  </div>
                  <Button
                    onClick={() => onReserve(garage)}
                    size="sm"
                    className="text-xs"
                  >
                    Réserver
                  </Button>
                </div>
                
                <p className="text-xs text-gray-500">
                  pour {selectedService}
                </p>
              </div>
            </Popup>
          </Marker>
        ))}
      </MapContainer>
    </div>
  );
};

export default InteractiveMap;
