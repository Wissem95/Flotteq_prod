import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Bell, AlertTriangle, Calendar, Car, FileText, Settings, TrendingUp, Clock, CheckCircle } from 'lucide-react';

const NotificationsPage: React.FC = () => {
  const [filter, setFilter] = useState('all');

  const notifications = [
    {
      id: 1,
      type: 'ct',
      priority: 'high',
      title: 'Contrôle technique expiré',
      message: 'Le CT du véhicule AB-123-CD a expiré le 15/03/2024',
      vehicle: 'AB-123-CD',
      date: '2024-03-16',
      status: 'unread',
      category: 'Urgent'
    },
    {
      id: 2,
      type: 'maintenance',
      priority: 'medium',
      title: 'Révision programmée',
      message: 'Révision des 20 000 km prévue pour EF-456-GH',
      vehicle: 'EF-456-GH',
      date: '2024-03-15',
      status: 'read',
      category: 'Entretien'
    },
    {
      id: 3,
      type: 'admin',
      priority: 'medium',
      title: 'Assurance à renouveler',
      message: 'L\'assurance de IJ-789-KL expire dans 30 jours',
      vehicle: 'IJ-789-KL',
      date: '2024-03-14',
      status: 'unread',
      category: 'Administratif'
    },
    {
      id: 4,
      type: 'invoice',
      priority: 'low',
      title: 'Facture en attente',
      message: 'Facture garage Martin non associée à un véhicule',
      vehicle: null,
      date: '2024-03-13',
      status: 'read',
      category: 'Facture'
    },
    {
      id: 5,
      type: 'mileage',
      priority: 'medium',
      title: 'Kilométrage élevé',
      message: 'Le véhicule MN-456-OP a atteint 152 000 km (limite: 150 000 km)',
      vehicle: 'MN-456-OP',
      date: '2024-03-12',
      status: 'unread',
      category: 'Surveillance'
    },
    {
      id: 6,
      type: 'mileage',
      priority: 'medium',
      title: 'Kilométrage élevé',
      message: 'Le véhicule QR-789-ST a atteint 165 000 km (limite: 150 000 km)',
      vehicle: 'QR-789-ST',
      date: '2024-03-11',
      status: 'unread',
      category: 'Surveillance'
    }
  ];

  const alertsHistory = [
    {
      id: 1,
      title: 'CT validé',
      message: 'Contrôle technique de AB-123-CD validé avec succès',
      date: '2024-03-10',
      action: 'Résolu'
    },
    {
      id: 2,
      title: 'Entretien terminé',
      message: 'Révision de EF-456-GH terminée par Garage Central',
      date: '2024-03-08',
      action: 'Terminé'
    }
  ];

  const filteredNotifications = filter === 'all' 
    ? notifications 
    : notifications.filter(n => n.type === filter || n.priority === filter || n.status === filter);

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'high': return 'border-red-500 bg-red-50';
      case 'medium': return 'border-orange-500 bg-orange-50';
      case 'low': return 'border-green-500 bg-green-50';
      default: return 'border-gray-200 bg-white';
    }
  };

  const getPriorityBadge = (priority: string) => {
    switch (priority) {
      case 'high': return 'bg-red-100 text-red-800';
      case 'medium': return 'bg-orange-100 text-orange-800';
      case 'low': return 'bg-green-100 text-green-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getTypeIcon = (type: string) => {
    switch (type) {
      case 'ct': return Calendar;
      case 'maintenance': return Settings;
      case 'admin': return FileText;
      case 'invoice': return FileText;
      case 'mileage': return TrendingUp;
      default: return Bell;
    }
  };

  return (
    <div className="flex-1 p-6 bg-gray-50">
      <div className="max-w-7xl mx-auto">
        <div className="flex items-center justify-between mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Notifications</h1>
          <div className="flex items-center gap-4">
            <Button variant="outline">
              <TrendingUp className="w-4 h-4 mr-2" />
              Exporter alertes
            </Button>
            <Button>
              Marquer tout comme lu
            </Button>
          </div>
        </div>

        {/* Summary Cards */}
        <div className="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Alertes urgentes</p>
                  <p className="text-2xl font-bold text-red-600">1</p>
                </div>
                <AlertTriangle className="w-8 h-8 text-red-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">CT à prévoir</p>
                  <p className="text-2xl font-bold text-orange-600">1</p>
                </div>
                <Calendar className="w-8 h-8 text-orange-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Entretiens</p>
                  <p className="text-2xl font-bold text-blue-600">1</p>
                </div>
                <Settings className="w-8 h-8 text-blue-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Kilométrage élevé</p>
                  <p className="text-2xl font-bold text-purple-600">2</p>
                </div>
                <TrendingUp className="w-8 h-8 text-purple-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Non lues</p>
                  <p className="text-2xl font-bold text-gray-600">4</p>
                </div>
                <Bell className="w-8 h-8 text-gray-600" />
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Filters and Notifications */}
        <Card>
          <CardHeader>
            <CardTitle>Centre de notifications</CardTitle>
          </CardHeader>
          <CardContent>
            <Tabs defaultValue="all" className="w-full">
              <TabsList className="grid w-full grid-cols-8">
                <TabsTrigger value="all">Toutes</TabsTrigger>
                <TabsTrigger value="high">Urgentes</TabsTrigger>
                <TabsTrigger value="ct">CT</TabsTrigger>
                <TabsTrigger value="maintenance">Entretien</TabsTrigger>
                <TabsTrigger value="mileage">Kilométrage</TabsTrigger>
                <TabsTrigger value="admin">Admin</TabsTrigger>
                <TabsTrigger value="unread">Non lues</TabsTrigger>
                <TabsTrigger value="history">Historique</TabsTrigger>
              </TabsList>

              <TabsContent value="all" className="mt-6">
                <div className="space-y-4">
                  {filteredNotifications.map((notification) => {
                    const Icon = getTypeIcon(notification.type);
                    return (
                      <Card key={notification.id} className={`border-l-4 ${getPriorityColor(notification.priority)} ${notification.status === 'unread' ? 'bg-blue-50' : ''}`}>
                        <CardContent className="p-4">
                          <div className="flex items-start justify-between">
                            <div className="flex items-start gap-3">
                              <Icon className="w-5 h-5 mt-1 text-gray-600" />
                              <div className="flex-1">
                                <div className="flex items-center gap-2 mb-1">
                                  <h3 className="font-medium text-gray-900">{notification.title}</h3>
                                  <Badge className={getPriorityBadge(notification.priority)}>
                                    {notification.priority === 'high' ? 'Urgent' : 
                                     notification.priority === 'medium' ? 'Moyen' : 'Faible'}
                                  </Badge>
                                  <Badge variant="secondary">{notification.category}</Badge>
                                  {notification.status === 'unread' && (
                                    <Badge variant="destructive">Nouveau</Badge>
                                  )}
                                </div>
                                <p className="text-sm text-gray-600 mb-2">{notification.message}</p>
                                <div className="flex items-center gap-4 text-xs text-gray-500">
                                  {notification.vehicle && (
                                    <span className="flex items-center gap-1">
                                      <Car className="w-3 h-3" />
                                      {notification.vehicle}
                                    </span>
                                  )}
                                  <span className="flex items-center gap-1">
                                    <Clock className="w-3 h-3" />
                                    {new Date(notification.date).toLocaleDateString('fr-FR')}
                                  </span>
                                </div>
                              </div>
                            </div>
                            <div className="flex gap-2">
                              {notification.type === 'mileage' && (
                                <Button size="sm" variant="outline">
                                  Programmer vente
                                </Button>
                              )}
                              <Button size="sm" variant="outline">
                                Traiter
                              </Button>
                            </div>
                          </div>
                        </CardContent>
                      </Card>
                    );
                  })}
                </div>
              </TabsContent>

              <TabsContent value="mileage" className="mt-6">
                <div className="space-y-4">
                  <p className="text-sm text-gray-600 mb-4">
                    Véhicules ayant dépassé la limite de kilométrage critique configurée dans les paramètres.
                  </p>
                  {notifications.filter(n => n.type === 'mileage').map((notification) => {
                    const Icon = getTypeIcon(notification.type);
                    return (
                      <Card key={notification.id} className="border-l-4 border-purple-500 bg-purple-50">
                        <CardContent className="p-4">
                          <div className="flex items-start justify-between">
                            <div className="flex items-start gap-3">
                              <Icon className="w-5 h-5 mt-1 text-purple-600" />
                              <div className="flex-1">
                                <h3 className="font-medium text-gray-900 mb-1">{notification.title}</h3>
                                <p className="text-sm text-gray-600 mb-2">{notification.message}</p>
                                <p className="text-xs text-gray-500">
                                  Recommandation : Envisager la vente de ce véhicule
                                </p>
                              </div>
                            </div>
                            <div className="flex gap-2">
                              <Button size="sm" variant="outline">
                                Programmer vente
                              </Button>
                              <Button size="sm" variant="outline">
                                Traiter
                              </Button>
                            </div>
                          </div>
                        </CardContent>
                      </Card>
                    );
                  })}
                </div>
              </TabsContent>

              <TabsContent value="high" className="mt-6">
                <div className="space-y-4">
                  {notifications.filter(n => n.priority === 'high').map((notification) => {
                    const Icon = getTypeIcon(notification.type);
                    return (
                      <Card key={notification.id} className="border-l-4 border-red-500 bg-red-50">
                        <CardContent className="p-4">
                          <div className="flex items-start gap-3">
                            <Icon className="w-5 h-5 mt-1 text-red-600" />
                            <div className="flex-1">
                              <h3 className="font-medium text-gray-900 mb-1">{notification.title}</h3>
                              <p className="text-sm text-gray-600">{notification.message}</p>
                            </div>
                          </div>
                        </CardContent>
                      </Card>
                    );
                  })}
                </div>
              </TabsContent>

              <TabsContent value="history" className="mt-6">
                <div className="space-y-4">
                  <h3 className="font-medium text-gray-900 mb-4">Alertes traitées</h3>
                  {alertsHistory.map((alert) => (
                    <Card key={alert.id} className="border-l-4 border-green-500 bg-green-50">
                      <CardContent className="p-4">
                        <div className="flex items-start gap-3">
                          <CheckCircle className="w-5 h-5 mt-1 text-green-600" />
                          <div className="flex-1">
                            <div className="flex items-center gap-2 mb-1">
                              <h3 className="font-medium text-gray-900">{alert.title}</h3>
                              <Badge className="bg-green-100 text-green-800">{alert.action}</Badge>
                            </div>
                            <p className="text-sm text-gray-600 mb-2">{alert.message}</p>
                            <p className="text-xs text-gray-500">{new Date(alert.date).toLocaleDateString('fr-FR')}</p>
                          </div>
                        </div>
                      </CardContent>
                    </Card>
                  ))}
                </div>
              </TabsContent>

              {/* Other tabs would follow similar patterns */}
              <TabsContent value="ct" className="mt-6">
                <div className="space-y-4">
                  {notifications.filter(n => n.type === 'ct').map((notification) => {
                    const Icon = getTypeIcon(notification.type);
                    return (
                      <Card key={notification.id} className={`border-l-4 ${getPriorityColor(notification.priority)}`}>
                        <CardContent className="p-4">
                          <div className="flex items-start gap-3">
                            <Icon className="w-5 h-5 mt-1 text-gray-600" />
                            <div className="flex-1">
                              <h3 className="font-medium text-gray-900 mb-1">{notification.title}</h3>
                              <p className="text-sm text-gray-600">{notification.message}</p>
                            </div>
                          </div>
                        </CardContent>
                      </Card>
                    );
                  })}
                </div>
              </TabsContent>
            </Tabs>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default NotificationsPage;
