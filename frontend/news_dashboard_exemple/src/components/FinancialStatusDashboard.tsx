
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { DollarSign, TrendingUp, TrendingDown, FileText, Calculator, AlertTriangle, Download, Wrench, Users } from 'lucide-react';

const FinancialStatusDashboard: React.FC = () => {
  const [selectedVehicle, setSelectedVehicle] = useState<string>('all');

  const financialStats = [
    { title: 'Coût mensuel', value: '€2,450', icon: DollarSign, color: 'text-purple-600' },
    { title: 'Factures en attente', value: '7', icon: FileText, color: 'text-amber-600' },
    { title: 'Entretien moyen', value: '€340', icon: Calculator, color: 'text-orange-600' },
    { title: 'Évolution', value: '+12%', icon: TrendingUp, color: 'text-green-600' },
  ];

  // Données pour le graphique des dépenses mensuelles
  const monthlyExpenses = [
    { month: 'Jan', amount: 2100 },
    { month: 'Fev', amount: 2450 },
    { month: 'Mar', amount: 2800 },
    { month: 'Avr', amount: 2300 },
    { month: 'Mai', amount: 2650 },
    { month: 'Jun', amount: 2450 },
    { month: 'Jul', amount: 2890 },
    { month: 'Aoû', amount: 2340 },
    { month: 'Sep', amount: 2670 },
    { month: 'Oct', amount: 2520 },
    { month: 'Nov', amount: 2780 },
    { month: 'Déc', amount: 2450 },
  ];

  // Top 3 véhicules les plus coûteux
  const topExpensiveVehicles = [
    { name: 'Peugeot 308', plate: 'EF-456-GH', cost: '€840', interventions: 5, mainExpense: 'Réparation' },
    { name: 'Renault Clio', plate: 'AB-123-CD', cost: '€620', interventions: 3, mainExpense: 'Entretien' },
    { name: 'Citroën C3', plate: 'IJ-789-KL', cost: '€450', interventions: 2, mainExpense: 'CT' },
  ];

  // Dépenses par type
  const expensesByType = [
    { type: 'Entretien courant', amount: 1200, percentage: 35, color: 'bg-blue-500' },
    { type: 'Réparation', amount: 950, percentage: 28, color: 'bg-red-500' },
    { type: 'CT', amount: 680, percentage: 20, color: 'bg-orange-500' },
    { type: 'Assurance', amount: 450, percentage: 13, color: 'bg-green-500' },
    { type: 'Autres', amount: 120, percentage: 4, color: 'bg-gray-500' },
  ];

  // Véhicules disponibles pour le filtre
  const vehicles = [
    { value: 'all', label: 'Tous les véhicules' },
    { value: 'AB-123-CD', label: 'Renault Clio (AB-123-CD)' },
    { value: 'EF-456-GH', label: 'Peugeot 308 (EF-456-GH)' },
    { value: 'IJ-789-KL', label: 'Citroën C3 (IJ-789-KL)' },
  ];

  // Historique des dépenses
  const expenseHistory = [
    { vehicle: 'Peugeot 308', plate: 'EF-456-GH', date: '15/11/2024', type: 'Réparation', amount: '€340', invoice: '#F-2024-089' },
    { vehicle: 'Renault Clio', plate: 'AB-123-CD', date: '12/11/2024', type: 'Entretien', amount: '€180', invoice: '#F-2024-088' },
    { vehicle: 'Citroën C3', plate: 'IJ-789-KL', date: '08/11/2024', type: 'CT', amount: '€85', invoice: '#F-2024-087' },
    { vehicle: 'Peugeot 308', plate: 'EF-456-GH', date: '05/11/2024', type: 'Entretien', amount: '€220', invoice: '#F-2024-086' },
    { vehicle: 'Renault Clio', plate: 'AB-123-CD', date: '02/11/2024', type: 'Réparation', amount: '€150', invoice: '#F-2024-085' },
  ];

  // Données du véhicule sélectionné
  const getVehicleData = (vehicleId: string) => {
    const vehicleData = {
      'AB-123-CD': { cumulatedCost: '€1,240', maintenanceCount: 8, repairTotal: '€580', monthlyAvg: '€155', highestCost: '€340' },
      'EF-456-GH': { cumulatedCost: '€2,180', maintenanceCount: 12, repairTotal: '€950', monthlyAvg: '€220', highestCost: '€440' },
      'IJ-789-KL': { cumulatedCost: '€890', maintenanceCount: 5, repairTotal: '€320', monthlyAvg: '€89', highestCost: '€180' },
    };
    return vehicleData[vehicleId as keyof typeof vehicleData];
  };

  return (
    <div className="flex-1 p-6 bg-gray-50">
      <div className="max-w-7xl mx-auto">
        <h1 className="text-3xl font-bold text-gray-900 mb-8">État financier</h1>
        
        {/* Financial Statistics */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          {financialStats.map((stat) => {
            const Icon = stat.icon;
            return (
              <Card key={stat.title} className="hover:shadow-lg transition-shadow">
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm text-gray-600 mb-1">{stat.title}</p>
                      <p className="text-2xl font-bold text-gray-900">{stat.value}</p>
                    </div>
                    <div className={`p-3 rounded-full bg-gray-100 ${stat.color}`}>
                      <Icon className="w-6 h-6" />
                    </div>
                  </div>
                </CardContent>
              </Card>
            );
          })}
        </div>

        {/* Graphique dépenses mensuelles et Top 3 véhicules */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          {/* Graphique dépenses mensuelles */}
          <Card className="lg:col-span-1">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <TrendingUp className="w-5 h-5" />
                Dépenses mensuelles (12 mois)
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="h-64 flex items-end justify-between space-x-1">
                {monthlyExpenses.map((expense, index) => (
                  <div key={index} className="flex flex-col items-center flex-1">
                    <div 
                      className="bg-blue-500 w-full rounded-t min-w-[20px]"
                      style={{ height: `${(expense.amount / 3000) * 200}px` }}
                    ></div>
                    <p className="text-xs text-gray-600 mt-2">{expense.month}</p>
                    <p className="text-xs font-medium">€{expense.amount}</p>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Top 3 véhicules les plus coûteux */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <DollarSign className="w-5 h-5" />
                Top 3 véhicules les plus coûteux
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {topExpensiveVehicles.map((vehicle, index) => (
                  <div key={index} className="flex items-center justify-between p-3 border rounded-lg">
                    <div>
                      <p className="font-medium text-sm">{vehicle.name}</p>
                      <p className="text-xs text-gray-600">{vehicle.plate}</p>
                      <p className="text-xs text-gray-500">{vehicle.interventions} interventions - {vehicle.mainExpense}</p>
                    </div>
                    <span className="text-lg font-bold text-red-600">{vehicle.cost}</span>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Dépenses par type et Statistiques d'entretien */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          {/* Dépenses par type */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <FileText className="w-5 h-5" />
                Dépenses par type
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {expensesByType.map((expense, index) => (
                  <div key={index} className="space-y-2">
                    <div className="flex justify-between items-center">
                      <span className="text-sm font-medium">{expense.type}</span>
                      <span className="text-sm font-bold">€{expense.amount}</span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                      <div 
                        className={`h-2 rounded-full ${expense.color}`}
                        style={{ width: `${expense.percentage}%` }}
                      ></div>
                    </div>
                    <p className="text-xs text-gray-600">{expense.percentage}% du total</p>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Statistiques d'entretien */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Wrench className="w-5 h-5" />
                Statistiques d'entretien
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-2 gap-4">
                <div className="text-center p-4 bg-blue-50 rounded-lg">
                  <p className="text-2xl font-bold text-blue-600">25</p>
                  <p className="text-sm text-gray-600">Entretiens ce mois</p>
                </div>
                <div className="text-center p-4 bg-green-50 rounded-lg">
                  <p className="text-2xl font-bold text-green-600">156</p>
                  <p className="text-sm text-gray-600">Entretiens cette année</p>
                </div>
                <div className="text-center p-4 bg-purple-50 rounded-lg">
                  <p className="text-2xl font-bold text-purple-600">6.5</p>
                  <p className="text-sm text-gray-600">Moyenne par véhicule</p>
                </div>
                <div className="text-center p-4 bg-orange-50 rounded-lg">
                  <p className="text-2xl font-bold text-orange-600">€440</p>
                  <p className="text-sm text-gray-600">Plus cher ce mois</p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Filtre par véhicule */}
        <div className="mb-8">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Users className="w-5 h-5" />
                Filtre par véhicule
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                <Select value={selectedVehicle} onValueChange={setSelectedVehicle}>
                  <SelectTrigger className="w-full max-w-md">
                    <SelectValue placeholder="Sélectionner un véhicule" />
                  </SelectTrigger>
                  <SelectContent>
                    {vehicles.map((vehicle) => (
                      <SelectItem key={vehicle.value} value={vehicle.value}>
                        {vehicle.label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>

                {selectedVehicle !== 'all' && getVehicleData(selectedVehicle) && (
                  <div className="grid grid-cols-1 md:grid-cols-5 gap-4 mt-4">
                    <div className="text-center p-3 bg-gray-50 rounded-lg">
                      <p className="text-lg font-bold text-gray-900">{getVehicleData(selectedVehicle)?.cumulatedCost}</p>
                      <p className="text-xs text-gray-600">Coût cumulé</p>
                    </div>
                    <div className="text-center p-3 bg-blue-50 rounded-lg">
                      <p className="text-lg font-bold text-blue-600">{getVehicleData(selectedVehicle)?.maintenanceCount}</p>
                      <p className="text-xs text-gray-600">Entretiens effectués</p>
                    </div>
                    <div className="text-center p-3 bg-red-50 rounded-lg">
                      <p className="text-lg font-bold text-red-600">{getVehicleData(selectedVehicle)?.repairTotal}</p>
                      <p className="text-xs text-gray-600">Total réparations</p>
                    </div>
                    <div className="text-center p-3 bg-green-50 rounded-lg">
                      <p className="text-lg font-bold text-green-600">{getVehicleData(selectedVehicle)?.monthlyAvg}</p>
                      <p className="text-xs text-gray-600">Moyenne mensuelle</p>
                    </div>
                    <div className="text-center p-3 bg-orange-50 rounded-lg">
                      <p className="text-lg font-bold text-orange-600">{getVehicleData(selectedVehicle)?.highestCost}</p>
                      <p className="text-xs text-gray-600">Coût le plus élevé</p>
                    </div>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Alertes et Historique */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          {/* Alertes */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <AlertTriangle className="w-5 h-5" />
                Alertes financières
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                <div className="p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                  <p className="text-sm font-medium text-yellow-800">Coût élevé détecté</p>
                  <p className="text-xs text-yellow-700">Peugeot 308 - €840 ce mois</p>
                </div>
                <div className="p-3 bg-orange-50 border-l-4 border-orange-400 rounded">
                  <p className="text-sm font-medium text-orange-800">Tendance à la hausse</p>
                  <p className="text-xs text-orange-700">+15% sur les 3 derniers mois</p>
                </div>
                <div className="p-3 bg-red-50 border-l-4 border-red-400 rounded">
                  <p className="text-sm font-medium text-red-800">Budget dépassé</p>
                  <p className="text-xs text-red-700">Objectif mensuel: €2,200</p>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Historique des dépenses */}
          <Card className="lg:col-span-2">
            <CardHeader>
              <div className="flex items-center justify-between">
                <CardTitle className="flex items-center gap-2">
                  <FileText className="w-5 h-5" />
                  Historique des dépenses
                </CardTitle>
                <Button variant="outline" size="sm" className="flex items-center gap-2">
                  <Download className="w-4 h-4" />
                  Exporter
                </Button>
              </div>
            </CardHeader>
            <CardContent>
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Véhicule</TableHead>
                      <TableHead>Date</TableHead>
                      <TableHead>Type</TableHead>
                      <TableHead>Montant</TableHead>
                      <TableHead>Facture</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {expenseHistory.map((expense, index) => (
                      <TableRow key={index}>
                        <TableCell>
                          <div>
                            <p className="font-medium text-sm">{expense.vehicle}</p>
                            <p className="text-xs text-gray-600">{expense.plate}</p>
                          </div>
                        </TableCell>
                        <TableCell className="text-sm">{expense.date}</TableCell>
                        <TableCell>
                          <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                            expense.type === 'Réparation' ? 'bg-red-100 text-red-800' :
                            expense.type === 'Entretien' ? 'bg-blue-100 text-blue-800' :
                            'bg-orange-100 text-orange-800'
                          }`}>
                            {expense.type}
                          </span>
                        </TableCell>
                        <TableCell className="font-medium">{expense.amount}</TableCell>
                        <TableCell>
                          <Button variant="ghost" size="sm" className="text-blue-600 hover:text-blue-800">
                            {expense.invoice}
                          </Button>
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
    </div>
  );
};

export default FinancialStatusDashboard;
