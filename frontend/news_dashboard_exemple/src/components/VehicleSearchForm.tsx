import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Car, Search } from 'lucide-react';
import { toast } from 'sonner';
import { useVehicleSearchPersistence } from '@/hooks/useVehicleSearchPersistence';

interface VehicleInfo {
  licensePlate: string;
  brand: string;
  model: string;
  year: string;
  version: string;
}

interface VehicleSearchFormProps {
  onVehicleSelected: (vehicle: VehicleInfo, service: string) => void;
  prefilledLicensePlate?: string;
  prefilledService?: string;
}

const VehicleSearchForm: React.FC<VehicleSearchFormProps> = ({ 
  onVehicleSelected, 
  prefilledLicensePlate = '',
  prefilledService = ''
}) => {
  const { searchData, saveSearchData } = useVehicleSearchPersistence();
  
  const [searchMode, setSearchMode] = useState<'plate' | 'manual'>('plate');
  const [vehicleInfo, setVehicleInfo] = useState<VehicleInfo>({
    licensePlate: prefilledLicensePlate,
    brand: '',
    model: '',
    year: '',
    version: ''
  });
  const [selectedService, setSelectedService] = useState(prefilledService);
  const [isPlateRecognized, setIsPlateRecognized] = useState(false);

  // Restaurer les données sauvegardées au chargement
  useEffect(() => {
    if (searchData && !prefilledLicensePlate && !prefilledService) {
      setSearchMode(searchData.searchMode);
      setVehicleInfo(searchData.vehicleInfo);
      setSelectedService(searchData.selectedService);
      setIsPlateRecognized(searchData.isPlateRecognized);
    }
  }, [searchData, prefilledLicensePlate, prefilledService]);

  const brands = ['Renault', 'Peugeot', 'Citroën', 'Volkswagen', 'BMW', 'Mercedes', 'Audi', 'Toyota', 'Honda'];
  const models = ['Clio', '208', 'C3', 'Golf', 'Série 3', 'Classe A', 'A3', 'Yaris', 'Civic'];
  const years = ['2024', '2023', '2022', '2021', '2020', '2019', '2018', '2017', '2016', '2015'];
  
  const services = [
    'Vidange',
    'Changement plaquettes de frein',
    'Contrôle technique',
    'Révision complète',
    'Changement pneus',
    'Réparation échappement',
    'Diagnostic électronique',
    'Climatisation',
    'Distribution',
    'Embrayage'
  ];

  const handlePlateSearch = async () => {
    if (!vehicleInfo.licensePlate.trim()) {
      toast.error('Veuillez saisir une plaque d\'immatriculation');
      return;
    }

    // Simulation d'une recherche de plaque
    const mockVehicleData = {
      licensePlate: vehicleInfo.licensePlate,
      brand: 'Renault',
      model: 'Clio V',
      year: '2021',
      version: 'Zen 1.0 TCe 100'
    };

    setVehicleInfo(mockVehicleData);
    setIsPlateRecognized(true);
    toast.success('Véhicule trouvé !');
  };

  const handleSearch = () => {
    if (searchMode === 'plate' && !isPlateRecognized) {
      toast.error('Veuillez d\'abord rechercher les informations du véhicule');
      return;
    }

    if (searchMode === 'manual') {
      const { brand, model, year, version } = vehicleInfo;
      if (!brand || !model || !year || !version) {
        toast.error('Veuillez remplir toutes les informations du véhicule');
        return;
      }
    }

    if (!selectedService) {
      toast.error('Veuillez sélectionner une prestation');
      return;
    }

    // Sauvegarder les données de recherche
    saveSearchData({
      vehicleInfo,
      selectedService,
      searchMode,
      isPlateRecognized
    });

    onVehicleSelected(vehicleInfo, selectedService);
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Car className="w-5 h-5 text-blue-600" />
          Recherche de garage
        </CardTitle>
        <p className="text-sm text-gray-600">
          Commencez par identifier votre véhicule et la prestation souhaitée
        </p>
      </CardHeader>
      <CardContent className="space-y-4">
        {/* Mode de recherche */}
        <div className="flex gap-2">
          <Button 
            variant={searchMode === 'plate' ? 'default' : 'outline'}
            size="sm"
            onClick={() => setSearchMode('plate')}
          >
            Par plaque
          </Button>
          <Button 
            variant={searchMode === 'manual' ? 'default' : 'outline'}
            size="sm"
            onClick={() => setSearchMode('manual')}
          >
            Saisie manuelle
          </Button>
        </div>

        {/* Recherche par plaque */}
        {searchMode === 'plate' && (
          <div className="space-y-3">
            <div className="flex gap-2">
              <Input
                placeholder="Ex: AB-123-CD"
                value={vehicleInfo.licensePlate}
                onChange={(e) => setVehicleInfo(prev => ({ ...prev, licensePlate: e.target.value.toUpperCase() }))}
                className="flex-1"
              />
              <Button onClick={handlePlateSearch} disabled={!vehicleInfo.licensePlate.trim()}>
                <Search className="w-4 h-4" />
              </Button>
            </div>
            
            {isPlateRecognized && (
              <div className="p-3 bg-green-50 border border-green-200 rounded-lg">
                <div className="flex items-center gap-2 mb-2">
                  <Badge variant="secondary" className="bg-green-100 text-green-800">
                    Véhicule identifié
                  </Badge>
                </div>
                <div className="text-sm space-y-1">
                  <p><strong>Marque:</strong> {vehicleInfo.brand}</p>
                  <p><strong>Modèle:</strong> {vehicleInfo.model}</p>
                  <p><strong>Année:</strong> {vehicleInfo.year}</p>
                  <p><strong>Version:</strong> {vehicleInfo.version}</p>
                </div>
              </div>
            )}
          </div>
        )}

        {/* Saisie manuelle */}
        {searchMode === 'manual' && (
          <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
            <Select value={vehicleInfo.brand} onValueChange={(value) => setVehicleInfo(prev => ({ ...prev, brand: value }))}>
              <SelectTrigger>
                <SelectValue placeholder="Marque" />
              </SelectTrigger>
              <SelectContent>
                {brands.map(brand => (
                  <SelectItem key={brand} value={brand}>{brand}</SelectItem>
                ))}
              </SelectContent>
            </Select>

            <Select value={vehicleInfo.model} onValueChange={(value) => setVehicleInfo(prev => ({ ...prev, model: value }))}>
              <SelectTrigger>
                <SelectValue placeholder="Modèle" />
              </SelectTrigger>
              <SelectContent>
                {models.map(model => (
                  <SelectItem key={model} value={model}>{model}</SelectItem>
                ))}
              </SelectContent>
            </Select>

            <Select value={vehicleInfo.year} onValueChange={(value) => setVehicleInfo(prev => ({ ...prev, year: value }))}>
              <SelectTrigger>
                <SelectValue placeholder="Année" />
              </SelectTrigger>
              <SelectContent>
                {years.map(year => (
                  <SelectItem key={year} value={year}>{year}</SelectItem>
                ))}
              </SelectContent>
            </Select>

            <Input
              placeholder="Version (ex: Zen 1.0 TCe)"
              value={vehicleInfo.version}
              onChange={(e) => setVehicleInfo(prev => ({ ...prev, version: e.target.value }))}
            />
          </div>
        )}

        {/* Sélection de prestation */}
        <div>
          <label className="text-sm font-medium mb-2 block">Prestation souhaitée</label>
          <Select value={selectedService} onValueChange={setSelectedService}>
            <SelectTrigger>
              <SelectValue placeholder="Sélectionner une prestation" />
            </SelectTrigger>
            <SelectContent>
              {services.map(service => (
                <SelectItem key={service} value={service}>{service}</SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>

        {/* Bouton de recherche */}
        <Button onClick={handleSearch} className="w-full" size="lg">
          <Search className="w-4 h-4 mr-2" />
          Rechercher des garages
        </Button>
      </CardContent>
    </Card>
  );
};

export default VehicleSearchForm;
