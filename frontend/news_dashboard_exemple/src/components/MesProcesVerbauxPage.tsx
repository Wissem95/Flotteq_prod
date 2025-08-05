
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { ClipboardCheck, Search, Download, Eye, Upload, FileText, Car, Calendar, MapPin } from 'lucide-react';
import { toast } from 'sonner';

interface ProcesVerbal {
  id: string;
  vehicleId: string;
  vehiclePlate: string;
  vehicleModel: string;
  ctAppointmentDate: string;
  controlDate: string;
  validityDate: string;
  centerName: string;
  centerAddress: string;
  result: 'favorable' | 'defavorable' | 'contre-visite';
  fileName: string;
  fileUrl: string;
  uploadDate: string;
  observations?: string;
}

const MesProcesVerbauxPage: React.FC = () => {
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedFilter, setSelectedFilter] = useState('all');

  // Données exemple des procès-verbaux
  const procesVerbaux: ProcesVerbal[] = [
    {
      id: 'pv-1',
      vehicleId: 'v-1',
      vehiclePlate: 'AB-123-CD',
      vehicleModel: 'Peugeot 308',
      ctAppointmentDate: '2024-03-15',
      controlDate: '2024-03-15',
      validityDate: '2026-03-15',
      centerName: 'Centre CT Paris République',
      centerAddress: '12 Avenue de la République, 75011 Paris',
      result: 'favorable',
      fileName: 'PV_CT_AB123CD_20240315.pdf',
      fileUrl: '/documents/pv_ct_ab123cd_20240315.pdf',
      uploadDate: '2024-03-15',
      observations: 'Contrôle technique favorable sans observation'
    },
    {
      id: 'pv-2',
      vehicleId: 'v-2',
      vehiclePlate: 'EF-456-GH',
      vehicleModel: 'Renault Clio',
      ctAppointmentDate: '2024-02-20',
      controlDate: '2024-02-20',
      validityDate: '2026-02-20',
      centerName: 'AutoContrôle Bastille',
      centerAddress: '5 Rue de la Bastille, 75012 Paris',
      result: 'contre-visite',
      fileName: 'PV_CT_EF456GH_20240220.pdf',
      fileUrl: '/documents/pv_ct_ef456gh_20240220.pdf',
      uploadDate: '2024-02-20',
      observations: 'Contre-visite nécessaire - Défaillance mineure sur éclairage'
    },
    {
      id: 'pv-3',
      vehicleId: 'v-3',
      vehiclePlate: 'IJ-789-KL',
      vehicleModel: 'Citroën C3',
      ctAppointmentDate: '2024-01-10',
      controlDate: '2024-01-10',
      validityDate: '2026-01-10',
      centerName: 'CT Express Montparnasse',
      centerAddress: '18 Boulevard Montparnasse, 75014 Paris',
      result: 'favorable',
      fileName: 'PV_CT_IJ789KL_20240110.pdf',
      fileUrl: '/documents/pv_ct_ij789kl_20240110.pdf',
      uploadDate: '2024-01-10'
    }
  ];

  const handleSearch = () => {
    toast.success(`Recherche mise à jour pour "${searchTerm}"`);
  };

  const handleDownload = (pv: ProcesVerbal) => {
    toast.success(`Téléchargement de ${pv.fileName} initié`);
  };

  const handleView = (pv: ProcesVerbal) => {
    toast.success(`Ouverture de ${pv.fileName}`);
  };

  const handleUpload = () => {
    toast.success('Fonctionnalité d\'upload en cours de développement');
  };

  const getResultBadge = (result: string) => {
    switch (result) {
      case 'favorable':
        return <Badge className="bg-green-100 text-green-800">Favorable</Badge>;
      case 'defavorable':
        return <Badge className="bg-red-100 text-red-800">Défavorable</Badge>;
      case 'contre-visite':
        return <Badge className="bg-orange-100 text-orange-800">Contre-visite</Badge>;
      default:
        return <Badge variant="secondary">{result}</Badge>;
    }
  };

  const filteredPV = procesVerbaux.filter(pv => {
    const matchesSearch = searchTerm === '' || 
      pv.vehiclePlate.toLowerCase().includes(searchTerm.toLowerCase()) ||
      pv.vehicleModel.toLowerCase().includes(searchTerm.toLowerCase()) ||
      pv.centerName.toLowerCase().includes(searchTerm.toLowerCase());
    
    const matchesFilter = selectedFilter === 'all' || pv.result === selectedFilter;
    
    return matchesSearch && matchesFilter;
  });

  return (
    <div className="flex-1 p-3 sm:p-6 bg-gray-50">
      <div className="max-w-7xl mx-auto">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between mb-6 sm:mb-8 gap-4">
          <h1 className="text-2xl sm:text-3xl font-bold text-gray-900">
            Mes Procès-Verbaux de Contrôle Technique
          </h1>
          <Button onClick={handleUpload} className="flex items-center gap-2">
            <Upload className="w-4 h-4" />
            Ajouter un PV
          </Button>
        </div>

        {/* Statistiques */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
          <Card>
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Total PV</p>
                  <p className="text-xl sm:text-2xl font-bold text-blue-600">{procesVerbaux.length}</p>
                </div>
                <FileText className="w-6 sm:w-8 h-6 sm:h-8 text-blue-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Favorables</p>
                  <p className="text-xl sm:text-2xl font-bold text-green-600">
                    {procesVerbaux.filter(pv => pv.result === 'favorable').length}
                  </p>
                </div>
                <ClipboardCheck className="w-6 sm:w-8 h-6 sm:h-8 text-green-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Contre-visites</p>
                  <p className="text-xl sm:text-2xl font-bold text-orange-600">
                    {procesVerbaux.filter(pv => pv.result === 'contre-visite').length}
                  </p>
                </div>
                <Calendar className="w-6 sm:w-8 h-6 sm:h-8 text-orange-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Ce mois</p>
                  <p className="text-xl sm:text-2xl font-bold text-purple-600">2</p>
                </div>
                <Car className="w-6 sm:w-8 h-6 sm:h-8 text-purple-600" />
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Barre de recherche et filtres */}
        <Card className="mb-6">
          <CardContent className="p-4">
            <div className="flex flex-col sm:flex-row gap-4">
              <div className="flex-1">
                <Input
                  type="text"
                  placeholder="Rechercher par plaque, modèle ou centre..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="w-full"
                />
              </div>
              <div className="flex gap-2">
                <select
                  value={selectedFilter}
                  onChange={(e) => setSelectedFilter(e.target.value)}
                  className="px-3 py-2 border border-gray-300 rounded-md text-sm"
                >
                  <option value="all">Tous les résultats</option>
                  <option value="favorable">Favorable</option>
                  <option value="contre-visite">Contre-visite</option>
                  <option value="defavorable">Défavorable</option>
                </select>
                <Button onClick={handleSearch} className="flex items-center gap-2">
                  <Search className="w-4 h-4" />
                  Rechercher
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Liste des procès-verbaux */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-lg sm:text-xl">
              <ClipboardCheck className="w-5 h-5 text-blue-600" />
              Mes procès-verbaux ({filteredPV.length})
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4">
              {filteredPV.map((pv) => (
                <div key={pv.id} className="border rounded-lg p-4 hover:shadow-md transition-shadow">
                  <div className="flex justify-between items-start mb-3">
                    <div className="flex-1">
                      <div className="flex items-center gap-2 mb-2">
                        <h3 className="text-lg font-semibold text-gray-900">{pv.vehiclePlate}</h3>
                        {getResultBadge(pv.result)}
                      </div>
                      <p className="text-sm text-gray-600">{pv.vehicleModel}</p>
                      <p className="text-sm text-gray-600 flex items-center gap-1 mt-1">
                        <MapPin className="w-4 h-4" />
                        {pv.centerName}
                      </p>
                      <p className="text-sm text-gray-600 flex items-center gap-1 mt-1">
                        <Calendar className="w-4 h-4" />
                        Contrôle: {new Date(pv.controlDate).toLocaleDateString('fr-FR')} • 
                        Validité: {new Date(pv.validityDate).toLocaleDateString('fr-FR')}
                      </p>
                      {pv.observations && (
                        <p className="text-sm text-gray-500 mt-2 italic">{pv.observations}</p>
                      )}
                    </div>
                  </div>
                  <div className="flex gap-2">
                    <Button 
                      onClick={() => handleView(pv)}
                      variant="outline"
                      size="sm"
                      className="flex items-center gap-1"
                    >
                      <Eye className="w-4 h-4" />
                      Voir
                    </Button>
                    <Button 
                      onClick={() => handleDownload(pv)}
                      size="sm"
                      className="flex items-center gap-1"
                    >
                      <Download className="w-4 h-4" />
                      Télécharger
                    </Button>
                  </div>
                </div>
              ))}
              {filteredPV.length === 0 && (
                <div className="text-center py-8 text-gray-500">
                  <FileText className="w-12 h-12 mx-auto mb-4 text-gray-300" />
                  <p>Aucun procès-verbal trouvé</p>
                  <p className="text-sm">Ajoutez vos procès-verbaux après vos contrôles techniques</p>
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default MesProcesVerbauxPage;
