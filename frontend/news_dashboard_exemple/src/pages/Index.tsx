
import React, { useState, useEffect } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import Sidebar from '@/components/Sidebar';
import Header from '@/components/Header';
import Dashboard from '@/components/Dashboard';
import FleetStatusDashboard from '@/components/FleetStatusDashboard';
import FinancialStatusDashboard from '@/components/FinancialStatusDashboard';
import VehicleForm from '@/components/VehicleForm';
import VehiclesPage from '@/components/VehiclesPage';
import VehicleSalesPage from '@/components/VehicleSalesPage';
import NotificationsPage from '@/components/NotificationsPage';
import SettingsPage from '@/components/SettingsPage';
import UserManagementPage from '@/components/UserManagementPage';
import RoleManagementPage from '@/components/RoleManagementPage';
import TransactionsPage from '@/components/TransactionsPage';
import TrouverAssurancePage from '@/components/TrouverAssurancePage';
import TrouverGaragePage from '@/components/TrouverGaragePage';
import TrouverGaragePageNew from '@/components/TrouverGaragePageNew';
import TrouverCentreCTPage from '@/components/TrouverCentreCTPage';
import ReservationsPage from '@/components/ReservationsPage';
import CTReservationsPage from '@/components/CTReservationsPage';
import MesProcesVerbauxPage from '@/components/MesProcesVerbauxPage';
import MesFacturesPage from '@/components/MesFacturesPage';

const Index = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const [activeView, setActiveView] = useState('fleet-status');
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  // Mapping des routes URL vers les vues
  const routeToView: { [key: string]: string } = {
    '/': 'fleet-status',
    '/fleet-status': 'fleet-status',
    '/financial-status': 'financial-status',
    '/trouver-garage': 'find-garage',
    '/trouver-centre-ct': 'find-ct',
    '/trouver-assurance': 'find-insurance',
    '/vehicules': 'vehicles',
    '/vehicules/liste': 'vehicles-list',
    '/vehicules/maintenance': 'vehicles-maintenance',
    '/vehicules/historique': 'vehicles-history',
    '/vehicules/achats-reventes': 'vehicles-sales',
    '/vehicules/statistiques': 'vehicles-statistics',
    '/mes-reservations': 'reservations',
    '/mes-reservations-ct': 'ct-reservations',
    '/mes-factures': 'invoices',
    '/mes-proces-verbaux': 'process-reports',
    '/utilisateurs': 'users-list',
    '/utilisateurs/roles': 'users-roles',
    '/notifications': 'notifications',
    '/parametres': 'settings'
  };

  // Mapping des vues vers les routes URL
  const viewToRoute: { [key: string]: string } = {
    'fleet-status': '/fleet-status',
    'financial-status': '/financial-status',
    'find-garage': '/trouver-garage',
    'find-ct': '/trouver-centre-ct',
    'find-insurance': '/trouver-assurance',
    'vehicles': '/vehicules',
    'vehicles-list': '/vehicules/liste',
    'vehicles-maintenance': '/vehicules/maintenance',
    'vehicles-history': '/vehicules/historique',
    'vehicles-sales': '/vehicules/achats-reventes',
    'vehicles-statistics': '/vehicules/statistiques',
    'reservations': '/mes-reservations',
    'ct-reservations': '/mes-reservations-ct',
    'invoices': '/mes-factures',
    'process-reports': '/mes-proces-verbaux',
    'users-list': '/utilisateurs',
    'users-roles': '/utilisateurs/roles',
    'notifications': '/notifications',
    'settings': '/parametres'
  };

  // Synchroniser la vue avec l'URL au chargement
  useEffect(() => {
    const currentView = routeToView[location.pathname] || 'fleet-status';
    setActiveView(currentView);
  }, [location.pathname]);

  // Fonction pour changer de vue et mettre à jour l'URL
  const handleViewChange = (newView: string) => {
    setActiveView(newView);
    const newRoute = viewToRoute[newView] || '/';
    navigate(newRoute, { replace: true });
  };

  const renderContent = () => {
    switch (activeView) {
      case 'fleet-status':
        return <FleetStatusDashboard />;
      case 'financial-status':
        return <FinancialStatusDashboard />;
      case 'vehicles-list':
        return <VehiclesPage />;
      case 'vehicles-maintenance':
        return <div className="flex-1 p-3 sm:p-6 bg-gray-50"><h1 className="text-2xl sm:text-3xl font-bold text-gray-900">Maintenance</h1><p className="text-gray-600 mt-4">Section maintenance des véhicules en développement...</p></div>;
      case 'vehicles-history':
        return <div className="flex-1 p-3 sm:p-6 bg-gray-50"><h1 className="text-2xl sm:text-3xl font-bold text-gray-900">Historique</h1><p className="text-gray-600 mt-4">Historique des véhicules en développement...</p></div>;
      case 'vehicles-sales':
        return <VehicleSalesPage />;
      case 'vehicles-statistics':
        return <div className="flex-1 p-3 sm:p-6 bg-gray-50"><h1 className="text-2xl sm:text-3xl font-bold text-gray-900">Statistiques</h1><p className="text-gray-600 mt-4">Statistiques des véhicules en développement...</p></div>;
      case 'vehicles':
        return <VehiclesPage />;
      case 'find-garage':
        return <TrouverGaragePageNew />;
      case 'find-ct':
        return <TrouverCentreCTPage />;
      case 'find-insurance':
        return <TrouverAssurancePage />;
      case 'reservations':
        return <ReservationsPage />;
      case 'ct-reservations':
        return <CTReservationsPage />;
      case 'invoices':
        return <MesFacturesPage />;
      case 'process-reports':
        return <MesProcesVerbauxPage />;
      case 'users-list':
        return <UserManagementPage />;
      case 'users-roles':
        return <RoleManagementPage />;
      case 'notifications':
        return <NotificationsPage />;
      case 'settings':
        return <SettingsPage />;
      default:
        return <FleetStatusDashboard />;
    }
  };

  const handleMenuClick = () => {
    setIsMobileMenuOpen(true);
  };

  const handleMenuClose = () => {
    setIsMobileMenuOpen(false);
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Sidebar 
        activeItem={activeView} 
        onItemClick={handleViewChange}
        isOpen={isMobileMenuOpen}
        onClose={handleMenuClose}
      />
      {/* Main content with left padding to account for fixed sidebar on desktop */}
      <div className="md:pl-64 flex flex-col min-h-screen">
        {!activeView.includes('add') && <Header onMenuClick={handleMenuClick} />}
        <main className="flex-1 overflow-auto">
          {renderContent()}
        </main>
      </div>
    </div>
  );
};

export default Index;
