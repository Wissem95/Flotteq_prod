
import React from 'react';
import { Menu } from 'lucide-react';
import { Button } from '@/components/ui/button';
import NotificationDropdown from '@/components/NotificationDropdown';

interface HeaderProps {
  onMenuClick?: () => void;
}

const Header: React.FC<HeaderProps> = ({ onMenuClick }) => {
  return (
    <header className="bg-white shadow-sm border-b border-gray-200 px-4 py-3">
      <div className="flex items-center justify-between">
        <div className="flex items-center space-x-4">
          {/* Burger menu button - visible only on mobile */}
          <Button
            variant="ghost"
            size="icon"
            className="md:hidden"
            onClick={onMenuClick}
          >
            <Menu className="h-6 w-6" />
          </Button>
          
          <div className="hidden md:block">
            <h1 className="text-xl font-semibold text-gray-900">Dashboard</h1>
          </div>
        </div>
        
        <div className="flex items-center space-x-4">
          <NotificationDropdown />
          
          <div className="flex items-center space-x-2">
            <div className="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
              <span className="text-white text-sm font-medium">U</span>
            </div>
            <span className="hidden sm:block text-sm font-medium text-gray-700">Utilisateur</span>
          </div>
        </div>
      </div>
    </header>
  );
};

export default Header;
