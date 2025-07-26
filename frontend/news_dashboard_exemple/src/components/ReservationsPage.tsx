
import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { CheckCircle, MapPin, Car, Wrench, Clock, Phone, Calendar, CreditCard, X, AlertTriangle } from 'lucide-react';
import { toast } from 'sonner';

interface Reservation {
  id: string;
  garage: {
    name: string;
    address: string;
    phone: string;
  };
  vehicleInfo: {
    brand: string;
    model: string;
    year: number;
    licensePlate?: string;
  };
  date: Date;
  timeSlot: string;
  selectedRepairs: string[];
  reservationFee: number;
  estimatedTotal: number;
  status: 'confirmed' | 'cancelled' | 'completed';
  canCancel: boolean;
}

const ReservationsPage: React.FC = () => {
  const [reservations, setReservations] = useState<Reservation[]>([]);
  const [showCancelDialog, setShowCancelDialog] = useState<string | null>(null);

  // Simuler la récupération des réservations (en réalité, cela viendrait d'une API/base de données)
  useEffect(() => {
    // Récupérer les réservations depuis le localStorage ou une API
    const savedReservations = localStorage.getItem('userReservations');
    if (savedReservations) {
      const parsed = JSON.parse(savedReservations);
      // Convertir les dates string en objets Date
      const reservationsWithDates = parsed.map((reservation: any) => ({
        ...reservation,
        date: new Date(reservation.date)
      }));
      setReservations(reservationsWithDates);
    }
  }, []);

  const handleCancelReservation = (reservationId: string) => {
    setReservations(prev => 
      prev.map(reservation => 
        reservation.id === reservationId 
          ? { ...reservation, status: 'cancelled' as const }
          : reservation
      )
    );
    
    // Mettre à jour le localStorage
    const updatedReservations = reservations.map(reservation => 
      reservation.id === reservationId 
        ? { ...reservation, status: 'cancelled' as const }
        : reservation
    );
    localStorage.setItem('userReservations', JSON.stringify(updatedReservations));
    
    setShowCancelDialog(null);
    toast.success('Réservation annulée avec succès');
    
    // Ajouter une notification
    const notifications = JSON.parse(localStorage.getItem('notifications') || '[]');
    notifications.unshift({
      id: Date.now().toString(),
      title: 'Réservation annulée',
      message: `Votre réservation chez ${reservations.find(r => r.id === reservationId)?.garage.name} a été annulée`,
      type: 'info',
      timestamp: new Date().toISOString(),
      read: false
    });
    localStorage.setItem('notifications', JSON.stringify(notifications));
  };

  const handleContactGarage = (garage: any) => {
    toast.success(`Appel en cours vers ${garage.name}...`);
    // Ajouter une notification
    const notifications = JSON.parse(localStorage.getItem('notifications') || '[]');
    notifications.unshift({
      id: Date.now().toString(),
      title: 'Contact garage',
      message: `Tentative de contact avec ${garage.name}`,
      type: 'info',
      timestamp: new Date().toISOString(),
      read: false
    });
    localStorage.setItem('notifications', JSON.stringify(notifications));
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'confirmed': return 'bg-green-100 text-green-800';
      case 'cancelled': return 'bg-red-100 text-red-800';
      case 'completed': return 'bg-blue-100 text-blue-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'confirmed': return 'Réservation confirmée';
      case 'cancelled': return 'Réservation annulée';
      case 'completed': return 'Réparation terminée';
      default: return 'Statut inconnu';
    }
  };

  if (reservations.length === 0) {
    return (
      <div className="flex-1 p-3 sm:p-6 bg-gray-50">
        <div className="max-w-4xl mx-auto">
          <h1 className="text-2xl sm:text-3xl font-bold text-gray-900 mb-6">Vos réservations</h1>
          
          <Card>
            <CardContent className="p-8 text-center">
              <Calendar className="w-12 h-12 text-gray-400 mx-auto mb-4" />
              <h2 className="text-xl font-semibold text-gray-900 mb-2">Aucune réservation</h2>
              <p className="text-gray-600 mb-4">
                Vous n'avez pas encore de réservation confirmée
              </p>
              <Button onClick={() => window.location.href = '/'}>
                Rechercher un garage
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    );
  }

  return (
    <div className="flex-1 p-3 sm:p-6 bg-gray-50">
      <div className="max-w-4xl mx-auto">
        <h1 className="text-2xl sm:text-3xl font-bold text-gray-900 mb-6">Vos réservations</h1>
        
        <div className="space-y-6">
          {reservations.map((reservation) => (
            <Card key={reservation.id} className="shadow-sm">
              <CardHeader>
                <div className="flex justify-between items-start">
                  <CardTitle className="flex items-center gap-2">
                    <CheckCircle className="w-5 h-5 text-green-600" />
                    Réservation #{reservation.id.slice(-8)}
                  </CardTitle>
                  <Badge className={getStatusColor(reservation.status)}>
                    {getStatusText(reservation.status)}
                  </Badge>
                </div>
              </CardHeader>
              <CardContent className="space-y-6">
                {/* Garage */}
                <div>
                  <h3 className="font-medium mb-2 flex items-center gap-2">
                    <MapPin className="w-4 h-4 text-purple-600" />
                    Garage partenaire
                  </h3>
                  <p className="font-medium">{reservation.garage.name}</p>
                  <p className="text-sm text-gray-600">{reservation.garage.address}</p>
                  <p className="text-sm text-gray-600 flex items-center gap-1 mt-1">
                    <Phone className="w-3 h-3" />
                    {reservation.garage.phone}
                  </p>
                </div>

                {/* Véhicule */}
                <div>
                  <h3 className="font-medium mb-2 flex items-center gap-2">
                    <Car className="w-4 h-4 text-blue-600" />
                    Véhicule concerné
                  </h3>
                  <p>
                    {reservation.vehicleInfo.brand} {reservation.vehicleInfo.model} ({reservation.vehicleInfo.year})
                  </p>
                  {reservation.vehicleInfo.licensePlate && (
                    <Badge variant="secondary" className="mt-1 font-mono text-xs">
                      {reservation.vehicleInfo.licensePlate}
                    </Badge>
                  )}
                </div>

                {/* Date et heure */}
                <div>
                  <h3 className="font-medium mb-2 flex items-center gap-2">
                    <Clock className="w-4 h-4 text-green-600" />
                    Date et heure
                  </h3>
                  <p className="text-lg">
                    {reservation.date.toLocaleDateString('fr-FR', {
                      weekday: 'long',
                      year: 'numeric',
                      month: 'long',
                      day: 'numeric'
                    })} à {reservation.timeSlot}
                  </p>
                </div>

                {/* Réparations */}
                <div>
                  <h3 className="font-medium mb-2 flex items-center gap-2">
                    <Wrench className="w-4 h-4 text-orange-600" />
                    Réparations demandées
                  </h3>
                  <div className="flex flex-wrap gap-2">
                    {reservation.selectedRepairs.map((repair: string, index: number) => (
                      <Badge key={index} variant="outline" className="text-xs">
                        {repair}
                      </Badge>
                    ))}
                  </div>
                </div>

                {/* Montants */}
                <div className="border-t pt-4">
                  <h3 className="font-medium mb-2 flex items-center gap-2">
                    <CreditCard className="w-4 h-4 text-green-600" />
                    Détails financiers
                  </h3>
                  <div className="flex justify-between items-center mb-2">
                    <span className="text-sm">Frais de réservation payés</span>
                    <span className="font-medium text-green-600">{reservation.reservationFee}€ TTC</span>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-sm">Total estimé réparations</span>
                    <span className="text-sm text-gray-600">{reservation.estimatedTotal}€ (à confirmer)</span>
                  </div>
                </div>

                {/* Actions */}
                {reservation.status === 'confirmed' && (
                  <div className="flex gap-3 pt-4 border-t">
                    <Button 
                      onClick={() => handleContactGarage(reservation.garage)}
                      variant="outline" 
                      className="flex-1"
                    >
                      <Phone className="w-4 h-4 mr-2" />
                      Contacter le garage
                    </Button>
                    {reservation.canCancel && (
                      <Button 
                        onClick={() => setShowCancelDialog(reservation.id)}
                        variant="destructive"
                        className="flex-1"
                      >
                        <X className="w-4 h-4 mr-2" />
                        Annuler la réservation
                      </Button>
                    )}
                  </div>
                )}
              </CardContent>
            </Card>
          ))}
        </div>
      </div>

      {/* Dialog de confirmation d'annulation */}
      {showCancelDialog && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-lg max-w-md w-full p-6">
            <div className="flex items-center gap-3 mb-4">
              <AlertTriangle className="w-6 h-6 text-red-600" />
              <h2 className="text-lg font-semibold">Confirmer l'annulation</h2>
            </div>
            <p className="text-gray-600 mb-6">
              Êtes-vous sûr de vouloir annuler cette réservation ? Cette action est irréversible.
            </p>
            <div className="flex gap-3">
              <Button 
                onClick={() => setShowCancelDialog(null)}
                variant="outline" 
                className="flex-1"
              >
                Garder la réservation
              </Button>
              <Button 
                onClick={() => handleCancelReservation(showCancelDialog)}
                variant="destructive"
                className="flex-1"
              >
                Confirmer l'annulation
              </Button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ReservationsPage;
