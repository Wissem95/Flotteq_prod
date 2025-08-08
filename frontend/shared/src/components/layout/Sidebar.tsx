import React, { useState, useEffect } from 'react';
import { LayoutDashboard, Car, Bell, Settings, X, ChevronDown, ChevronRight, Activity, TrendingUp, List, Wrench, History, BarChart3, Users, FileText, Shield, MapPin, Calendar, TrendingDown, ClipboardCheck, Receipt } from 'lucide-react';
import { cn } from '../../lib/utils';
import { Drawer, DrawerContent, DrawerHeader, DrawerTitle } from '../ui/drawer';

// Types
interface MenuItem {
  id: string;
  icon: React.ComponentType<{ className?: string }>;
  label: string;
}

interface MenuSection {
  id: string;
  icon: React.ComponentType<{ className?: string }>;
  label: string;
  items: MenuItem[];
}

interface SidebarConfig {
  title: string;
  menuSections: MenuSection[];
  simpleMenuItems: MenuItem[];
  features?: {
    multiUser?: boolean;
    userRoles?: boolean;
  };
}

interface SidebarProps {
  activeItem: string;
  onItemClick: (item: string) => void;
  isOpen?: boolean;
  onClose?: () => void;
  config?: SidebarConfig;
}

// Default configuration (based on news dashboard example)
const defaultConfig: SidebarConfig = {
  title: 'Flotteq',
  menuSections: [
    {
      id: 'dashboard',
      icon: LayoutDashboard,
      label: 'Dashboard',
      items: [
        { id: 'fleet-status', icon: Activity, label: 'État de la flotte' },
        { id: 'financial-status', icon: TrendingUp, label: 'État financier' },
      ]
    },
    {
      id: 'vehicles',
      icon: Car,
      label: 'Véhicules',
      items: [
        { id: 'vehicles-list', icon: List, label: 'Liste des véhicules' },
        { id: 'vehicles-maintenance', icon: Wrench, label: 'Maintenance' },
        { id: 'vehicles-history', icon: History, label: 'Historique' },
        { id: 'vehicles-statistics', icon: BarChart3, label: 'Statistiques' },
      ]
    },
    {
      id: 'garage',
      icon: MapPin,
      label: 'Trouver un garage',
      items: [
        { id: 'find-garage', icon: MapPin, label: 'Recherche de garage' },
        { id: 'reservations', icon: Calendar, label: 'Mes réservations' },
        { id: 'invoices', icon: Receipt, label: 'Mes factures' },
      ]
    },
    {
      id: 'ct',
      icon: ClipboardCheck,
      label: 'Trouver Centre de CT',
      items: [
        { id: 'find-ct', icon: ClipboardCheck, label: 'Recherche de centre CT' },
        { id: 'ct-reservations', icon: Calendar, label: 'Mes rendez-vous CT' },
        { id: 'process-reports', icon: FileText, label: 'Mes procès-verbaux' },
      ]
    }
  ],
  simpleMenuItems: [
    { id: 'vehicles-sales', icon: TrendingDown, label: 'Achats/Ventes' },
    { id: 'find-insurance', icon: Shield, label: 'Trouver une assurance' },
    { id: 'notifications', icon: Bell, label: 'Notifications' },
    { id: 'settings', icon: Settings, label: 'Paramètres' },
  ],
  features: {
    multiUser: true,
    userRoles: true,
  }
};

// Custom hook for collapsible state
const useCollapsibleState = (_key: string, defaultValue: boolean = false) => {
  const [isExpanded, setIsExpanded] = useState(defaultValue);

  const toggle = () => setIsExpanded(prev => !prev);

  return { isExpanded, toggle };
};

