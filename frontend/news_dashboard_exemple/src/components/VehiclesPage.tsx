
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Search, Download } from 'lucide-react';

const VehiclesPage: React.FC = () => {
  const [searchTerm, setSearchTerm] = useState('');

  const vehicles = [
    {
      id: 1,
      name: 'Renault Clio',
      plate: 'AB-123-CD',
      status: 'En service',
      lastMaintenance: '15/03/2024',
      nextCT: '15/04/2025',
      mileage: '45,200 km',
      estimatedValue: '€12,500',
      totalMaintenanceCost: '€2,340',
      interventions: 8,
      ctStatus: 'OK',
      adminStatus: 'Conforme',
      lastUser: 'Jean Dupont',
      recentParts: ['Plaquettes de frein', 'Filtres'],
      alertLevel: 'low'
    },
    {
      id: 2,
      name: 'Peugeot 308',
      plate: 'EF-456-GH',
      status: 'Maintenance',
      lastMaintenance: '10/03/2024',
      nextCT: '02/02/2024',
      mileage: '78,900 km',
      estimatedValue: '€15,800',
      totalMaintenanceCost: '€3,120',
      interventions: 12,
      ctStatus: 'Expiré',
      adminStatus: 'Attention',
      lastUser: 'Marie Martin',
      recentParts: ['Pneumatiques', 'Courroie'],
      alertLevel: 'high'
    },
    {
      id: 3,
      name: 'Citroën C3',
      plate: 'IJ-789-KL',
      status: 'En service',
      lastMaintenance: '22/02/2024',
      nextCT: '10/08/2024',
      mileage: '32,100 km',
      estimatedValue: '€18,200',
      totalMaintenanceCost: '€1,890',
      interventions: 5,
      ctStatus: 'À prévoir',
      adminStatus: 'Conforme',
      lastUser: 'Pierre Durand',
      recentParts: ['Huile moteur', 'Filtres'],
      alertLevel: 'medium'
    }
  ];

  const filteredVehicles = vehicles.filter(vehicle =>
    vehicle.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    vehicle.plate.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'En service': return 'bg-green-100 text-green-800';
      case 'Maintenance': return 'bg-orange-100 text-orange-800';
      case 'Hors service': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <div className="flex-1 p-3 sm:p-6 bg-gray-50">
      <div className="max-w-7xl mx-auto">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between mb-6 sm:mb-8 gap-4">
          <h1 className="text-2xl sm:text-3xl font-bold text-gray-900">Véhicules</h1>
          <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
              <Input
                placeholder="Rechercher un véhicule..."
                className="pl-10 w-full sm:w-64"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
              />
            </div>
            <Button variant="outline" className="whitespace-nowrap">
              <Download className="w-4 h-4 mr-2" />
              Exporter
            </Button>
          </div>
        </div>

        {/* Table View Only */}
        <Card>
          <CardHeader>
            <CardTitle>Liste des véhicules</CardTitle>
          </CardHeader>
          <CardContent className="p-0 sm:p-6">
            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead className="min-w-[120px]">Véhicule</TableHead>
                    <TableHead className="min-w-[100px]">Statut</TableHead>
                    <TableHead className="min-w-[100px] hidden sm:table-cell">Kilométrage</TableHead>
                    <TableHead className="min-w-[120px] hidden md:table-cell">Dernier entretien</TableHead>
                    <TableHead className="min-w-[120px] hidden lg:table-cell">Prochain CT</TableHead>
                    <TableHead className="min-w-[120px] hidden lg:table-cell">Coût maintenance</TableHead>
                    <TableHead className="min-w-[100px] hidden xl:table-cell">État admin</TableHead>
                    <TableHead className="min-w-[120px]">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredVehicles.map((vehicle) => (
                    <TableRow key={vehicle.id}>
                      <TableCell>
                        <div>
                          <p className="font-medium text-sm sm:text-base">{vehicle.name}</p>
                          <p className="text-xs sm:text-sm text-gray-600">{vehicle.plate}</p>
                        </div>
                      </TableCell>
                      <TableCell>
                        <Badge className={`${getStatusColor(vehicle.status)} text-xs`}>
                          {vehicle.status}
                        </Badge>
                      </TableCell>
                      <TableCell className="hidden sm:table-cell text-sm">{vehicle.mileage}</TableCell>
                      <TableCell className="hidden md:table-cell text-sm">{vehicle.lastMaintenance}</TableCell>
                      <TableCell className={`hidden lg:table-cell text-sm ${vehicle.ctStatus === 'Expiré' ? 'text-red-600' : ''}`}>
                        {vehicle.nextCT}
                      </TableCell>
                      <TableCell className="text-red-600 hidden lg:table-cell text-sm">{vehicle.totalMaintenanceCost}</TableCell>
                      <TableCell className="hidden xl:table-cell">
                        <Badge variant={vehicle.adminStatus === 'Conforme' ? 'default' : 'destructive'} className="text-xs">
                          {vehicle.adminStatus}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        <div className="flex flex-col sm:flex-row gap-1 sm:gap-2">
                          <Button size="sm" variant="outline" className="text-xs px-2 py-1">
                            Voir
                          </Button>
                          <Button size="sm" variant="outline" className="text-xs px-2 py-1">
                            PDF
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default VehiclesPage;
