
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ClipboardCheck, Calendar, Clock, MapPin, CheckCircle, AlertCircle } from 'lucide-react';

const CTReservationsPage: React.FC = () => {
  const rendezVous = [
    {
      id: 1,
      vehicule: 'Peugeot 308 - AB-123-CD',
      centre: 'Autosur Contrôle Technique',
      adresse: '15 Avenue des Champs, 75008 Paris',
      date: '2024-02-15',
      heure: '14:30',
      type: 'Contrôle technique',
      statut: 'confirmé'
    },
    {
      id: 2,
      vehicule: 'Renault Clio - EF-456-GH',
      centre: 'CT Express Paris Nord',
      adresse: '28 Rue de la République, 93200 Saint-Denis',
      date: '2024-02-20',
      heure: '10:00',
      type: 'Contre-visite',
      statut: 'en_attente'
    },
    {
      id: 3,
      vehicule: 'Citroën C3 - IJ-789-KL',
      centre: 'Contrôle Auto Plus',
      adresse: '5 Boulevard Voltaire, 75011 Paris',
      date: '2024-01-30',
      heure: '16:15',
      type: 'Contrôle technique',
      statut: 'terminé'
    }
  ];

  const getStatutColor = (statut: string) => {
    switch (statut) {
      case 'confirmé':
        return 'text-green-600 bg-green-100';
      case 'en_attente':
        return 'text-orange-600 bg-orange-100';
      case 'terminé':
        return 'text-blue-600 bg-blue-100';
      default:
        return 'text-gray-600 bg-gray-100';
    }
  };

  const getStatutIcon = (statut: string) => {
    switch (statut) {
      case 'confirmé':
        return <CheckCircle className="w-4 h-4" />;
      case 'en_attente':
        return <Clock className="w-4 h-4" />;
      case 'terminé':
        return <CheckCircle className="w-4 h-4" />;
      default:
        return <AlertCircle className="w-4 h-4" />;
    }
  };

  return (
    <div className="flex-1 p-3 sm:p-6 bg-gray-50">
      <div className="max-w-7xl mx-auto">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between mb-6 sm:mb-8 gap-4">
          <h1 className="text-2xl sm:text-3xl font-bold text-gray-900">Mes Rendez-vous Contrôle Technique</h1>
          <div className="text-sm text-gray-600">
            Gérez vos rendez-vous de contrôle technique
          </div>
        </div>

        {/* Statistiques */}
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
          <Card>
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Rendez-vous confirmés</p>
                  <p className="text-xl sm:text-2xl font-bold text-green-600">1</p>
                </div>
                <CheckCircle className="w-6 sm:w-8 h-6 sm:h-8 text-green-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">En attente</p>
                  <p className="text-xl sm:text-2xl font-bold text-orange-600">1</p>
                </div>
                <Clock className="w-6 sm:w-8 h-6 sm:h-8 text-orange-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-4 sm:p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Terminés</p>
                  <p className="text-xl sm:text-2xl font-bold text-blue-600">1</p>
                </div>
                <ClipboardCheck className="w-6 sm:w-8 h-6 sm:h-8 text-blue-600" />
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Liste des rendez-vous */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-lg sm:text-xl">
              <Calendar className="w-5 h-5 text-blue-600" />
              Mes rendez-vous
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {rendezVous.map((rdv) => (
                <div key={rdv.id} className="border rounded-lg p-4 hover:shadow-md transition-shadow">
                  <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div className="flex-1">
                      <div className="flex items-center gap-2 mb-2">
                        <h3 className="font-semibold text-gray-900">{rdv.vehicule}</h3>
                        <span className={`px-2 py-1 rounded-full text-xs font-medium flex items-center gap-1 ${getStatutColor(rdv.statut)}`}>
                          {getStatutIcon(rdv.statut)}
                          {rdv.statut.replace('_', ' ')}
                        </span>
                      </div>
                      <div className="space-y-1 text-sm text-gray-600">
                        <div className="flex items-center gap-2">
                          <ClipboardCheck className="w-4 h-4" />
                          <span>{rdv.type}</span>
                        </div>
                        <div className="flex items-center gap-2">
                          <MapPin className="w-4 h-4" />
                          <span>{rdv.centre} - {rdv.adresse}</span>
                        </div>
                        <div className="flex items-center gap-2">
                          <Calendar className="w-4 h-4" />
                          <span>{new Date(rdv.date).toLocaleDateString('fr-FR')} à {rdv.heure}</span>
                        </div>
                      </div>
                    </div>
                    <div className="flex gap-2">
                      {rdv.statut === 'confirmé' && (
                        <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                          Modifier
                        </button>
                      )}
                      {rdv.statut !== 'terminé' && (
                        <button className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                          Annuler
                        </button>
                      )}
                      {rdv.statut === 'terminé' && (
                        <button className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                          Rapport
                        </button>
                      )}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default CTReservationsPage;
