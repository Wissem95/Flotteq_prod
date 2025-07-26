import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Car, Users, AlertTriangle, TrendingUp, MapPin, Wrench, FileText, TrendingDown, Calendar, DollarSign, Clock, Activity, CheckCircle, Check } from 'lucide-react';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

const FleetStatusDashboard: React.FC = () => {
  // 1. Zone haute - tuiles résumé avec icônes et barres de progression
  const summaryStats = [
    { 
      title: 'Véhicules', 
      subtitle: 'État de votre flotte',
      value: '24', 
      progressText: '92% en état de marche',
      progressValue: 92,
      progressColor: 'bg-blue-500',
      textColor: 'text-green-600',
      icon: Car 
    },
    { 
      title: 'Contrôles techniques', 
      subtitle: 'Prochaines échéances',
      value: '5', 
      progressText: '3 dans le mois à venir',
      progressValue: 60,
      progressColor: 'bg-blue-300',
      textColor: 'text-orange-600',
      icon: Calendar 
    },
    { 
      title: 'Alertes', 
      subtitle: 'Problèmes à résoudre',
      value: '7', 
      progressText: '2 urgences à traiter',
      progressValue: 28,
      progressColor: 'bg-red-300',
      textColor: 'text-red-600',
      icon: AlertTriangle 
    },
  ];

  // Données pour le statut de la flotte - format tuiles comme dans l'image de référence
  const fleetStatusTiles = [
    { 
      count: 22, 
      label: 'En service', 
      bgColor: 'bg-green-100', 
      icon: Check, 
      iconColor: 'text-green-600',
      iconBg: 'bg-green-200'
    },
    { 
      count: 2, 
      label: 'Hors service', 
      bgColor: 'bg-red-100', 
      icon: AlertTriangle, 
      iconColor: 'text-red-600',
      iconBg: 'bg-red-200'
    },
    { 
      count: 3, 
      label: 'En maintenance', 
      bgColor: 'bg-orange-100', 
      icon: Clock, 
      iconColor: 'text-orange-600',
      iconBg: 'bg-orange-200'
    },
    { 
      count: 5, 
      label: 'À inspecter', 
      bgColor: 'bg-blue-100', 
      icon: Calendar, 
      iconColor: 'text-blue-600',
      iconBg: 'bg-blue-200'
    },
  ];

  // Entretiens à venir avec icônes
  const upcomingMaintenance = [
    { vehicle: 'Renault Clio', plate: 'AB-123-CD', type: 'Vidange', dueDate: "Aujourd'hui", priority: 'urgent', icon: Clock, iconBg: 'bg-red-100', iconColor: 'text-red-600' },
    { vehicle: 'Peugeot 308', plate: 'EF-456-GH', type: 'Révision complète', dueDate: 'Demain', priority: 'high', icon: Wrench, iconBg: 'bg-orange-100', iconColor: 'text-orange-600' },
    { vehicle: 'Citroën C3', plate: 'IJ-789-KL', type: 'Contrôle technique', dueDate: 'Dans 3 jours', priority: 'medium', icon: Calendar, iconBg: 'bg-blue-100', iconColor: 'text-blue-600' },
  ];

  // Alertes prioritaires avec icônes
  const priorityAlerts = [
    { vehicle: 'Ford Focus', plate: 'MN-012-OP', issue: 'Défaillance système de freinage', action: 'Traiter', priority: 'critical', icon: AlertTriangle, iconBg: 'bg-red-100', iconColor: 'text-red-600' },
    { vehicle: 'Volkswagen Golf', plate: 'QR-345-ST', issue: 'Niveau huile critique', action: 'Traiter', priority: 'high', icon: AlertTriangle, iconBg: 'bg-orange-100', iconColor: 'text-orange-600' },
    { vehicle: 'Toyota Yaris', plate: 'UV-678-WX', issue: 'Contrôle technique expiré', action: 'Traiter', priority: 'high', icon: AlertTriangle, iconBg: 'bg-red-100', iconColor: 'text-red-600' },
  ];

  const stats = [
    { title: 'Véhicules actifs', value: '24', icon: Car, color: 'text-blue-600' },
    { title: 'Conducteurs', value: '18', icon: Users, color: 'text-green-600' },
    { title: 'Alertes', value: '3', icon: AlertTriangle, color: 'text-red-600' },
    { title: 'Disponibilité', value: '87%', icon: TrendingUp, color: 'text-purple-600' },
  ];

  const monthlyExpenses = [
    { month: 'Jan', amount: 2100 },
    { month: 'Fev', amount: 2450 },
    { month: 'Mar', amount: 2800 },
    { month: 'Avr', amount: 2300 },
    { month: 'Mai', amount: 2650 },
    { month: 'Jun', amount: 2450 },
  ];

  const lastEvents = [
    { type: 'Ajout', message: 'Véhicule Renault Clio ajouté', time: '2h' },
    { type: 'Alerte', message: 'CT expiré pour AB-123-CD', time: '4h' },
    { type: 'Entretien', message: 'Révision EF-456-GH terminée', time: '1j' },
    { type: 'Facture', message: 'Facture garage Martin ajoutée', time: '2j' },
    { type: 'Mise à jour', message: 'Kilométrage mis à jour IJ-789-KL', time: '3j' },
  ];

  const urgentMaintenance = [
    { vehicle: 'AB-123-CD', type: 'CT', dueDate: '5 jours', priority: 'high' },
    { vehicle: 'MN-456-OP', type: 'Entretien', dueDate: '12 jours', priority: 'medium' },
    { vehicle: 'QR-789-ST', type: 'CT', dueDate: '18 jours', priority: 'low' },
  ];

  const additionalStats = [
    { title: 'Taux disponibilité', value: '87%', icon: TrendingUp, color: 'text-emerald-600' },
    { title: 'À surveiller', value: '5', icon: AlertTriangle, color: 'text-red-500' },
    { title: 'Entretien moyen', value: '€340', icon: Wrench, color: 'text-orange-600' },
  ];

  return (
    <div className="flex-1 p-6 bg-gray-50">
      <div className="max-w-7xl mx-auto">
        <h1 className="text-3xl font-bold text-gray-900 mb-8">État de la flotte</h1>
        
        {/* 1. Zone haute - Tuiles résumé avec icônes et barres de progression */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          {summaryStats.map((stat) => {
            const Icon = stat.icon;
            return (
              <Card key={stat.title} className="hover:shadow-lg transition-shadow">
                <CardContent className="p-6">
                  <div className="flex items-start gap-4">
                    <div className="p-2 rounded-lg bg-gray-100">
                      <Icon className="w-6 h-6 text-gray-600" />
                    </div>
                    <div className="flex-1">
                      <div className="flex items-center justify-between mb-2">
                        <div>
                          <h4 className="text-lg font-semibold text-gray-900">{stat.title}</h4>
                          <p className="text-sm text-gray-600">{stat.subtitle}</p>
                        </div>
                        <span className="text-3xl font-bold text-gray-900">{stat.value}</span>
                      </div>
                      <div className="space-y-2">
                        <p className={`text-sm font-medium ${stat.textColor}`}>{stat.progressText}</p>
                        <Progress value={stat.progressValue} className="h-2" />
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            );
          })}
        </div>

        {/* 2. Statut de la flotte - Tuiles carrées comme dans l'image de référence */}
        <div className="mb-8">
          <Card>
            <CardHeader>
              <CardTitle className="text-2xl">Statut de la flotte</CardTitle>
              <p className="text-sm text-gray-600">Vue d'ensemble des véhicules</p>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
                {fleetStatusTiles.map((tile, index) => {
                  const Icon = tile.icon;
                  return (
                    <div key={index} className={`${tile.bgColor} rounded-lg p-6 text-center`}>
                      <div className="flex justify-start mb-4">
                        <div className={`p-2 rounded-full ${tile.iconBg}`}>
                          <Icon className={`w-5 h-5 ${tile.iconColor}`} />
                        </div>
                      </div>
                      <div className="text-4xl font-bold text-gray-900 mb-2">{tile.count}</div>
                      <div className="text-sm text-gray-700 font-medium">{tile.label}</div>
                    </div>
                  );
                })}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* 3. Entretiens à venir et 4. Alertes prioritaires avec icônes */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          {/* Entretiens à venir */}
          <Card>
            <CardHeader>
              <CardTitle className="text-2xl">Entretiens à venir</CardTitle>
              <p className="text-sm text-gray-600">Planning des 7 prochains jours</p>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {upcomingMaintenance.map((item, index) => (
                  <div key={index} className="flex items-center justify-between p-3 border rounded-lg">
                    <div className="flex items-center gap-3">
                      <div className={`p-2 rounded-full ${item.iconBg}`}>
                        <item.icon className={`w-4 h-4 ${item.iconColor}`} />
                      </div>
                      <div>
                        <p className="font-medium text-sm">{item.vehicle}</p>
                        <p className="text-xs text-gray-600">{item.plate} • {item.type}</p>
                      </div>
                    </div>
                    <div className="text-right">
                      <span className={`text-sm font-medium ${
                        item.priority === 'urgent' ? 'text-red-600' :
                        item.priority === 'high' ? 'text-orange-600' : 'text-blue-600'
                      }`}>
                        {item.dueDate}
                      </span>
                    </div>
                  </div>
                ))}
                <div className="pt-2">
                  <button className="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                    Voir tous les entretiens
                    <span>→</span>
                  </button>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Alertes prioritaires */}
          <Card>
            <CardHeader>
              <CardTitle className="text-2xl">Alertes prioritaires</CardTitle>
              <p className="text-sm text-gray-600">Nécessitant une action</p>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {priorityAlerts.map((alert, index) => (
                  <div key={index} className="flex items-center justify-between p-3 border rounded-lg">
                    <div className="flex items-center gap-3">
                      <div className={`p-2 rounded-full ${alert.iconBg}`}>
                        <alert.icon className={`w-4 h-4 ${alert.iconColor}`} />
                      </div>
                      <div>
                        <p className="font-medium text-sm">{alert.vehicle}</p>
                        <p className="text-xs text-gray-600">{alert.plate} • {alert.issue}</p>
                      </div>
                    </div>
                    <button className={`px-3 py-1 rounded text-xs font-medium ${
                      alert.priority === 'critical' 
                        ? 'bg-red-600 text-white hover:bg-red-700' 
                        : 'bg-orange-600 text-white hover:bg-orange-700'
                    }`}>
                      {alert.action}
                    </button>
                  </div>
                ))}
                <div className="pt-2">
                  <button className="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                    Voir toutes les alertes
                    <span>→</span>
                  </button>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Charts and Analytics Section - keeping existing content unchanged */}
        <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
          {/* Monthly Expenses Chart */}
          <Card className="lg:col-span-2">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Activity className="w-5 h-5" />
                Activité de la flotte
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="h-64 flex items-end justify-between space-x-2">
                {monthlyExpenses.map((expense, index) => (
                  <div key={index} className="flex flex-col items-center flex-1">
                    <div 
                      className="bg-blue-500 w-full rounded-t"
                      style={{ height: `${(expense.amount / 3000) * 200}px` }}
                    ></div>
                    <p className="text-xs text-gray-600 mt-2">{expense.month}</p>
                    <p className="text-xs font-medium">{expense.amount}</p>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Status Distribution */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Car className="w-5 h-5" />
                Répartition des statuts
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <div className="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span className="text-sm">En service</span>
                  </div>
                  <span className="text-sm font-medium">18</span>
                </div>
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <div className="w-3 h-3 bg-orange-500 rounded-full"></div>
                    <span className="text-sm">Maintenance</span>
                  </div>
                  <span className="text-sm font-medium">4</span>
                </div>
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <div className="w-3 h-3 bg-red-500 rounded-full"></div>
                    <span className="text-sm">Hors service</span>
                  </div>
                  <span className="text-sm font-medium">2</span>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Additional Analytics - keeping existing content unchanged */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          {/* Urgent Maintenance */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Clock className="w-5 h-5" />
                Entretiens urgents
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {urgentMaintenance.map((item, index) => (
                  <div key={index} className="flex items-center justify-between p-3 border rounded-lg">
                    <div>
                      <p className="font-medium text-sm">{item.vehicle}</p>
                      <p className="text-xs text-gray-600">{item.type}</p>
                    </div>
                    <div className="text-right">
                      <p className="text-xs text-gray-600">Dans</p>
                      <span className={`text-sm font-medium ${
                        item.priority === 'high' ? 'text-red-600' :
                        item.priority === 'medium' ? 'text-orange-600' : 'text-green-600'
                      }`}>
                        {item.dueDate}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Last Events */}
          <Card className="lg:col-span-2">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Clock className="w-5 h-5" />
                Derniers événements
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {lastEvents.map((event, index) => (
                  <div key={index} className="flex items-start gap-3 p-2 border-l-2 border-blue-200">
                    <div className={`w-2 h-2 rounded-full mt-2 ${
                      event.type === 'Alerte' ? 'bg-red-500' :
                      event.type === 'Ajout' ? 'bg-green-500' :
                      event.type === 'Entretien' ? 'bg-orange-500' :
                      'bg-blue-500'
                    }`}></div>
                    <div className="flex-1">
                      <p className="text-sm font-medium">{event.type}</p>
                      <p className="text-xs text-gray-600">{event.message}</p>
                      <p className="text-xs text-gray-400">Il y a {event.time}</p>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Original Content - Vehicles and Alerts - keeping existing content unchanged */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <Card>
            <CardHeader>
              <CardTitle>Véhicules récents</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {[
                  { name: 'Renault Clio', plate: 'AB-123-CD', status: 'En service', lastMaintenance: '15/03/2024' },
                  { name: 'Peugeot 308', plate: 'EF-456-GH', status: 'Maintenance', lastMaintenance: '10/03/2024' },
                  { name: 'Citroën C3', plate: 'IJ-789-KL', status: 'En service', lastMaintenance: '22/02/2024' },
                ].map((vehicle, index) => (
                  <div key={index} className="flex items-center justify-between p-3 border rounded-lg">
                    <div>
                      <p className="font-medium">{vehicle.name}</p>
                      <p className="text-sm text-gray-600">{vehicle.plate}</p>
                      <p className="text-xs text-gray-500">Dernier entretien: {vehicle.lastMaintenance}</p>
                    </div>
                    <div className="flex flex-col items-end gap-1">
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                        vehicle.status === 'En service' 
                          ? 'bg-green-100 text-green-800' 
                          : 'bg-orange-100 text-orange-800'
                      }`}>
                        {vehicle.status}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Alertes et notifications</CardTitle>
            </CardHeader>
            <CardContent>
              <Tabs defaultValue="all" className="w-full">
                <TabsList className="grid w-full grid-cols-4">
                  <TabsTrigger value="all">Toutes</TabsTrigger>
                  <TabsTrigger value="urgent">Urgent</TabsTrigger>
                  <TabsTrigger value="ct">CT</TabsTrigger>
                  <TabsTrigger value="admin">Admin</TabsTrigger>
                </TabsList>
                <TabsContent value="all" className="space-y-4 mt-4">
                  {[
                    { message: 'Contrôle technique à prévoir pour AB-123-CD', type: 'warning', category: 'CT' },
                    { message: 'Assurance expire dans 30 jours pour EF-456-GH', type: 'alert', category: 'Admin' },
                    { message: 'Maintenance programmée terminée pour IJ-789-KL', type: 'success', category: 'Entretien' },
                    { message: 'Kilométrage élevé détecté sur MN-456-OP', type: 'warning', category: 'Surveillance' },
                  ].map((alert, index) => (
                    <div key={index} className={`p-3 rounded-lg border-l-4 ${
                      alert.type === 'warning' ? 'border-yellow-400 bg-yellow-50' :
                      alert.type === 'alert' ? 'border-red-400 bg-red-50' :
                      'border-green-400 bg-green-50'
                    }`}>
                      <div className="flex items-center justify-between">
                        <p className="text-sm">{alert.message}</p>
                        <span className="text-xs bg-gray-200 px-2 py-1 rounded">{alert.category}</span>
                      </div>
                    </div>
                  ))}
                </TabsContent>
                <TabsContent value="urgent" className="space-y-4 mt-4">
                  <div className="p-3 rounded-lg border-l-4 border-red-400 bg-red-50">
                    <p className="text-sm">Contrôle technique expiré pour AB-123-CD</p>
                  </div>
                </TabsContent>
                <TabsContent value="ct" className="space-y-4 mt-4">
                  <div className="p-3 rounded-lg border-l-4 border-yellow-400 bg-yellow-50">
                    <p className="text-sm">Contrôle technique à prévoir pour AB-123-CD</p>
                  </div>
                </TabsContent>
                <TabsContent value="admin" className="space-y-4 mt-4">
                  <div className="p-3 rounded-lg border-l-4 border-red-400 bg-red-50">
                    <p className="text-sm">Assurance expire dans 30 jours pour EF-456-GH</p>
                  </div>
                </TabsContent>
              </Tabs>
            </CardContent>
          </Card>
        </div>

        {/* Additional Stats - Les trois tuiles ajoutées en bas - keeping existing content unchanged */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {additionalStats.map((stat) => {
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
      </div>
    </div>
  );
};

export default FleetStatusDashboard;
