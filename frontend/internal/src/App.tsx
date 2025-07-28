// App.tsx - Application principale pour l'interface d'administration FlotteQ

import React from "react";
import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";

// Layout
import InternalLayout from "./components/layout/InternalLayout";

// Pages d'authentification
import LoginPage from "./pages/auth/LoginPage";

// Pages principales
import DashboardOverview from "./pages/admin/DashboardOverview";
import PartnersOverview from "./pages/partners/PartnersOverview";
import PartnersMap from "./pages/partners/PartnersMap";
import SupportDashboard from "./pages/support/SupportDashboard";
import EmployeesOverview from "./pages/employees/EmployeesOverview";
import SubscriptionsOverview from "./pages/subscriptions/SubscriptionsOverview";
import PlansManagement from "./pages/subscriptions/PlansManagement";
import AnalyticsDashboard from "./pages/analytics/AnalyticsDashboard";
import SystemMonitoring from "./pages/tools/SystemMonitoring";
import GlobalSettings from "./pages/settings/GlobalSettings";
import RolesPermissions from "./pages/permissions/RolesPermissions";
import APIIntegrations from "./pages/tools/APIIntegrations";
import DemoDataGenerator from "./components/demo/DemoDataGenerator";

// Pages existantes (à conserver)
import AdminRoutes from "./pages/admin/AdminRoutes";

// Hook d'authentification
import { useInternalAuth } from "./hooks/useInternalAuth";

// Configuration TanStack Query
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});

// Composant de protection des routes
const ProtectedRoute: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { isAuthenticated, isLoading } = useInternalAuth();

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen bg-gray-50">
        <div className="text-center">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Chargement...</p>
        </div>
      </div>
    );
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  return <>{children}</>;
};

const App: React.FC = () => {
  return (
    <QueryClientProvider client={queryClient}>
      <TooltipProvider>
        <Toaster />
        <Sonner />
        <BrowserRouter future={{ v7_startTransition: true, v7_relativeSplatPath: true }}>
          <Routes>
            {/* Page de connexion (publique) */}
            <Route path="/login" element={<LoginPage />} />
            
            {/* Routes protégées avec layout */}
            <Route 
              path="/*" 
              element={
                <ProtectedRoute>
                  <InternalLayout>
                    <Routes>
                      {/* Redirection par défaut */}
                      <Route path="/" element={<Navigate to="/dashboard/overview" replace />} />
                      
                      {/* Dashboard */}
                      <Route path="/dashboard/overview" element={<DashboardOverview />} />
                      <Route path="/dashboard/tenants" element={
                        <div className="p-8 text-center">
                          <h2 className="text-xl font-semibold mb-4">Page Tenants</h2>
                          <p className="text-gray-600">Cette page sera développée prochainement</p>
                        </div>
                      } />
                      <Route path="/dashboard/alerts" element={
                        <div className="p-8 text-center">
                          <h2 className="text-xl font-semibold mb-4">Page Alertes Système</h2>
                          <p className="text-gray-600">Cette page sera développée prochainement</p>
                        </div>
                      } />
                      
                      {/* Support */}
                      <Route path="/support" element={<SupportDashboard />} />
                      
                      {/* Employés */}
                      <Route path="/employes" element={<EmployeesOverview />} />
                      
                      {/* Partenaires */}
                      <Route path="/partenaires/garages" element={<PartnersOverview />} />
                      <Route path="/partenaires/controle-technique" element={<PartnersOverview />} />
                      <Route path="/partenaires/assurances" element={<PartnersOverview />} />
                      <Route path="/partenaires/carte" element={<PartnersMap />} />
                      
                      {/* Abonnements */}
                      <Route path="/abonnements" element={<SubscriptionsOverview />} />
                      <Route path="/abonnements/plans" element={<PlansManagement />} />
                      
                      {/* Promotions */}
                      <Route path="/promotions" element={
                        <div className="p-8 text-center">
                          <h2 className="text-xl font-semibold mb-4">Offres & Promotions</h2>
                          <p className="text-gray-600">Codes promo et campagnes marketing</p>
                        </div>
                      } />
                      
                      {/* Finance */}
                      <Route path="/finance/revenus" element={
                        <div className="p-8 text-center">
                          <h2 className="text-xl font-semibold mb-4">Revenus Globaux</h2>
                          <p className="text-gray-600">Analyse financière de la plateforme</p>
                        </div>
                      } />
                      <Route path="/finance/commissions" element={
                        <div className="p-8 text-center">
                          <h2 className="text-xl font-semibold mb-4">Commissions Partenaires</h2>
                          <p className="text-gray-600">Gestion des commissions et paiements</p>
                        </div>
                      } />
                      <Route path="/finance/rapports" element={
                        <div className="p-8 text-center">
                          <h2 className="text-xl font-semibold mb-4">Rapports Financiers</h2>
                          <p className="text-gray-600">Rapports comptables et exports</p>
                        </div>
                      } />
                      
                      {/* Analytics */}
                      <Route path="/analytics/usage" element={<AnalyticsDashboard />} />
                      <Route path="/analytics/performance" element={<AnalyticsDashboard />} />
                      <Route path="/analytics/comportement" element={<AnalyticsDashboard />} />
                      
                      {/* Autres sections */}
                      <Route path="/paiements" element={
                        <div className="p-8 text-center">
                          <h2 className="text-xl font-semibold mb-4">Modes de Paiement</h2>
                          <p className="text-gray-600">Configuration des passerelles</p>
                        </div>
                      } />
                      <Route path="/parrainage" element={
                        <div className="p-8 text-center">
                          <h2 className="text-xl font-semibold mb-4">Programme de Parrainage</h2>
                          <p className="text-gray-600">Gestion des parrainages et récompenses</p>
                        </div>
                      } />
                      <Route path="/features-bonus" element={
                        <div className="p-8 text-center">
                          <h2 className="text-xl font-semibold mb-4">Fonctionnalités Bonus</h2>
                          <p className="text-gray-600">Features expérimentales et bêta</p>
                        </div>
                      } />
                      <Route path="/permissions" element={<RolesPermissions />} />
                      
                      {/* Outils */}
                      <Route path="/outils/api" element={<APIIntegrations />} />
                      <Route path="/outils/demo-data" element={<DemoDataGenerator />} />
                      <Route path="/outils/monitoring" element={<SystemMonitoring />} />
                      <Route path="/outils/logs" element={<SystemMonitoring />} />
                      
                      {/* Paramètres */}
                      <Route path="/parametres" element={<GlobalSettings />} />
                      <Route path="/profile" element={
                        <div className="p-8 text-center">
                          <h2 className="text-xl font-semibold mb-4">Mon Profil</h2>
                          <p className="text-gray-600">Informations personnelles</p>
                        </div>
                      } />
                      
                      {/* Routes admin existantes (compatibilité) */}
                      <Route path="/admin/*" element={<AdminRoutes />} />
                      
                      {/* Route 404 */}
                      <Route path="*" element={
                        <div className="p-8 text-center">
                          <h2 className="text-xl font-semibold mb-4">Page non trouvée</h2>
                          <p className="text-gray-600">La page demandée n'existe pas</p>
                        </div>
                      } />
                    </Routes>
                  </InternalLayout>
                </ProtectedRoute>
              } 
            />
          </Routes>
        </BrowserRouter>
      </TooltipProvider>
    </QueryClientProvider>
  );
};

export default App;

