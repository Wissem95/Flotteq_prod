
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Calendar, Plus, Car, FileText, TrendingUp, TrendingDown } from 'lucide-react';

// Define proper types for transactions
interface PurchaseTransaction {
  id: number;
  type: 'achat';
  vehicle: string;
  brand: string;
  model: string;
  date: string;
  price: number;
  seller: string;
  mileage: number;
  status: string;
}

interface SaleTransaction {
  id: number;
  type: 'vente';
  vehicle: string;
  brand: string;
  model: string;
  date: string;
  price: number;
  buyer: string;
  mileage: number;
  reason: string;
  status: string;
}

type Transaction = PurchaseTransaction | SaleTransaction;

const TransactionsPage: React.FC = () => {
  const [activeTab, setActiveTab] = useState('overview');

  const purchaseTransactions: PurchaseTransaction[] = [
    {
      id: 1,
      type: 'achat',
      vehicle: 'AB-123-CD',
      brand: 'Peugeot',
      model: '308',
      date: '2024-01-15',
      price: 18500,
      seller: 'Concessionnaire Martin',
      mileage: 45000,
      status: 'Terminé'
    },
    {
      id: 2,
      type: 'achat',
      vehicle: 'EF-456-GH',
      brand: 'Renault',
      model: 'Clio',
      date: '2024-02-20',
      price: 15200,
      seller: 'Particulier',
      mileage: 38000,
      status: 'Terminé'
    }
  ];

  const saleTransactions: SaleTransaction[] = [
    {
      id: 3,
      type: 'vente',
      vehicle: 'IJ-789-KL',
      brand: 'Citroën',
      model: 'Berlingo',
      date: '2024-03-10',
      price: 12000,
      buyer: 'Garage Central',
      mileage: 180000,
      reason: 'Kilométrage élevé',
      status: 'Terminé'
    }
  ];

  const allTransactions: Transaction[] = [...purchaseTransactions, ...saleTransactions].sort(
    (a, b) => new Date(b.date).getTime() - new Date(a.date).getTime()
  );

  const getTypeColor = (type: string) => {
    return type === 'achat' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800';
  };

  const getTypeIcon = (type: string) => {
    return type === 'achat' ? TrendingDown : TrendingUp;
  };

  // Helper function to get the counterpart (seller or buyer)
  const getCounterpart = (transaction: Transaction) => {
    return transaction.type === 'achat' ? transaction.seller : transaction.buyer;
  };

  // Helper function to get the counterpart label
  const getCounterpartLabel = (transaction: Transaction) => {
    return transaction.type === 'achat' ? 'Vendeur:' : 'Acheteur:';
  };

  return (
    <div className="flex-1 p-6 bg-gray-50">
      <div className="max-w-7xl mx-auto">
        <div className="flex items-center justify-between mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Transactions</h1>
          <div className="flex items-center gap-4">
            <Button>
              <Plus className="w-4 h-4 mr-2" />
              Nouvelle transaction
            </Button>
          </div>
        </div>

        {/* Summary Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Total achats</p>
                  <p className="text-2xl font-bold text-green-600">33 700 €</p>
                </div>
                <TrendingDown className="w-8 h-8 text-green-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Total ventes</p>
                  <p className="text-2xl font-bold text-blue-600">12 000 €</p>
                </div>
                <TrendingUp className="w-8 h-8 text-blue-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Véhicules achetés</p>
                  <p className="text-2xl font-bold text-gray-900">2</p>
                </div>
                <Car className="w-8 h-8 text-gray-600" />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Véhicules vendus</p>
                  <p className="text-2xl font-bold text-gray-900">1</p>
                </div>
                <Car className="w-8 h-8 text-gray-600" />
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Transactions List */}
        <Card>
          <CardHeader>
            <CardTitle>Historique des transactions</CardTitle>
          </CardHeader>
          <CardContent>
            <Tabs defaultValue="all" className="w-full">
              <TabsList className="grid w-full grid-cols-3">
                <TabsTrigger value="all">Toutes</TabsTrigger>
                <TabsTrigger value="purchases">Achats</TabsTrigger>
                <TabsTrigger value="sales">Ventes</TabsTrigger>
              </TabsList>

              <TabsContent value="all" className="mt-6">
                <div className="space-y-4">
                  {allTransactions.map((transaction) => {
                    const TypeIcon = getTypeIcon(transaction.type);
                    return (
                      <Card key={transaction.id} className="border-l-4 border-gray-200">
                        <CardContent className="p-4">
                          <div className="flex items-start justify-between">
                            <div className="flex items-start gap-3">
                              <TypeIcon className="w-5 h-5 mt-1 text-gray-600" />
                              <div className="flex-1">
                                <div className="flex items-center gap-2 mb-1">
                                  <h3 className="font-medium text-gray-900">
                                    {transaction.brand} {transaction.model} ({transaction.vehicle})
                                  </h3>
                                  <Badge className={getTypeColor(transaction.type)}>
                                    {transaction.type === 'achat' ? 'Achat' : 'Vente'}
                                  </Badge>
                                  <Badge variant="secondary">{transaction.status}</Badge>
                                </div>
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600 mt-2">
                                  <div>
                                    <span className="font-medium">Date:</span> {new Date(transaction.date).toLocaleDateString('fr-FR')}
                                  </div>
                                  <div>
                                    <span className="font-medium">Prix:</span> {transaction.price.toLocaleString()} €
                                  </div>
                                  <div>
                                    <span className="font-medium">Kilométrage:</span> {transaction.mileage?.toLocaleString()} km
                                  </div>
                                  <div>
                                    <span className="font-medium">
                                      {getCounterpartLabel(transaction)}
                                    </span> {getCounterpart(transaction)}
                                  </div>
                                </div>
                                {transaction.type === 'vente' && transaction.reason && (
                                  <div className="text-sm text-gray-600 mt-1">
                                    <span className="font-medium">Raison:</span> {transaction.reason}
                                  </div>
                                )}
                              </div>
                            </div>
                            <div className="flex gap-2">
                              <Button size="sm" variant="outline">
                                <FileText className="w-4 h-4 mr-1" />
                                Détails
                              </Button>
                            </div>
                          </div>
                        </CardContent>
                      </Card>
                    );
                  })}
                </div>
              </TabsContent>

              <TabsContent value="purchases" className="mt-6">
                <div className="space-y-4">
                  {purchaseTransactions.map((transaction) => {
                    const TypeIcon = getTypeIcon(transaction.type);
                    return (
                      <Card key={transaction.id} className="border-l-4 border-green-500 bg-green-50">
                        <CardContent className="p-4">
                          <div className="flex items-start gap-3">
                            <TypeIcon className="w-5 h-5 mt-1 text-green-600" />
                            <div className="flex-1">
                              <h3 className="font-medium text-gray-900 mb-1">
                                Achat - {transaction.brand} {transaction.model} ({transaction.vehicle})
                              </h3>
                              <div className="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm text-gray-600">
                                <div>Date: {new Date(transaction.date).toLocaleDateString('fr-FR')}</div>
                                <div>Prix: {transaction.price.toLocaleString()} €</div>
                                <div>Vendeur: {transaction.seller}</div>
                              </div>
                            </div>
                          </div>
                        </CardContent>
                      </Card>
                    );
                  })}
                </div>
              </TabsContent>

              <TabsContent value="sales" className="mt-6">
                <div className="space-y-4">
                  {saleTransactions.map((transaction) => {
                    const TypeIcon = getTypeIcon(transaction.type);
                    return (
                      <Card key={transaction.id} className="border-l-4 border-blue-500 bg-blue-50">
                        <CardContent className="p-4">
                          <div className="flex items-start gap-3">
                            <TypeIcon className="w-5 h-5 mt-1 text-blue-600" />
                            <div className="flex-1">
                              <h3 className="font-medium text-gray-900 mb-1">
                                Vente - {transaction.brand} {transaction.model} ({transaction.vehicle})
                              </h3>
                              <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600">
                                <div>Date: {new Date(transaction.date).toLocaleDateString('fr-FR')}</div>
                                <div>Prix: {transaction.price.toLocaleString()} €</div>
                                <div>Acheteur: {transaction.buyer}</div>
                                <div>Raison: {transaction.reason}</div>
                              </div>
                            </div>
                          </div>
                        </CardContent>
                      </Card>
                    );
                  })}
                </div>
              </TabsContent>
            </Tabs>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default TransactionsPage;
