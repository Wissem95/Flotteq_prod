
import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";

import Layout from "./components/layout/Layout";

// Pages publiques
import Login from "./pages/Login";
import VerifyAccount from "./pages/VerifyAccount";
import Logout from "./pages/Logout";
import ForgotPassword from "./pages/ForgotPassword";
import ResetPassword from "./pages/ResetPassword";
import GoogleCallback from "./pages/GoogleCallback";
import EmailEnterForgotPassword from "./pages/EmailEnterForgotPassword";
import RegisterSuccess from "./pages/RegisterSuccess";
import LoginSuccess from "./pages/LoginSuccess";
import CGU from "./pages/CGU";
import CGUPopup from "./pages/CGUPopup";

// Pages privées
import Dashboard from "./pages/Dashboard";
import VehiclesList from "./pages/VehiclesList";
import VehicleDetail from "./pages/VehicleDetail";
import AddVehicle from "./pages/AddVehicle";
import Notifications from "./pages/Notifications";
import Settings from "./pages/Settings";
import Profile from "./pages/Profile";
import Maintenances from "./pages/Maintenances";
import EditMaintenance from "./pages/EditMaintenance";
import AddMaintenance from "./pages/AddMaintenance";
import VehiclesHistory from "./pages/VehiclesHistory";
import Statistics from "./pages/statistics2";
import UserManagement from "./pages/UserManagement";
import CodeVerification from "./pages/Verification/CodeVerification";
import SaisieVerification from "./pages/Verification/SaisieVerification";
import TrouverGaragePage from "./pages/TrouverGaragePage";
import Transactions from "./pages/Transactions";
import FleetStatus from "./pages/FleetStatus";
import FinancialStatus from "./pages/FinancialStatus";

// Vérification de l'authentification
const isAuthenticated = () => {
  try {
    const token = localStorage.getItem("token");
    const user = localStorage.getItem("user");
    
    return !!(token && user);
  } catch (error) {
    console.error("Erreur vérification authentification:", error);
    return false;
  }
};

const PrivateRoute = ({ children }: { children: JSX.Element }) => {
  const authenticated = isAuthenticated();
  return authenticated ? children : <Navigate to="/register" replace />;
};

const queryClient = new QueryClient();

const App = () => (
  <QueryClientProvider client={queryClient}>
    <TooltipProvider>
      <Toaster />
      <Sonner />
      <BrowserRouter future={{ v7_startTransition: true, v7_relativeSplatPath: true }}>
        <Routes>
          {/* Racine → register */}
          <Route path="/" element={<Navigate to="/login" replace />} />
          <Route path="/verification" element={<CodeVerification />} />
          <Route path="/verification/saisie" element={<SaisieVerification />} />



          {/* Publiques */}
          <Route path="/login" element={<Login />} />
          <Route path="/VerifyAccount" element={<VerifyAccount />} />
          <Route path="/logout" element={<Logout />} />
          <Route path="/register-success" element={<RegisterSuccess />} />
          <Route path="/forgot-password" element={<ForgotPassword />} />
          <Route path="/reset-password" element={<ResetPassword />} />
          <Route path="/google-callback" element={<GoogleCallback />} />
          <Route path="/Enter-new-password" element={<EmailEnterForgotPassword />} />
          <Route path="/login-success" element={<LoginSuccess />} />
          <Route path="/cgu" element={<CGU />} />
          <Route path="/cgupopup" element={<Layout><CGUPopup /></Layout>} />

          {/* Privées */}
          <Route path="/dashboard" element={<Navigate to="/dashboard/fleet" replace />} />
          <Route path="/dashboard/fleet" element={<PrivateRoute><Layout><FleetStatus /></Layout></PrivateRoute>} />
          <Route path="/dashboard/financial" element={<PrivateRoute><Layout><FinancialStatus /></Layout></PrivateRoute>} />
          <Route path="/vehicles" element={<PrivateRoute><Layout><VehiclesList /></Layout></PrivateRoute>} />
          <Route path="/vehicles/add" element={<PrivateRoute><Layout><AddVehicle /></Layout></PrivateRoute>} />
          <Route path="/vehicle/:id" element={<PrivateRoute><Layout><VehicleDetail /></Layout></PrivateRoute>} />
          <Route path="/vehicles/history" element={<PrivateRoute><Layout><VehiclesHistory /></Layout></PrivateRoute>} />
          <Route path="/vehicles/maintenance" element={<PrivateRoute><Layout><Maintenances /></Layout></PrivateRoute>} />
          <Route path="/vehicles/maintenance/add" element={<PrivateRoute><Layout><AddMaintenance /></Layout></PrivateRoute>} />
          <Route path="/vehicles/maintenance/edit/:id" element={<PrivateRoute><Layout><EditMaintenance /></Layout></PrivateRoute>} />
          <Route path="/vehicles/stats" element={<PrivateRoute><Layout><Statistics /></Layout></PrivateRoute>} />
          <Route path="/trouver-garage" element={<PrivateRoute><Layout><TrouverGaragePage /></Layout></PrivateRoute>} />
          <Route path="/transactions" element={<PrivateRoute><Layout><Transactions /></Layout></PrivateRoute>} />
          <Route path="/notifications" element={<PrivateRoute><Layout><Notifications /></Layout></PrivateRoute>} />
          <Route path="/settings" element={<PrivateRoute><Layout><Settings /></Layout></PrivateRoute>} />
          <Route path="/profile" element={<PrivateRoute><Layout><Profile /></Layout></PrivateRoute>} />
          <Route path="/users" element={<PrivateRoute><Layout><UserManagement /></Layout></PrivateRoute>} />
          <Route path="/users/stats" element={<PrivateRoute><Layout><Statistics /></Layout></PrivateRoute>} />

          {/* Fallback vers register si non connecté, dashboard si connecté */}
          <Route path="*" element={
            isAuthenticated() ? 
            <Navigate to="/dashboard" replace /> : 
            <Navigate to="/login" replace />
          } />
        </Routes>
      </BrowserRouter>
    </TooltipProvider>
  </QueryClientProvider>
);

export default App;

