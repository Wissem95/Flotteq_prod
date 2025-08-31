import React from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { CreditCard, Smartphone, Building, Globe, Settings, CheckCircle, AlertCircle, Clock } from 'lucide-react';

// Utilitaires sécurisés
import { safeArray, safeLength, safeReduce, safeFilter, safeMap } from '@/utils/safeData';

interface PaymentGateway {
  id: number;
  name: string;
  type: 'card' | 'mobile' | 'bank' | 'crypto';
  status: 'active' | 'inactive' | 'pending';
  provider: string;
  countries: string[];
  fees: {
    percentage: number;
    fixed: number;
  };
  volume_monthly: number;
  transactions_count: number;
  last_transaction: string;
}

const PaymentMethods: React.FC = () => {
  // Payment methods not yet implemented - showing empty state
  const paymentGateways: PaymentGateway[] = [];

  const getTypeIcon = (type: string) => {
    switch (type) {
      case 'card': return <CreditCard className="h-5 w-5 text-blue-600" />;
      case 'mobile': return <Smartphone className="h-5 w-5 text-green-600" />;
      case 'bank': return <Building className="h-5 w-5 text-purple-600" />;
      case 'crypto': return <Globe className="h-5 w-5 text-orange-600" />;
      default: return <CreditCard className="h-5 w-5 text-gray-600" />;
    }
  };

  const getStatusBadge = (status: string) => {
    const variants = {
      active: { variant: 'default' as const, icon: CheckCircle, text: 'Actif' },
      inactive: { variant: 'secondary' as const, icon: Clock, text: 'Inactif' },
      pending: { variant: 'outline' as const, icon: AlertCircle, text: 'En attente' }
    };
    
    const config = variants[status as keyof typeof variants];
    const Icon = config.icon;
    
    return (
      <Badge variant={config.variant} className="flex items-center gap-1">
        <Icon className="h-3 w-3" />
        {config.text}
      </Badge>
    );
  };

  const totalVolume = safeReduce(paymentGateways, (sum, gateway) => sum + gateway.volume_monthly, 0);
  const totalTransactions = safeReduce(paymentGateways, (sum, gateway) => sum + gateway.transactions_count, 0);
  const activeGateways = safeLength(safeFilter(paymentGateways, g => g.status === 'active'));

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Modes de Paiement</h1>
          <p className="text-gray-600">Configuration et gestion des passerelles de paiement</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline">
            <Settings className="h-4 w-4 mr-2" />
            Paramètres Globaux
          </Button>
          <Button>
            <CreditCard className="h-4 w-4 mr-2" />
            Ajouter Passerelle
          </Button>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Passerelles Actives</CardTitle>
            <CheckCircle className="h-4 w-4 text-green-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-green-600">{activeGateways}</div>
            <p className="text-xs text-muted-foreground">Sur {safeLength(paymentGateways)} configurées</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Volume Mensuel</CardTitle>
            <CreditCard className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{totalVolume.toLocaleString('fr-FR')} €</div>
            <p className="text-xs text-muted-foreground">+12% vs mois dernier</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Transactions</CardTitle>
            <Building className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{totalTransactions.toLocaleString('fr-FR')}</div>
            <p className="text-xs text-muted-foreground">Ce mois</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Frais Moyens</CardTitle>
            <Globe className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">2.3%</div>
            <p className="text-xs text-muted-foreground">+ 0.28€ fixe</p>
          </CardContent>
        </Card>
      </div>

      {/* Payment Gateways List */}
      <Card>
        <CardHeader>
          <CardTitle>Passerelles de Paiement</CardTitle>
          <CardDescription>
            Configuration et monitoring des différents moyens de paiement
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {safeMap(paymentGateways, (gateway) => (
              <div key={gateway.id} className="flex items-center gap-4 p-4 border rounded-lg hover:bg-gray-50">
                <div className="flex-shrink-0">
                  {getTypeIcon(gateway.type)}
                </div>
                
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2 mb-1">
                    <h3 className="font-medium text-gray-900">{gateway.name}</h3>
                    {getStatusBadge(gateway.status)}
                  </div>
                  
                  <div className="flex items-center gap-4 text-sm text-gray-600 mb-2">
                    <span className="font-medium">{gateway.provider}</span>
                    <span>Frais: {gateway.fees.percentage}% + {gateway.fees.fixed}€</span>
                    <span>Pays: {gateway.countries.join(', ')}</span>
                  </div>
                  
                  <div className="flex items-center gap-6 text-xs text-gray-500">
                    <span>Volume: {gateway.volume_monthly.toLocaleString('fr-FR')} €</span>
                    <span>Transactions: {gateway.transactions_count}</span>
                    {gateway.last_transaction && (
                      <span>
                        Dernière: {new Date(gateway.last_transaction).toLocaleString('fr-FR')}
                      </span>
                    )}
                  </div>
                </div>
                
                <div className="flex-shrink-0">
                  <div className="flex gap-2">
                    <Button size="sm" variant="outline">
                      Configurer
                    </Button>
                    <Button size="sm" variant="outline">
                      Stats
                    </Button>
                    {gateway.status === 'pending' && (
                      <Button size="sm" variant="default">
                        Activer
                      </Button>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      {/* Transaction Fees Summary */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Frais par Type</CardTitle>
            <CardDescription>Répartition des coûts par mode de paiement</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              <div className="flex justify-between items-center">
                <div className="flex items-center gap-2">
                  <CreditCard className="h-4 w-4 text-blue-600" />
                  <span className="text-sm">Cartes bancaires</span>
                </div>
                <span className="font-medium">2.9% + 0.30€</span>
              </div>
              <div className="flex justify-between items-center">
                <div className="flex items-center gap-2">
                  <Smartphone className="h-4 w-4 text-green-600" />
                  <span className="text-sm">Paiement mobile</span>
                </div>
                <span className="font-medium">1.8% + 0.10€</span>
              </div>
              <div className="flex justify-between items-center">
                <div className="flex items-center gap-2">
                  <Building className="h-4 w-4 text-purple-600" />
                  <span className="text-sm">Virement SEPA</span>
                </div>
                <span className="font-medium">1.0% + 0.25€</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Paramètres Généraux</CardTitle>
            <CardDescription>Configuration globale des paiements</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div className="flex justify-between items-center">
                <span className="text-sm">Auto-capture des paiements</span>
                <Badge variant="default">Activé</Badge>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-sm">Remboursements automatiques</span>
                <Badge variant="secondary">Désactivé</Badge>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-sm">Notifications webhook</span>
                <Badge variant="default">Activé</Badge>
              </div>
              <div className="flex justify-between items-center">
                <span className="text-sm">Mode test</span>
                <Badge variant="outline">Sandbox</Badge>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default PaymentMethods;