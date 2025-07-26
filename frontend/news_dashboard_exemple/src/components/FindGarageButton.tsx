
import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Wrench } from 'lucide-react';
import GarageMap from './GarageMap';

interface FindGarageButtonProps {
  vehicleId: string;
  alertType?: string;
  vehicleLocation?: string;
}

const FindGarageButton: React.FC<FindGarageButtonProps> = ({
  vehicleId,
  alertType,
  vehicleLocation
}) => {
  const [isOpen, setIsOpen] = useState(false);

  return (
    <Dialog open={isOpen} onOpenChange={setIsOpen}>
      <DialogTrigger asChild>
        <Button className="w-full bg-orange-600 hover:bg-orange-700 text-sm sm:text-base px-3 sm:px-4 py-2">
          <Wrench className="w-4 h-4 mr-2" />
          <span className="hidden sm:inline">🔧 Trouver un garage partenaire</span>
          <span className="sm:hidden">🔧 Garage</span>
        </Button>
      </DialogTrigger>
      <DialogContent className="max-w-[95vw] sm:max-w-6xl max-h-[90vh] overflow-y-auto p-3 sm:p-6">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2 text-base sm:text-lg">
            <Wrench className="w-5 h-5 text-orange-600" />
            <span className="truncate">
              Garages partenaires
              {alertType && ` - ${alertType}`}
            </span>
          </DialogTitle>
        </DialogHeader>
        <div className="mt-4">
          <GarageMap 
            vehicleId={vehicleId}
            alertType={alertType}
            location={vehicleLocation}
          />
        </div>
      </DialogContent>
    </Dialog>
  );
};

export default FindGarageButton;
