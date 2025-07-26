
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Shield, Phone, FileText } from 'lucide-react';

interface InsuranceOffer {
  id: string;
  partnerName: string;
  partnerLogo?: string;
  coverageType: string;
  advantages: string[];
  expiryDate?: string;
}

interface InsuranceOfferCardProps {
  offer: InsuranceOffer;
  vehicleId?: string;
  onRequestQuote: (offerId: string) => void;
  onRequestCallback: (offerId: string) => void;
}

const InsuranceOfferCard: React.FC<InsuranceOfferCardProps> = ({
  offer,
  vehicleId,
  onRequestQuote,
  onRequestCallback
}) => {
  return (
    <Card className="border-2 border-blue-100 bg-blue-50/50 h-full flex flex-col">
      <CardHeader className="pb-3">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div className="flex items-center gap-3">
            <Shield className="w-6 h-6 text-blue-600 flex-shrink-0" />
            <div className="min-w-0">
              <CardTitle className="text-base sm:text-lg text-blue-900 truncate">{offer.partnerName}</CardTitle>
              <Badge variant="secondary" className="mt-1 text-xs">
                {offer.coverageType}
              </Badge>
            </div>
          </div>
          {offer.expiryDate && (
            <Badge variant="destructive" className="text-xs whitespace-nowrap">
              Expire le {new Date(offer.expiryDate).toLocaleDateString('fr-FR')}
            </Badge>
          )}
        </div>
      </CardHeader>
      <CardContent className="flex-1 flex flex-col">
        <div className="space-y-3 flex-1">
          <div>
            <h4 className="font-medium text-gray-900 mb-2 text-sm sm:text-base">Avantages :</h4>
            <ul className="space-y-1">
              {offer.advantages.map((advantage, index) => (
                <li key={index} className="text-xs sm:text-sm text-gray-600 flex items-start gap-2">
                  <div className="w-1.5 h-1.5 bg-blue-500 rounded-full mt-1.5 flex-shrink-0"></div>
                  <span className="break-words">{advantage}</span>
                </li>
              ))}
            </ul>
          </div>
        </div>
        <div className="flex flex-col sm:flex-row gap-2 pt-4 mt-auto">
          <Button 
            onClick={() => onRequestQuote(offer.id)}
            className="flex-1 text-xs sm:text-sm"
            size="sm"
          >
            <FileText className="w-4 h-4 mr-2" />
            <span className="hidden sm:inline">Demander un devis</span>
            <span className="sm:hidden">Devis</span>
          </Button>
          <Button 
            onClick={() => onRequestCallback(offer.id)}
            variant="outline"
            size="sm"
            className="flex-1 text-xs sm:text-sm"
          >
            <Phone className="w-4 h-4 mr-2" />
            <span className="hidden sm:inline">Être rappelé</span>
            <span className="sm:hidden">Rappel</span>
          </Button>
        </div>
      </CardContent>
    </Card>
  );
};

export default InsuranceOfferCard;
