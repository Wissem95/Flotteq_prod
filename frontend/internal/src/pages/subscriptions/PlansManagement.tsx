// PlansManagement.tsx - Gestion des plans tarifaires FlotteQ

import React, { useState, useEffect } from "react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue, } from "@/components/ui/select";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from "@/components/ui/dialog";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger, } from "@/components/ui/dropdown-menu";
import { Plus, Edit, MoreHorizontal, Star, Check, Eye, Power, Users, Car, Shield, Zap, Crown, Building2, DollarSign, } from "lucide-react";
import { SubscriptionPlan, CreatePlanData, subscriptionsService } from "@/services/subscriptionsService";

const PlansManagement: React.FC = () => {
  const [plans, setPlans] = useState<SubscriptionPlan[]>([]);
  const [loading, setLoading] = useState(true);
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [editingPlan, setEditingPlan] = useState<SubscriptionPlan | null>(null);
  const [formData, setFormData] = useState<CreatePlanData>({
    name: "",
    description: "",
    price_monthly: 0,
    price_yearly: 0,
    features: [],
    max_vehicles: 0,
    max_users: 0,
    support_level: "basic",
    is_popular: false,
  });


  useEffect(() => {
    loadPlans();
  }, []);

  const loadPlans = async () => {
    setLoading(true);
    try {
      const plansData = await subscriptionsService.getPlans();
      setPlans(plansData);
    } catch (error) {
      console.error("Erreur lors du chargement des plans:", error);
      setPlans([]);
    } finally {
      setLoading(false);
    }
  };

  const handleCreatePlan = async () => {
    try {
      const newPlan = await subscriptionsService.createPlan(formData);
      setPlans(prev => [...prev, newPlan]);
      setShowCreateModal(false);
      resetForm();
    } catch (error) {
      console.error("Erreur lors de la création du plan:", error);
    }
  };

  const handleUpdatePlan = async () => {
    if (!editingPlan) return;
    
    try {
      const updatedPlan = await subscriptionsService.updatePlan(editingPlan.id, formData);
      setPlans(prev => prev.map(plan => plan.id === editingPlan.id ? updatedPlan : plan));
      setEditingPlan(null);
      resetForm();
    } catch (error) {
      console.error("Erreur lors de la mise à jour du plan:", error);
    }
  };

  const togglePlanStatus = async (planId: string) => {
    try {
      const updatedPlan = await subscriptionsService.togglePlanStatus(planId);
      setPlans(prev => prev.map(plan => 
        plan.id === planId ? updatedPlan : plan
      ));
    } catch (error) {
      console.error("Erreur lors du changement de statut:", error);
    }
  };

  const resetForm = () => {
    setFormData({
      name: "",
      description: "",
      price_monthly: 0,
      price_yearly: 0,
      features: [],
      max_vehicles: 0,
      max_users: 0,
      support_level: "basic",
      is_popular: false,
    });
  };

  const openEditModal = (plan: SubscriptionPlan) => {
    setEditingPlan(plan);
    setFormData({
      name: plan.name || '',
      description: plan.description || '',
      price_monthly: plan.price_monthly || 0,
      price_yearly: plan.price_yearly || 0,
      features: plan.features || [],
      max_vehicles: plan.max_vehicles || 0,
      max_users: plan.max_users || 0,
      support_level: plan.support_level || 'basic',
      is_popular: plan.is_popular || false,
    });
  };

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency: 'EUR',
    }).format(price);
  };

  const getPlanIcon = (planName?: string) => {
    if (!planName) {
      return <Star className="w-5 h-5 text-gray-500" />;
    }
    
    switch (planName.toLowerCase()) {
      case 'starter':
        return <Zap className="w-5 h-5 text-green-500" />;
      case 'business':
      case 'professional':
        return <Building2 className="w-5 h-5 text-blue-500" />;
      case 'enterprise':
        return <Crown className="w-5 h-5 text-purple-500" />;
      default:
        return <Star className="w-5 h-5 text-gray-500" />;
    }
  };

  const getSupportBadge = (level?: string) => {
    switch (level) {
      case 'basic':
        return <Badge variant="secondary">Support basique</Badge>;
      case 'premium':
        return <Badge variant="default" className="bg-blue-100 text-blue-800">Support premium</Badge>;
      case 'enterprise':
        return <Badge variant="default" className="bg-purple-100 text-purple-800">Support 24/7</Badge>;
      default:
        return <Badge variant="secondary">Support</Badge>;
    }
  };

  return (
    <div className="space-y-6">
      {/* En-tête */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Gestion des plans tarifaires</h1>
          <p className="text-gray-600">Créez et gérez les offres d'abonnement FlotteQ</p>
        </div>
        <Dialog open={showCreateModal} onOpenChange={setShowCreateModal}>
          <DialogTrigger asChild>
            <Button className="flex items-center gap-2">
              <Plus className="w-4 h-4" />
              Créer un plan
            </Button>
          </DialogTrigger>
        </Dialog>
      </div>

      {/* Statistiques rapides */}
      <div className="grid gap-4 md:grid-cols-4">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Plans actifs</CardTitle>
            <Star className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{plans.filter(p => p.is_active).length}</div>
            <p className="text-xs text-muted-foreground">
              {plans.filter(p => !p.is_active).length} inactifs
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Prix minimum</CardTitle>
            <DollarSign className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {plans.length > 0 && plans.some(p => p.price_monthly !== undefined) 
                ? formatPrice(Math.min(...plans.filter(p => p.price_monthly !== undefined).map(p => p.price_monthly)))
                : '0,00 €'
              }
            </div>
            <p className="text-xs text-muted-foreground">par mois</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Prix maximum</CardTitle>
            <DollarSign className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {plans.length > 0 && plans.some(p => p.price_monthly !== undefined) 
                ? formatPrice(Math.max(...plans.filter(p => p.price_monthly !== undefined).map(p => p.price_monthly)))
                : '0,00 €'
              }
            </div>
            <p className="text-xs text-muted-foreground">par mois</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Plan populaire</CardTitle>
            <Crown className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {plans.find(p => p.is_popular)?.name || "Aucun"}
            </div>
            <p className="text-xs text-muted-foreground">Plan mis en avant</p>
          </CardContent>
        </Card>
      </div>

      {/* Grille des plans */}
      <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        {loading ? (
          <div className="col-span-full flex items-center justify-center h-64">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
        ) : (
          plans.map((plan) => (
            <Card key={plan.id} className={`relative ${plan.is_popular ? 'ring-2 ring-blue-500' : ''}`}>
              {plan.is_popular && (
                <div className="absolute -top-3 left-1/2 transform -translate-x-1/2">
                  <Badge className="bg-blue-500 text-white">
                    <Star className="w-3 h-3 mr-1" />
                    Populaire
                  </Badge>
                </div>
              )}
              
              <CardHeader>
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    {getPlanIcon(plan.name)}
                    <CardTitle className="text-xl">{plan.name}</CardTitle>
                  </div>
                  <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                      <Button variant="ghost" size="icon">
                        <MoreHorizontal className="w-4 h-4" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                      <DropdownMenuItem onClick={() => openEditModal(plan)} className="flex items-center gap-2">
                        <Edit className="w-4 h-4" />
                        Modifier
                      </DropdownMenuItem>
                      <DropdownMenuItem className="flex items-center gap-2">
                        <Eye className="w-4 h-4" />
                        Voir détails
                      </DropdownMenuItem>
                      <DropdownMenuSeparator />
                      <DropdownMenuItem 
                        onClick={() => togglePlanStatus(plan.id)}
                        className={`flex items-center gap-2 ${plan.is_active ? 'text-red-600' : 'text-green-600'}`}
                      >
                        <Power className="w-4 h-4" />
                        {plan.is_active ? 'Désactiver' : 'Activer'}
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </div>
                <CardDescription>{plan.description}</CardDescription>
              </CardHeader>
              
              <CardContent className="space-y-4">
                {/* Prix */}
                <div className="space-y-2">
                  <div className="flex items-baseline gap-2">
                    <span className="text-3xl font-bold">{formatPrice(plan.price_monthly || 0)}</span>
                    <span className="text-gray-500">/mois</span>
                  </div>
                  {plan.price_yearly && plan.price_monthly && (
                    <div className="text-sm text-gray-500">
                      {formatPrice(plan.price_yearly)} /an 
                      <span className="text-green-600 ml-1">
                        (économie de {Math.round((1 - plan.price_yearly / (plan.price_monthly * 12)) * 100)}%)
                      </span>
                    </div>
                  )}
                </div>

                {/* Limites */}
                <div className="space-y-2">
                  <div className="flex items-center gap-2 text-sm">
                    <Car className="w-4 h-4 text-gray-500" />
                    <span>
                      {(plan.max_vehicles === -1 || plan.max_vehicles === undefined) ? 'Véhicules illimités' : `${plan.max_vehicles} véhicules max`}
                    </span>
                  </div>
                  <div className="flex items-center gap-2 text-sm">
                    <Users className="w-4 h-4 text-gray-500" />
                    <span>
                      {(plan.max_users === -1 || plan.max_users === undefined) ? 'Utilisateurs illimités' : `${plan.max_users} utilisateurs max`}
                    </span>
                  </div>
                  <div className="flex items-center gap-2 text-sm">
                    <Shield className="w-4 h-4 text-gray-500" />
                    {getSupportBadge(plan.support_level)}
                  </div>
                </div>

                {/* Fonctionnalités */}
                {plan.features && plan.features.length > 0 && (
                  <div>
                    <h4 className="font-medium mb-2">Fonctionnalités incluses :</h4>
                    <ul className="space-y-1">
                      {plan.features.slice(0, 4).map((feature, index) => (
                        <li key={index} className="flex items-center gap-2 text-sm">
                          <Check className="w-3 h-3 text-green-500" />
                          <span>{feature}</span>
                        </li>
                      ))}
                      {plan.features.length > 4 && (
                        <li className="text-sm text-gray-500">
                          +{plan.features.length - 4} autres fonctionnalités
                        </li>
                      )}
                    </ul>
                  </div>
                )}

                {/* Statut */}
                <div className="pt-2 border-t">
                  <Badge variant={plan.is_active ? "default" : "secondary"}>
                    {plan.is_active ? 'Actif' : 'Inactif'}
                  </Badge>
                </div>
              </CardContent>
            </Card>
          ))
        )}
      </div>

      {/* Modal de création/édition */}
      <Dialog open={showCreateModal || editingPlan !== null} onOpenChange={(open) => {
        if (!open) {
          setShowCreateModal(false);
          setEditingPlan(null);
          resetForm();
        }
      }}>
        <DialogContent className="max-w-md">
          <DialogHeader>
            <DialogTitle>
              {editingPlan ? 'Modifier le plan' : 'Créer un nouveau plan'}
            </DialogTitle>
            <DialogDescription>
              {editingPlan ? 'Modifiez les détails du plan tarifaire' : 'Configurez les détails du nouveau plan tarifaire'}
            </DialogDescription>
          </DialogHeader>
          
          <div className="space-y-4">
            <div>
              <Label htmlFor="name">Nom du plan</Label>
              <Input
                id="name"
                value={formData.name}
                onChange={(e) => setFormData(prev => ({ ...prev, name: e.target.value }))}
                placeholder="Ex: Business"
              />
            </div>
            
            <div>
              <Label htmlFor="description">Description</Label>
              <Textarea
                id="description"
                value={formData.description}
                onChange={(e) => setFormData(prev => ({ ...prev, description: e.target.value }))}
                placeholder="Décrivez les avantages du plan"
                rows={3}
              />
            </div>
            
            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="price_monthly">Prix mensuel (€)</Label>
                <Input
                  id="price_monthly"
                  type="number"
                  value={formData.price_monthly}
                  onChange={(e) => setFormData(prev => ({ ...prev, price_monthly: Number(e.target.value) }))}
                />
              </div>
              <div>
                <Label htmlFor="price_yearly">Prix annuel (€)</Label>
                <Input
                  id="price_yearly"
                  type="number"
                  value={formData.price_yearly}
                  onChange={(e) => setFormData(prev => ({ ...prev, price_yearly: Number(e.target.value) }))}
                />
              </div>
            </div>
            
            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="max_vehicles">Véhicules max</Label>
                <Input
                  id="max_vehicles"
                  type="number"
                  value={formData.max_vehicles === -1 ? '' : formData.max_vehicles}
                  onChange={(e) => setFormData(prev => ({ 
                    ...prev, 
                    max_vehicles: e.target.value === '' ? -1 : Number(e.target.value) 
                  }))}
                  placeholder="Illimité si vide"
                />
              </div>
              <div>
                <Label htmlFor="max_users">Utilisateurs max</Label>
                <Input
                  id="max_users"
                  type="number"
                  value={formData.max_users === -1 ? '' : formData.max_users}
                  onChange={(e) => setFormData(prev => ({ 
                    ...prev, 
                    max_users: e.target.value === '' ? -1 : Number(e.target.value) 
                  }))}
                  placeholder="Illimité si vide"
                />
              </div>
            </div>
            
            <div>
              <Label htmlFor="support_level">Niveau de support</Label>
              <Select 
                value={formData.support_level || "basic"} 
                onValueChange={(value) => setFormData(prev => ({ ...prev, support_level: value as 'basic' | 'premium' | 'enterprise' }))}
              >
                <SelectTrigger>
                  <SelectValue placeholder="Sélectionner un niveau" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="basic">Support basique</SelectItem>
                  <SelectItem value="premium">Support premium</SelectItem>
                  <SelectItem value="enterprise">Support 24/7</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
          
          <DialogFooter>
            <Button variant="outline" onClick={() => {
              setShowCreateModal(false);
              setEditingPlan(null);
              resetForm();
            }}>
              Annuler
            </Button>
            <Button onClick={editingPlan ? handleUpdatePlan : handleCreatePlan}>
              {editingPlan ? 'Modifier' : 'Créer'}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default PlansManagement; 