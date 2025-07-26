
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Receipt, Search, Download, Eye, Upload, FileText, Car, Calendar, MapPin, Euro } from 'lucide-react';
import { toast } from 'sonner';

interface Facture {
  id: string;
  vehicleId: string;
  vehiclePlate: string;
  vehicleModel: string;
  reservationId: string;
  reservationDate: string;
  garageName: string;
  garageAddress: string;
  interventionType: string;
  amount: number;
  invoiceNumber: string;
  invoiceDate: string;
  fileName: string;
  fileUrl: string;
  uploadDate: string;
  uploadedBy: 'client' | 'garage';
  paymentStatus: 'paid' | 'pending' | 'overdue';
}

const MesFacturesPage: React.FC = () => {
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedFilter, setSelectedFilter] = useState('all');

  // Données exemple des factures
  const factures: Facture[] = [
    {
      id: 'f-1',
      vehicleId: 'v-1',
      vehiclePlate: 'AB-123-CD',
      vehicleModel: 'Peugeot 308',
      reservationId: 'r-1',
      reservationDate: '2024-03-10',
      garageName: 'Garage Central Paris',
      garageAddress: '15 Rue de la Paix, 75001 Paris',
      interventionType: 'Révision complète',
      amount: 350.00,
      invoiceNumber: 'FACT-2024-001',
      invoiceDate: '2024-03-10',
      fileName: 'Facture_FACT-2024-001.pdf',
      fileUrl: '/documents/facture_fact-2024-001.pdf',
      uploadDate: '2024-03-10',
      uploadedBy: 'garage',
      paymentStatus: 'paid'
    },
    {
      id: 'f-2',
      vehicleId: 'v-2',
      vehiclePlate: 'EF-456-GH',
      vehicleModel: 'Renault Clio',
      reservationId: 'r-2',
      reservationDate: '2024-02-15',
      garageName: 'Auto Service Bastille',
      garageAddress: '8 Avenue Parmentier, 75011 Paris',
      interventionType: 'Changement plaquettes de frein',
      amount: 180.50,
      invoiceNumber: 'INV-2024-025',
      invoiceDate: '2024-02-15',
      fileName: 'Facture_INV-2024-025.pdf',
      fileUrl: '/documents/facture_inv-2024-025.pdf',
      uploadDate: '2024-02-16',
      uploadedBy: 'client',
      paymentStatus: 'paid'
    },
    {
      id: 'f-3',
      vehicleId: 'v-3',
      vehiclePlate: 'IJ-789-KL',
      vehicleModel: 'Citroën C3',
      reservationId: 'r-3',
      reservationDate: '2024-03-20',
      garageName: 'Méca Express Montparnasse',
      garageAddress: '22 Boulevard Montparnasse, 75014 Paris',
      interventionType: 'Vidange + Filtres',
      amount: 85.00,
      invoiceNumber: 'MECA-2024-078',
      invoiceDate: '2024-03-20',
      fileName: 'Facture_MECA-2024-078.pdf',
      fileUrl: '/documents/facture_meca-2024-078.pdf',
      uploadDate: '2024-03-20',
      uploadedBy: 'garage',
      paymentStatus: 'pending'
    },
    {
      id: 'f-4',
      vehicleId: 'v-1',
      vehiclePlate: 'AB-123-CD',
      vehicleModel: 'Peugeot 308',
      reservationId: 'r-4',
      reservationDate: '2024-01-25',
      garageName: 'Garage du Marais',
      garageAddress: '12 Rue des Rosiers, 75004 Paris',
      interventionType: 'Réparation climatisation',
      amount: 245.75,
      invoiceNumber: 'GM-2024-015',
      invoiceDate: '2024-01-25',
      fileName: 'Facture_GM-2024-015.pdf',
      fileUrl: '/documents/facture_gm-2024-015.pdf',
      uploadDate: '2024-01-26',
      uploadedBy: 'client',
      paymentStatus: 'paid'
    }
  ];

  const handleSearch = () => {
    console.log('Recherche:', searchTerm);
    toast.success(`Recherche mise à jour pour "${searchTerm}"`);
  };

  const handleDownload = (facture: Facture) => {
    console.log('Téléchargement facture:', facture.fileName);
    toast.success(`Téléchargement de ${facture.fileName} initié`);
  };

  const handleView = (facture: Facture) => {
    console.log('Visualisation facture:', facture.fileName);
    toast.success(`Ouverture de ${facture.fileName}`);
  };

  const handleUpload = () => {
    console.log('Upload nouvelle facture');
    toast.success('Fonctionnalité d\'upload en cours de développement');
  };

  const getPaymentStatusBadge = (status: string) => {
    switch (status) {
      case 'paid':
        return <Badge className="bg-green-100 text-green-800">Payée</Badge>;
      case 'pending':
        return <Badge className="bg-orange-100 text-orange-800">En attente</Badge>;
      case 'overdue':
        return <Badge className="bg-red-100 text-red-800">En retard</Badge>;
      default:
        return <Badge variant="secondary">{status}</Badge>;
    }
  };

  const getUploadSourceBadge = (source: string) => {
    switch (source) {
      case 'garage':
        return <Badge variant="outline" className="text-blue-600 border-blue-300">Garage partenaire</Badge>;
      case 'client':
        return <Badge variant="outline" className="text-gray-600">Ajoutée manuellement</Badge>;
      default:
        return <Badge variant="secondary">{source}</Badge>;
    }
  };

  const filteredFactures = factures.filter(facture => {
    const matchesSearch = searchTerm === '' || 
      facture.vehiclePlate.toLowerCase().includes(searchTerm.toLowerCase()) ||
      facture.garageName.toLowerCase().includes(searchTerm.toLowerCase()) ||
      facture.interventionType.toLowerCase().includes(searchTerm.toLowerCase());
    
    const matchesFilter = selectedFilter === 'all' || 
      (selectedFilter === 'garage' && facture.uploadedBy === 'garage') ||
      (selectedFilter === 'client' && facture.uploadedBy === 'client');
    
    return matchesSearch && matchesFilter;
  });

  const totalAmount = filteredFactures.reduce((sum, facture) => sum + facture.amount, 0);

  return (
    <div className="flex-1 p-3 sm:p-6 bg-gray-50">
      <div className="max-w-7xl mx-auto">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between mb-6 sm:mb-8 gap-4">
          <h1 className="text-2xl sm:text-3xl font-bold text-gray-900">
            Mes Factures
          </h1>
          <Button onClick={handleUpload} className="flex items-center gap-2">
            <Upload className="w-4 h-4" />
            Ajouter une facture
          </Button>
        </div>

        {/* Statistiques */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
          <Card>
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Total factures</p>
                  <p className="text-xl sm:text-2xl font-bold text-blue-600">{factures.length}</p>
                </div>
                <Receipt className="w-6 sm:w-8 h-6 sm:h-8 text-blue-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Montant total</p>
                  <p className="text-xl sm:text-2xl font-bold text-green-600">
                    {factures.reduce((sum, f) => sum + f.amount, 0).toFixed(2)}€
                  </p>
                </div>
                <Euro className="w-6 sm:w-8 h-6 sm:h-8 text-green-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Garages partenaires</p>
                  <p className="text-xl sm:text-2xl font-bold text-purple-600">
                    {factures.filter(f => f.uploadedBy === 'garage').length}
                  </p>
                </div>
                <MapPin className="w-6 sm:w-8 h-6 sm:h-8 text-purple-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Ce mois</p>
                  <p className="text-xl sm:text-2xl font-bold text-orange-600">2</p>
                </div>
                <Calendar className="w-6 sm:w-8 h-6 sm:h-8 text-orange-600" />
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
                  placeholder="Rechercher par plaque, garage ou intervention..."
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
                  <option value="all">Toutes les factures</option>
                  <option value="garage">Garages partenaires</option>
                  <option value="client">Ajoutées manuellement</option>
                </select>
                <Button onClick={handleSearch} className="flex items-center gap-2">
                  <Search className="w-4 h-4" />
                  Rechercher
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Liste des factures */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-lg sm:text-xl">
              <Receipt className="w-5 h-5 text-blue-600" />
              Mes factures ({filteredFactures.length}) - Total: {totalAmount.toFixed(2)}€
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4">
              {filteredFactures.map((facture) => (
                <div key={facture.id} className="border rounded-lg p-4 hover:shadow-md transition-shadow">
                  <div className="flex justify-between items-start mb-3">
                    <div className="flex-1">
                      <div className="flex items-center gap-2 mb-2">
                        <h3 className="text-lg font-semibold text-gray-900">
                          {facture.invoiceNumber} - {facture.vehiclePlate}
                        </h3>
                        {getPaymentStatusBadge(facture.paymentStatus)}
                        {getUploadSourceBadge(facture.uploadedBy)}
                      </div>
                      <p className="text-sm text-gray-600">{facture.vehicleModel}</p>
                      <p className="text-sm text-gray-600 flex items-center gap-1 mt-1">
                        <MapPin className="w-4 h-4" />
                        {facture.garageName}
                      </p>
                      <p className="text-sm text-gray-600 mt-1">
                        <strong>Intervention:</strong> {facture.interventionType}
                      </p>
                      <p className="text-sm text-gray-600 flex items-center gap-1 mt-1">
                        <Calendar className="w-4 h-4" />
                        Date: {new Date(facture.invoiceDate).toLocaleDateString('fr-FR')}
                      </p>
                    </div>
                    <div className="text-right">
                      <div className="text-lg font-bold text-green-600">{facture.amount.toFixed(2)}€</div>
                      <div className="text-sm text-gray-500">
                        Ajoutée le {new Date(facture.uploadDate).toLocaleDateString('fr-FR')}
                      </div>
                    </div>
                  </div>
                  <div className="flex gap-2">
                    <Button 
                      onClick={() => handleView(facture)}
                      variant="outline"
                      size="sm"
                      className="flex items-center gap-1"
                    >
                      <Eye className="w-4 h-4" />
                      Voir
                    </Button>
                    <Button 
                      onClick={() => handleDownload(facture)}
                      size="sm"
                      className="flex items-center gap-1"
                    >
                      <Download className="w-4 h-4" />
                      Télécharger
                    </Button>
                  </div>
                </div>
              ))}
              {filteredFactures.length === 0 && (
                <div className="text-center py-8 text-gray-500">
                  <Receipt className="w-12 h-12 mx-auto mb-4 text-gray-300" />
                  <p>Aucune facture trouvée</p>
                  <p className="text-sm">Ajoutez vos factures après vos interventions en garage</p>
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default MesFacturesPage;
