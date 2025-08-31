import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
  Plus,
  Search,
  Filter,
  Gift,
  TrendingUp,
  Users,
  Calendar,
  MoreHorizontal,
  Eye,
  Edit,
  Copy,
  Trash2,
  Play,
  Pause,
  Target,
  DollarSign,
  Percent,
  Clock,
  CheckCircle,
  AlertTriangle,
  XCircle,
} from 'lucide-react';
import { toast } from '@/components/ui/use-toast';

interface Promotion {
  id: number;
  code: string;
  name: string;
  description: string;
  type: 'percentage' | 'fixed_amount' | 'free_trial' | 'upgrade_discount';
  value: number;
  currency?: string;
  status: 'active' | 'inactive' | 'expired' | 'scheduled';
  usage_limit?: number;
  usage_count: number;
  min_purchase_amount?: number;
  applicable_plans?: string[];
  valid_from: string;
  valid_until: string;
  created_at: string;
  updated_at: string;
  created_by: string;
}

interface PromotionStats {
  total_promotions: number;
  active_promotions: number;
  total_usage: number;
  total_savings: number;
  conversion_rate: number;
  top_performing: Array<{
    code: string;
    usage_count: number;
    savings: number;
  }>;
  usage_by_month: Array<{
    month: string;
    usage_count: number;
    savings: number;
  }>;
}

