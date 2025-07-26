import React, { useState } from 'react';
import { ArrowLeft, Upload, Calendar } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface VehicleFormProps {
  onBack: () => void;
}

const VehicleForm: React.FC<VehicleFormProps> = ({ onBack }) => {
  const [formData, setFormData] = useState({
    marque: '',
    immatriculation: '',
    modele: '',
    numeroVin: '',
    version: '',
    typeCarburant: '',
    annee: '',
    kilometrage: '',
    dateAchat: '',
    prixAchat: '',
    dateDernierCT: '',
    dateProchainCT: '',
    conducteurAssigne: '',
    statut: 'En service'
  });

  const handleInputChange = (field: string, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  return (
    <div className="flex-1 p-6 bg-gray-50">
      <div className="max-w-6xl mx-auto">
        {/* Header */}
        <div className="flex items-center mb-6">
          <Button
            variant="ghost"
            onClick={onBack}
            className="mr-4 p-2 hover:bg-gray-200"
          >
            <ArrowLeft className="w-5 h-5" />
          </Button>
          <h1 className="text-2xl font-semibold text-gray-900">Ajouter un véhicule</h1>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Vehicle Information */}
          <div className="lg:col-span-2">
            <Card>
              <CardHeader>
                <CardTitle className="text-lg font-semibold">Informations véhicule</CardTitle>
                <p className="text-sm text-gray-600">Détails techniques et administratifs</p>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="immatriculation">Immatriculation</Label>
                    <Input
                      id="immatriculation"
                      placeholder="Ex: AB-123-CD"
                      value={formData.immatriculation}
                      onChange={(e) => handleInputChange('immatriculation', e.target.value)}
                    />
                  </div>
                  <div>
                    <Label htmlFor="marque">Marque</Label>
                    <Select onValueChange={(value) => handleInputChange('marque', value)}>
                      <SelectTrigger>
                        <SelectValue placeholder="Sélectionner" />
                      </SelectTrigger>
                      <SelectContent className="bg-white border shadow-md z-50">
                        <SelectItem value="renault">Renault</SelectItem>
                        <SelectItem value="peugeot">Peugeot</SelectItem>
                        <SelectItem value="citroen">Citroën</SelectItem>
                        <SelectItem value="volkswagen">Volkswagen</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="modele">Modèle</Label>
                    <Input
                      id="modele"
                      placeholder="Ex: Clio, 308, Golf..."
                      value={formData.modele}
                      onChange={(e) => handleInputChange('modele', e.target.value)}
                    />
                  </div>
                  <div>
                    <Label htmlFor="numeroVin">Numéro VIN</Label>
                    <Input
                      id="numeroVin"
                      placeholder="Numéro d'identification du véhicule"
                      value={formData.numeroVin}
                      onChange={(e) => handleInputChange('numeroVin', e.target.value)}
                    />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="version">Version</Label>
                    <Input
                      id="version"
                      placeholder="Ex: 1.5 dCi 90ch Business"
                      value={formData.version}
                      onChange={(e) => handleInputChange('version', e.target.value)}
                    />
                  </div>
                  <div>
                    <Label htmlFor="typeCarburant">Type de carburant</Label>
                    <Select onValueChange={(value) => handleInputChange('typeCarburant', value)}>
                      <SelectTrigger>
                        <SelectValue placeholder="Sélectionner" />
                      </SelectTrigger>
                      <SelectContent className="bg-white border shadow-md z-50">
                        <SelectItem value="essence">Essence</SelectItem>
                        <SelectItem value="diesel">Diesel</SelectItem>
                        <SelectItem value="electrique">Électrique</SelectItem>
                        <SelectItem value="hybride">Hybride</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="annee">Année</Label>
                    <Input
                      id="annee"
                      placeholder="Ex: 2020"
                      value={formData.annee}
                      onChange={(e) => handleInputChange('annee', e.target.value)}
                    />
                  </div>
                  <div>
                    <Label htmlFor="kilometrage">Kilométrage</Label>
                    <Input
                      id="kilometrage"
                      placeholder="Kilométrage actuel"
                      value={formData.kilometrage}
                      onChange={(e) => handleInputChange('kilometrage', e.target.value)}
                    />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="dateAchat">Date d'achat</Label>
                    <div className="relative">
                      <Input
                        id="dateAchat"
                        placeholder="Sélectionner une date"
                        value={formData.dateAchat}
                        onChange={(e) => handleInputChange('dateAchat', e.target.value)}
                      />
                      <Calendar className="absolute right-3 top-3 w-4 h-4 text-gray-400" />
                    </div>
                  </div>
                  <div>
                    <Label htmlFor="prixAchat">Prix d'achat</Label>
                    <div className="relative">
                      <Input
                        id="prixAchat"
                        placeholder="Ex: 15000"
                        value={formData.prixAchat}
                        onChange={(e) => handleInputChange('prixAchat', e.target.value)}
                      />
                      <span className="absolute right-3 top-3 text-gray-400 text-sm">€</span>
                    </div>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="dateDernierCT">Date du dernier CT</Label>
                    <div className="relative">
                      <Input
                        id="dateDernierCT"
                        placeholder="Sélectionner une date"
                        value={formData.dateDernierCT}
                        onChange={(e) => handleInputChange('dateDernierCT', e.target.value)}
                      />
                      <Calendar className="absolute right-3 top-3 w-4 h-4 text-gray-400" />
                    </div>
                  </div>
                  <div>
                    <Label htmlFor="dateProchainCT">Date du prochain CT</Label>
                    <div className="relative">
                      <Input
                        id="dateProchainCT"
                        placeholder="Sélectionner une date"
                        value={formData.dateProchainCT}
                        onChange={(e) => handleInputChange('dateProchainCT', e.target.value)}
                      />
                      <Calendar className="absolute right-3 top-3 w-4 h-4 text-gray-400" />
                    </div>
                  </div>
                </div>

                <div>
                  <Label htmlFor="conducteurAssigne">Conducteur assigné</Label>
                  <Select onValueChange={(value) => handleInputChange('conducteurAssigne', value)}>
                    <SelectTrigger>
                      <SelectValue placeholder="Sélectionner" />
                    </SelectTrigger>
                    <SelectContent className="bg-white border shadow-md z-50">
                      <SelectItem value="jean-dupont">Jean Dupont</SelectItem>
                      <SelectItem value="marie-martin">Marie Martin</SelectItem>
                      <SelectItem value="pierre-durand">Pierre Durand</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Right column */}
          <div className="space-y-6">
            {/* Photo section */}
            <Card>
              <CardHeader>
                <CardTitle className="text-lg font-semibold">Photo</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                  <Upload className="mx-auto w-8 h-8 text-gray-400 mb-2" />
                  <p className="text-sm text-gray-600">Ajouter une photo du véhicule</p>
                </div>
              </CardContent>
            </Card>

            {/* Documents */}
            <Card>
              <CardHeader>
                <CardTitle className="text-lg font-semibold">Documents</CardTitle>
                <p className="text-sm text-gray-600">Ajouter des documents</p>
              </CardHeader>
              <CardContent>
                <div className="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                  <Upload className="mx-auto w-8 h-8 text-gray-400 mb-2" />
                  <p className="text-sm text-gray-600 mb-1">
                    Glissez-déposez ou<br />cliquez pour importer
                  </p>
                  <p className="text-xs text-gray-400">PDF, JPG ou PNG (max. 10 Mo)</p>
                </div>
                <Button variant="outline" className="w-full mt-4">
                  Parcourir
                </Button>
                <div className="mt-4">
                  <p className="text-sm font-medium text-gray-700 mb-2">Documents suggérés:</p>
                  <ul className="text-sm text-gray-600 space-y-1">
                    <li>• Carte grise</li>
                    <li>• Certificat d'assurance</li>
                    <li>• Dernier contrôle technique</li>
                    <li>• Facture d'achat</li>
                  </ul>
                </div>
              </CardContent>
            </Card>

            {/* Status */}
            <Card>
              <CardHeader>
                <CardTitle className="text-lg font-semibold">Statut initial</CardTitle>
                <p className="text-sm text-gray-600">État de service</p>
              </CardHeader>
              <CardContent>
                <Select value={formData.statut} onValueChange={(value) => handleInputChange('statut', value)}>
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent className="bg-white border shadow-md z-50">
                    <SelectItem value="En service">En service</SelectItem>
                    <SelectItem value="Maintenance">Maintenance</SelectItem>
                    <SelectItem value="Hors service">Hors service</SelectItem>
                  </SelectContent>
                </Select>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  );
};

export default VehicleForm;