const Sidebar: React.FC<SidebarProps> = ({ 
  activeItem, 
  onItemClick, 
  isOpen = true, 
  onClose,
  config = defaultConfig
}) => {
  // Collapsible states for each section
  const sectionStates = config.menuSections.reduce((acc, section) => {
    acc[section.id] = useCollapsibleState(section.id, section.id === 'dashboard');
    return acc;
  }, {} as Record<string, { isExpanded: boolean; toggle: () => void }>);

  const [isMultiUserEnabled, setIsMultiUserEnabled] = useState(false);
  const [isUserRolesEnabled, setIsUserRolesEnabled] = useState(false);

  // Check localStorage for multi-user settings
  useEffect(() => {
    if (config.features?.multiUser) {
      const multiUserSetting = localStorage.getItem('multiUser');
      const userRolesSetting = localStorage.getItem('userRoles');
      setIsMultiUserEnabled(multiUserSetting === 'true');
      setIsUserRolesEnabled(config.features?.userRoles ? userRolesSetting === 'true' : false);
    }
  }, [config.features]);

  // Listen for storage changes to update sidebar in real-time
  useEffect(() => {
    if (!config.features?.multiUser) return;

    const handleStorageChange = () => {
      const multiUserSetting = localStorage.getItem('multiUser');
      const userRolesSetting = localStorage.getItem('userRoles');
      setIsMultiUserEnabled(multiUserSetting === 'true');
      setIsUserRolesEnabled(config.features?.userRoles ? userRolesSetting === 'true' : false);
    };

    window.addEventListener('storage', handleStorageChange);
    window.addEventListener('settingsUpdated', handleStorageChange);

    return () => {
      window.removeEventListener('storage', handleStorageChange);
      window.removeEventListener('settingsUpdated', handleStorageChange);
    };
  }, [config.features]);

  // Users section (only if multiUser is enabled)
  const usersSection: MenuSection = {
    id: 'users',
    icon: Users,
    label: 'Utilisateurs',
    items: [
      { id: 'users-list', icon: Users, label: 'Liste des utilisateurs' },
      ...(isUserRolesEnabled ? [{ id: 'users-roles', icon: Settings, label: 'Rôles' }] : [])
    ]
  };

  const handleItemClick = (itemId: string) => {
    onItemClick(itemId);
    // Close drawer on mobile when item is clicked
    if (onClose) {
      onClose();
    }
  };

  const renderMenuItem = (item: MenuItem) => {
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
  };

  const renderMenuSection = (section: MenuSection) => {
    const Icon = section.icon;
    const sectionState = sectionStates[section.id];
    const isActive = section.items.some(item => activeItem === item.id) || activeItem === section.id;

    return (
      <div key={section.id}>
        <button
          onClick={sectionState.toggle}
          className={cn(
            "w-full flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200 hover:bg-white/10",
            isActive && "bg-white/20 shadow-md"
          )}
        >
          <div className="flex items-center space-x-3">
            <Icon className="w-5 h-5" />
            <span className="text-sm font-medium">{section.label}</span>
          </div>
          {sectionState.isExpanded ? (
            <ChevronDown className="w-4 h-4" />
          ) : (
            <ChevronRight className="w-4 h-4" />
          )}
        </button>
        
        {/* Section sub-items */}
        {sectionState.isExpanded && (
          <div className="ml-4 mt-2 space-y-1">
            {section.items.map(renderMenuItem)}
          </div>
        )}
      </div>
    );
  };

  const renderSimpleMenuItem = (item: MenuItem) => {
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
  };

  const SidebarContent = () => (
    <div className="flotteq-sidebar w-full h-full text-white p-4 relative overflow-y-auto">
      <div className="flex items-center justify-center gap-3 mb-8">
        <h1 className="text-3xl font-bold">{config.title}</h1>
        {onClose && (
          <button onClick={onClose} className="md:hidden">
            <X className="w-5 h-5 cursor-pointer hover:opacity-70" />
          </button>
        )}
      </div>
      
      <nav className="space-y-2">
        {/* Render menu sections */}
        {config.menuSections.map(renderMenuSection)}

        {/* Users section (conditionally rendered) */}
        {isMultiUserEnabled && renderMenuSection(usersSection)}

        {/* Simple menu items */}
        {config.simpleMenuItems.map(renderSimpleMenuItem)}
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