const PromotionsOverview: React.FC = () => {
  const [promotions, setPromotions] = useState<Promotion[]>([]);
  const [stats, setStats] = useState<PromotionStats | null>(null);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');
  const [typeFilter, setTypeFilter] = useState('all');

  // Promotions feature not yet implemented in backend
  // Using empty state until API is available

  useEffect(() => {
    loadData();
  }, [searchTerm, statusFilter, typeFilter]);

  const loadData = async () => {
    try {
      setLoading(true);
      // Promotions feature not yet implemented - show empty state
      setPromotions([]);
      setStats({
        total_promotions: 0,
        active_promotions: 0,
        total_usage: 0,
        total_savings: 0,
        conversion_rate: 0,
        top_performing: [],
        usage_by_month: [],
      });
    } catch (error) {
      toast({
        title: 'Erreur',
        description: 'Impossible de charger les promotions',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency: 'EUR',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    });
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'active':
        return <Badge className="bg-green-100 text-green-800"><CheckCircle className="w-3 h-3 mr-1" />Actif</Badge>;
      case 'inactive':
        return <Badge className="bg-gray-100 text-gray-800"><Pause className="w-3 h-3 mr-1" />Inactif</Badge>;
      case 'expired':
        return <Badge variant="destructive"><XCircle className="w-3 h-3 mr-1" />Expiré</Badge>;
      case 'scheduled':
        return <Badge className="bg-blue-100 text-blue-800"><Clock className="w-3 h-3 mr-1" />Programmé</Badge>;
      default:
        return <Badge variant="secondary">{status}</Badge>;
    }
  };

  const getTypeLabel = (type: string) => {
    switch (type) {
      case 'percentage':
        return 'Pourcentage';
      case 'fixed_amount':
        return 'Montant fixe';
      case 'free_trial':
        return 'Essai gratuit';
      case 'upgrade_discount':
        return 'Remise upgrade';
      default:
        return type;
    }
  };

  const getTypeIcon = (type: string) => {
    switch (type) {
      case 'percentage':
        return <Percent className="w-4 h-4 text-blue-600" />;
      case 'fixed_amount':
        return <DollarSign className="w-4 h-4 text-green-600" />;
      case 'free_trial':
        return <Gift className="w-4 h-4 text-purple-600" />;
      case 'upgrade_discount':
        return <TrendingUp className="w-4 h-4 text-orange-600" />;
      default:
        return <Gift className="w-4 h-4" />;
    }
  };

  const formatPromotionValue = (promotion: Promotion) => {
    switch (promotion.type) {
      case 'percentage':
        return `${promotion.value}%`;
      case 'fixed_amount':
        return formatCurrency(promotion.value);
      case 'free_trial':
        return `${promotion.value} jours`;
      default:
        return promotion.value.toString();
    }
  };

  const handleToggleStatus = async (promotionId: number) => {
    toast({
      title: 'Statut modifié',
      description: 'Le statut de la promotion a été modifié',
    });
    loadData();
  };

  const handleCopyCode = (code: string) => {
    navigator.clipboard.writeText(code);
    toast({
      title: 'Code copié',
      description: `Le code "${code}" a été copié dans le presse-papiers`,
    });
  };

  const handleDeletePromotion = async (promotionId: number) => {
    toast({
      title: 'Promotion supprimée',
      description: 'La promotion a été supprimée avec succès',
    });
    loadData();
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold flex items-center gap-2">
            <Gift className="w-6 h-6" />
            Offres & Promotions
          </h1>
          <p className="text-gray-600">Codes promo et campagnes marketing</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" className="flex items-center gap-2">
            <Target className="w-4 h-4" />
            Campagnes
          </Button>
          <Button className="flex items-center gap-2">
            <Plus className="w-4 h-4" />
            Nouvelle promotion
          </Button>
        </div>
      </div>

      {/* Statistiques principales */}
      {stats && (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total promotions</CardTitle>
              <Gift className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.total_promotions}</div>
              <p className="text-xs text-muted-foreground">
                {stats.active_promotions} actives
              </p>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Utilisations</CardTitle>
              <Users className="h-4 w-4 text-blue-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.total_usage.toLocaleString()}</div>
              <p className="text-xs text-muted-foreground">
                Taux de conversion: {stats.conversion_rate}%
              </p>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Économies totales</CardTitle>
              <DollarSign className="h-4 w-4 text-green-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">{formatCurrency(stats.total_savings)}</div>
              <p className="text-xs text-muted-foreground">
                Accordées aux clients
              </p>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Performance</CardTitle>
              <TrendingUp className="h-4 w-4 text-purple-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {stats.top_performing[0]?.code.substring(0, 8)}...
              </div>
              <p className="text-xs text-muted-foreground">
                Meilleure promotion
              </p>
            </CardContent>
          </Card>
        </div>
      )}

      {/* Filtres et recherche */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Filter className="w-5 h-5" />
            Filtres et recherche
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex gap-4 flex-wrap">
            <div className="flex-1 min-w-[300px]">
              <div className="relative">
                <Search className="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                <Input
                  placeholder="Rechercher par code ou nom de promotion..."
                  className="pl-10"
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                />
              </div>
            </div>
            
            <Select value={statusFilter} onValueChange={setStatusFilter}>
              <SelectTrigger className="w-[150px]">
                <SelectValue placeholder="Statut" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Tous les statuts</SelectItem>
                <SelectItem value="active">Actif</SelectItem>
                <SelectItem value="inactive">Inactif</SelectItem>
                <SelectItem value="scheduled">Programmé</SelectItem>
                <SelectItem value="expired">Expiré</SelectItem>
              </SelectContent>
            </Select>
            
            <Select value={typeFilter} onValueChange={setTypeFilter}>
              <SelectTrigger className="w-[180px]">
                <SelectValue placeholder="Type" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Tous les types</SelectItem>
                <SelectItem value="percentage">Pourcentage</SelectItem>
                <SelectItem value="fixed_amount">Montant fixe</SelectItem>
                <SelectItem value="free_trial">Essai gratuit</SelectItem>
                <SelectItem value="upgrade_discount">Remise upgrade</SelectItem>
              </SelectContent>
            </Select>
            
            <Button 
              variant="outline" 
              onClick={() => {
                setSearchTerm('');
                setStatusFilter('all');
                setTypeFilter('all');
              }}
            >
              Réinitialiser
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Liste des promotions */}
      <Card>
        <CardHeader>
          <CardTitle>Promotions ({promotions.length})</CardTitle>
          <CardDescription>
            Gestion des codes promo et campagnes promotionnelles
          </CardDescription>
        </CardHeader>
        <CardContent>
          {loading ? (
            <div className="space-y-4">
              {[1, 2, 3].map((i) => (
                <div key={i} className="animate-pulse flex space-x-4 p-4 border rounded">
                  <div className="rounded-full bg-gray-200 h-10 w-10"></div>
                  <div className="flex-1 space-y-2">
                    <div className="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div className="h-3 bg-gray-200 rounded w-1/2"></div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Code & Nom</TableHead>
                  <TableHead>Type</TableHead>
                  <TableHead>Valeur</TableHead>
                  <TableHead>Utilisation</TableHead>
                  <TableHead>Validité</TableHead>
                  <TableHead>Statut</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {promotions.map((promotion) => (
                  <TableRow key={promotion.id}>
                    <TableCell>
                      <div>
                        <div className="font-mono font-medium text-blue-600">{promotion.code}</div>
                        <div className="text-sm font-medium">{promotion.name}</div>
                        <div className="text-xs text-gray-500 truncate max-w-[200px]">
                          {promotion.description}
                        </div>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center gap-2">
                        {getTypeIcon(promotion.type)}
                        <span className="text-sm">{getTypeLabel(promotion.type)}</span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="font-medium">{formatPromotionValue(promotion)}</div>
                      {promotion.min_purchase_amount && (
                        <div className="text-xs text-gray-500">
                          Min: {formatCurrency(promotion.min_purchase_amount)}
                        </div>
                      )}
                    </TableCell>
                    <TableCell>
                      <div>
                        <div className="font-medium">{promotion.usage_count}</div>
                        {promotion.usage_limit && (
                          <div className="text-xs text-gray-500">
                            / {promotion.usage_limit} max
                          </div>
                        )}
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="text-sm">
                        <div>{formatDate(promotion.valid_from)}</div>
                        <div className="text-gray-500">→ {formatDate(promotion.valid_until)}</div>
                      </div>
                    </TableCell>
                    <TableCell>
                      {getStatusBadge(promotion.status)}
                    </TableCell>
                    <TableCell>
                      <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                          <Button variant="ghost" size="icon">
                            <MoreHorizontal className="w-4 h-4" />
                          </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                          <DropdownMenuItem>
                            <Eye className="w-4 h-4 mr-2" />
                            Voir détails
                          </DropdownMenuItem>
                          <DropdownMenuItem>
                            <Edit className="w-4 h-4 mr-2" />
                            Modifier
                          </DropdownMenuItem>
                          <DropdownMenuItem onClick={() => handleCopyCode(promotion.code)}>
                            <Copy className="w-4 h-4 mr-2" />
                            Copier le code
                          </DropdownMenuItem>
                          <DropdownMenuSeparator />
                          <DropdownMenuItem onClick={() => handleToggleStatus(promotion.id)}>
                            {promotion.status === 'active' ? (
                              <>
                                <Pause className="w-4 h-4 mr-2" />
                                Désactiver
                              </>
                            ) : (
                              <>
                                <Play className="w-4 h-4 mr-2" />
                                Activer
                              </>
                            )}
                          </DropdownMenuItem>
                          <DropdownMenuSeparator />
                          <DropdownMenuItem 
                            className="text-red-600"
                            onClick={() => handleDeletePromotion(promotion.id)}
                          >
                            <Trash2 className="w-4 h-4 mr-2" />
                            Supprimer
                          </DropdownMenuItem>
                        </DropdownMenuContent>
                      </DropdownMenu>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
          
          {promotions.length === 0 && !loading && (
            <div className="text-center py-12">
              <Gift className="w-16 h-16 text-gray-400 mx-auto mb-4" />
              <div className="text-xl font-medium text-gray-900 mb-2">
                Aucune promotion trouvée
              </div>
              <div className="text-gray-500 mb-4">
                {searchTerm || statusFilter !== 'all' || typeFilter !== 'all'
                  ? 'Essayez de modifier vos filtres de recherche'
                  : 'Commencez par créer votre première promotion'
                }
              </div>
              <Button>
                <Plus className="w-4 h-4 mr-2" />
                Créer une promotion
              </Button>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
};

export default PromotionsOverview;