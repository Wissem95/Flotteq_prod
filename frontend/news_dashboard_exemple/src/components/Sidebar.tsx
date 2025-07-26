import React, { useState, useEffect } from 'react';
import { LayoutDashboard, Car, Bell, Settings, X, ChevronDown, ChevronRight, Activity, TrendingUp, List, Wrench, History, BarChart3, Users, FileText, Shield, UserCheck, MapPin, Calendar, TrendingDown, ClipboardCheck, Receipt } from 'lucide-react';
import { cn } from '@/lib/utils';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle } from '@/components/ui/drawer';
import { useCollapsibleState } from '@/hooks/useCollapsibleState';

interface SidebarProps {
  activeItem: string;
  onItemClick: (item: string) => void;
  isOpen?: boolean;
  onClose?: () => void;
}

const Sidebar: React.FC<SidebarProps> = ({ activeItem, onItemClick, isOpen = true, onClose }) => {
  const { isExpanded: isDashboardExpanded, toggle: toggleDashboard } = useCollapsibleState('dashboard', true);
  const { isExpanded: isUsersExpanded, toggle: toggleUsers } = useCollapsibleState('users', false);
  const { isExpanded: isVehiclesExpanded, toggle: toggleVehicles } = useCollapsibleState('vehicles', false);
  const { isExpanded: isSalesExpanded, toggle: toggleSales } = useCollapsibleState('sales', false);
  const { isExpanded: isGarageExpanded, toggle: toggleGarage } = useCollapsibleState('garage', false);
  const { isExpanded: isCTExpanded, toggle: toggleCT } = useCollapsibleState('ct', false);
  
  const [isMultiUserEnabled, setIsMultiUserEnabled] = useState(false);
  const [isUserRolesEnabled, setIsUserRolesEnabled] = useState(false);

  // Check localStorage for multi-user settings
  useEffect(() => {
    const multiUserSetting = localStorage.getItem('multiUser');
    const userRolesSetting = localStorage.getItem('userRoles');
    setIsMultiUserEnabled(multiUserSetting === 'true');
    setIsUserRolesEnabled(userRolesSetting === 'true');
  }, []);

  // Listen for storage changes to update sidebar in real-time
  useEffect(() => {
    const handleStorageChange = () => {
      const multiUserSetting = localStorage.getItem('multiUser');
      const userRolesSetting = localStorage.getItem('userRoles');
      setIsMultiUserEnabled(multiUserSetting === 'true');
      setIsUserRolesEnabled(userRolesSetting === 'true');
    };

    window.addEventListener('storage', handleStorageChange);
    
    // Also listen for custom event for same-page updates
    window.addEventListener('settingsUpdated', handleStorageChange);

    return () => {
      window.removeEventListener('storage', handleStorageChange);
      window.removeEventListener('settingsUpdated', handleStorageChange);
    };
  }, []);

  const menuItems = [
    { id: 'find-insurance', icon: Shield, label: 'Trouver une assurance' },
    { id: 'notifications', icon: Bell, label: 'Notifications' },
    { id: 'settings', icon: Settings, label: 'Paramètres' },
  ];

  const dashboardSubItems = [
    { id: 'fleet-status', icon: Activity, label: 'État de la flotte' },
    { id: 'financial-status', icon: TrendingUp, label: 'État financier' },
  ];

  const usersSubItems = [
    { id: 'users-list', icon: Users, label: 'Liste des utilisateurs' },
    ...(isUserRolesEnabled ? [{ id: 'users-roles', icon: Settings, label: 'Rôles' }] : [])
  ];

  const vehiclesSubItems = [
    { id: 'vehicles-list', icon: List, label: 'Liste des véhicules' },
    { id: 'vehicles-maintenance', icon: Wrench, label: 'Maintenance' },
    { id: 'vehicles-history', icon: History, label: 'Historique' },
    { id: 'vehicles-statistics', icon: BarChart3, label: 'Statistiques' },
  ];

  const garageSubItems = [
    { id: 'find-garage', icon: MapPin, label: 'Recherche de garage' },
    { id: 'reservations', icon: Calendar, label: 'Mes réservations' },
    { id: 'invoices', icon: Receipt, label: 'Mes factures' },
  ];

  const ctSubItems = [
    { id: 'find-ct', icon: ClipboardCheck, label: 'Recherche de centre CT' },
    { id: 'ct-reservations', icon: Calendar, label: 'Mes rendez-vous CT' },
    { id: 'process-reports', icon: FileText, label: 'Mes procès-verbaux' },
  ];

  const handleItemClick = (itemId: string) => {
    onItemClick(itemId);
    // Close drawer on mobile when item is clicked
    if (onClose) {
      onClose();
    }
  };

  const SidebarContent = () => (
    <div className="flotteq-sidebar w-full h-full text-white p-4 relative overflow-y-auto">
      <div className="flex items-center justify-center gap-3 mb-8">
        <h1 className="text-3xl font-bold">Flotteq</h1>
        {onClose && (
          <button onClick={onClose} className="md:hidden">
            <X className="w-5 h-5 cursor-pointer hover:opacity-70" />
          </button>
        )}
      </div>
      
      <nav className="space-y-2">
        {/* Dashboard parent menu with sub-items */}
        <div>
          <button
            onClick={toggleDashboard}
            className={cn(
              "w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200 hover:bg-white/10",
              (activeItem === 'fleet-status' || activeItem === 'financial-status') && "bg-white/20 shadow-md"
            )}
          >
            <div className="flex items-center space-x-3">
              <LayoutDashboard className="w-5 h-5" />
              <span className="text-sm font-medium">Dashboard</span>
            </div>
            {isDashboardExpanded ? (
              <ChevronDown className="w-4 h-4" />
            ) : (
              <ChevronRight className="w-4 h-4" />
            )}
          </button>
          
          {/* Dashboard sub-items */}
          {isDashboardExpanded && (
            <div className="ml-4 mt-2 space-y-1">
              {dashboardSubItems.map((item) => {
                const Icon = item.icon;
                return (
                  <button
                    key={item.id}
                    onClick={() => handleItemClick(item.id)}
                    className={cn(
                      "w-full flex items-center space-x-3 px-4 py-2 rounded-lg transition-all duration-200 hover:bg-white/10 text-sm",
                      activeItem === item.id && "bg-white/30 shadow-md"
                    )}
                  >
                    <Icon className="w-4 h-4" />
                    <span className="font-medium">{item.label}</span>
                  </button>
                );
              })}
            </div>
          )}
        </div>

        {/* Utilisateurs menu - after Dashboard and before Véhicules */}
        {isMultiUserEnabled && (
          <div>
            <button
              onClick={toggleUsers}
              className={cn(
                "w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200 hover:bg-white/10",
                (activeItem.startsWith('users-') || activeItem === 'users') && "bg-white/20 shadow-md"
              )}
            >
              <div className="flex items-center space-x-3">
                <Users className="w-5 h-5" />
                <span className="text-sm font-medium">Utilisateurs</span>
              </div>
              {isUsersExpanded ? (
                <ChevronDown className="w-4 h-4" />
              ) : (
                <ChevronRight className="w-4 h-4" />
              )}
            </button>
            
            {/* Utilisateurs sub-items */}
            {isUsersExpanded && (
              <div className="ml-4 mt-2 space-y-1">
                {usersSubItems.map((item) => {
                  const Icon = item.icon;
                  return (
                    <button
                      key={item.id}
                      onClick={() => handleItemClick(item.id)}
                      className={cn(
                        "w-full flex items-center space-x-3 px-4 py-2 rounded-lg transition-all duration-200 hover:bg-white/10 text-sm",
                        activeItem === item.id && "bg-white/30 shadow-md"
                      )}
                    >
                      <Icon className="w-4 h-4" />
                      <span className="font-medium">{item.label}</span>
                    </button>
                  );
                })}
              </div>
            )}
          </div>
        )}

        {/* Véhicules parent menu with sub-items */}
        <div>
          <button
            onClick={toggleVehicles}
            className={cn(
              "w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200 hover:bg-white/10",
              (activeItem.startsWith('vehicles-') || activeItem === 'vehicles') && "bg-white/20 shadow-md"
            )}
          >
            <div className="flex items-center space-x-3">
              <Car className="w-5 h-5" />
              <span className="text-sm font-medium">Véhicules</span>
            </div>
            {isVehiclesExpanded ? (
              <ChevronDown className="w-4 h-4" />
            ) : (
              <ChevronRight className="w-4 h-4" />
            )}
          </button>
          
          {/* Véhicules sub-items */}
          {isVehiclesExpanded && (
            <div className="ml-4 mt-2 space-y-1">
              {vehiclesSubItems.map((item) => {
                const Icon = item.icon;
                return (
                  <button
                    key={item.id}
                    onClick={() => handleItemClick(item.id)}
                    className={cn(
                      "w-full flex items-center space-x-3 px-4 py-2 rounded-lg transition-all duration-200 hover:bg-white/10 text-sm",
                      activeItem === item.id && "bg-white/30 shadow-md"
                    )}
                  >
                    <Icon className="w-4 h-4" />
                    <span className="font-medium">{item.label}</span>
                  </button>
                );
              })}
            </div>
          )}
        </div>

        {/* Achats/Ventes - positioned after Véhicules and before Trouver un garage */}
        <div>
          <button
            onClick={() => handleItemClick('vehicles-sales')}
            className={cn(
              "w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 hover:bg-white/10",
              activeItem === 'vehicles-sales' && "bg-white/20 shadow-md"
            )}
          >
            <TrendingDown className="w-5 h-5" />
            <span className="text-sm font-medium">Achats/Ventes</span>
          </button>
        </div>

        {/* Trouver un garage parent menu with sub-items */}
        <div>
          <button
            onClick={toggleGarage}
            className={cn(
              "w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200 hover:bg-white/10",
              (activeItem === 'find-garage' || activeItem === 'reservations') && "bg-white/20 shadow-md"
            )}
          >
            <div className="flex items-center space-x-3">
              <MapPin className="w-5 h-5" />
              <span className="text-sm font-medium">Trouver un garage</span>
            </div>
            {isGarageExpanded ? (
              <ChevronDown className="w-4 h-4" />
            ) : (
              <ChevronRight className="w-4 h-4" />
            )}
          </button>
          
          {/* Garage sub-items */}
          {isGarageExpanded && (
            <div className="ml-4 mt-2 space-y-1">
              {garageSubItems.map((item) => {
                const Icon = item.icon;
                return (
                  <button
                    key={item.id}
                    onClick={() => handleItemClick(item.id)}
                    className={cn(
                      "w-full flex items-center space-x-3 px-4 py-2 rounded-lg transition-all duration-200 hover:bg-white/10 text-sm",
                      activeItem === item.id && "bg-white/30 shadow-md"
                    )}
                  >
                    <Icon className="w-4 h-4" />
                    <span className="font-medium">{item.label}</span>
                  </button>
                );
              })}
            </div>
          )}
        </div>

        {/* Trouver Centre de CT parent menu with sub-items */}
        <div>
          <button
            onClick={toggleCT}
            className={cn(
              "w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200 hover:bg-white/10",
              (activeItem === 'find-ct' || activeItem === 'ct-reservations') && "bg-white/20 shadow-md"
            )}
          >
            <div className="flex items-center space-x-3">
              <ClipboardCheck className="w-5 h-5" />
              <span className="text-sm font-medium">Trouver Centre de CT</span>
            </div>
            {isCTExpanded ? (
              <ChevronDown className="w-4 h-4" />
            ) : (
              <ChevronRight className="w-4 h-4" />
            )}
          </button>
          
          {/* CT sub-items */}
          {isCTExpanded && (
            <div className="ml-4 mt-2 space-y-1">
              {ctSubItems.map((item) => {
                const Icon = item.icon;
                return (
                  <button
                    key={item.id}
                    onClick={() => handleItemClick(item.id)}
                    className={cn(
                      "w-full flex items-center space-x-3 px-4 py-2 rounded-lg transition-all duration-200 hover:bg-white/10 text-sm",
                      activeItem === item.id && "bg-white/30 shadow-md"
                    )}
                  >
                    <Icon className="w-4 h-4" />
                    <span className="font-medium">{item.label}</span>
                  </button>
                );
              })}
            </div>
          )}
        </div>

        {/* Other menu items */}
        {menuItems.map((item) => {
          const Icon = item.icon;
          return (
            <button
              key={item.id}
              onClick={() => handleItemClick(item.id)}
              className={cn(
                "w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 hover:bg-white/10",
                activeItem === item.id && "bg-white/20 shadow-md"
              )}
            >
              <Icon className="w-5 h-5" />
              <span className="text-sm font-medium">{item.label}</span>
            </button>
          );
        })}
      </nav>
    </div>
  );

  return (
    <>
      {/* Desktop Sidebar - Fixed position */}
      <div className="hidden md:block w-64 h-screen fixed top-0 left-0 z-50">
        <SidebarContent />
      </div>
      
      {/* Mobile Drawer */}
      <Drawer open={isOpen && window.innerWidth < 768} onOpenChange={onClose}>
        <DrawerContent className="h-[90vh]">
          <DrawerHeader className="sr-only">
            <DrawerTitle>Menu Navigation</DrawerTitle>
          </DrawerHeader>
          <SidebarContent />
        </DrawerContent>
      </Drawer>
    </>
  );
};

export default Sidebar;
