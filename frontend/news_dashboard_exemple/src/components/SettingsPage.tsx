import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Settings, Bell, Users, FileText, Shield, Palette, Database, Car } from 'lucide-react';

const SettingsPage: React.FC = () => {
  const [multiUser, setMultiUser] = useState(false);
  const [userRoles, setUserRoles] = useState(false);

  // Load settings from localStorage on component mount
  useEffect(() => {
    const savedMultiUser = localStorage.getItem('multiUser');
    const savedUserRoles = localStorage.getItem('userRoles');
    
    if (savedMultiUser !== null) {
      setMultiUser(savedMultiUser === 'true');
    }
    if (savedUserRoles !== null) {
      setUserRoles(savedUserRoles === 'true');
    }
  }, []);

  // Save settings to localStorage and dispatch custom event
  const updateSetting = (key: string, value: boolean) => {
    localStorage.setItem(key, value.toString());
    // Dispatch custom event to notify sidebar of changes
    window.dispatchEvent(new CustomEvent('settingsUpdated'));
  };

  const handleMultiUserChange = (checked: boolean) => {
    setMultiUser(checked);
    updateSetting('multiUser', checked);
    
    // If multi-user is disabled, also disable user roles
    if (!checked) {
      setUserRoles(false);
      updateSetting('userRoles', false);
    }
  };

  const handleUserRolesChange = (checked: boolean) => {
    setUserRoles(checked);
    updateSetting('userRoles', checked);
  };

  return (
    <div className="flex-1 p-6 bg-gray-50">
      <div className="max-w-7xl mx-auto">
        <h1 className="text-3xl font-bold text-gray-900 mb-8">Paramètres</h1>
        
        <Tabs defaultValue="general" className="w-full">
          <TabsList className="grid w-full grid-cols-7">
            <TabsTrigger value="general">Général</TabsTrigger>
            <TabsTrigger value="notifications">Notifications</TabsTrigger>
            <TabsTrigger value="users">Utilisateurs</TabsTrigger>
            <TabsTrigger value="maintenance">Entretien</TabsTrigger>
            <TabsTrigger value="voiture">Voiture</TabsTrigger>
            <TabsTrigger value="security">Sécurité</TabsTrigger>
            <TabsTrigger value="export">Export</TabsTrigger>
          </TabsList>

          <TabsContent value="general" className="space-y-6 mt-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Settings className="w-5 h-5" />
                  Configuration générale
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="grid grid-cols-2 gap-6">
                  <div>
                    <Label htmlFor="companyName">Nom de l'entreprise</Label>
                    <Input id="companyName" defaultValue="Flotteq" />
                  </div>
                  <div>
                    <Label htmlFor="contactEmail">Email de contact</Label>
                    <Input id="contactEmail" type="email" defaultValue="contact@flotteq.com" />
                  </div>
                </div>
                <div className="grid grid-cols-2 gap-6">
                  <div>
                    <Label htmlFor="currency">Devise</Label>
                    <Input id="currency" defaultValue="EUR (€)" />
                  </div>
                  <div>
                    <Label htmlFor="timezone">Fuseau horaire</Label>
                    <Input id="timezone" defaultValue="Europe/Paris" />
                  </div>
                </div>
                <div className="flex items-center justify-between">
                  <div>
                    <Label htmlFor="autoBackup">Sauvegarde automatique</Label>
                    <p className="text-sm text-gray-600">Sauvegarder automatiquement les données chaque nuit</p>
                  </div>
                  <Switch id="autoBackup" defaultChecked />
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Palette className="w-5 h-5" />
                  Préférences d'affichage
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="flex items-center justify-between">
                  <div>
                    <Label htmlFor="darkMode">Mode sombre</Label>
                    <p className="text-sm text-gray-600">Activer le thème sombre</p>
                  </div>
                  <Switch id="darkMode" />
                </div>
                <div className="flex items-center justify-between">
                  <div>
                    <Label htmlFor="compactView">Vue compacte</Label>
                    <p className="text-sm text-gray-600">Affichage plus dense des données</p>
                  </div>
                  <Switch id="compactView" />
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="notifications" className="space-y-6 mt-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Bell className="w-5 h-5" />
                  Paramètres de notification
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="flex items-center justify-between">
                  <div>
                    <Label htmlFor="ctNotifications">Notifications CT</Label>
                    <p className="text-sm text-gray-600">Alertes pour les contrôles techniques</p>
                  </div>
                  <Switch id="ctNotifications" defaultChecked />
                </div>
                <div className="flex items-center justify-between">
                  <div>
                    <Label htmlFor="maintenanceNotifications">Notifications entretien</Label>
                    <p className="text-sm text-gray-600">Rappels pour les révisions</p>
                  </div>
                  <Switch id="maintenanceNotifications" defaultChecked />
                </div>
                <div className="flex items-center justify-between">
                  <div>
                    <Label htmlFor="adminNotifications">Notifications administratives</Label>
                    <p className="text-sm text-gray-600">Assurances, cartes grises, etc.</p>
                  </div>
                  <Switch id="adminNotifications" defaultChecked />
                </div>
                <div className="grid grid-cols-2 gap-6">
                  <div>
                    <Label htmlFor="ctAdvance">Préavis CT (jours)</Label>
                    <Input id="ctAdvance" type="number" defaultValue="30" />
                  </div>
                  <div>
                    <Label htmlFor="maintenanceAdvance">Préavis entretien (km)</Label>
                    <Input id="maintenanceAdvance" type="number" defaultValue="1000" />
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="users" className="space-y-6 mt-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Users className="w-5 h-5" />
                  Gestion des utilisateurs
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="flex items-center justify-between">
                  <div>
                    <Label htmlFor="multiUser">Mode multi-utilisateur</Label>
                    <p className="text-sm text-gray-600">Permettre plusieurs utilisateurs</p>
                  </div>
                  <Switch 
                    id="multiUser" 
                    checked={multiUser}
                    onCheckedChange={handleMultiUserChange}
                  />
                </div>
                <div className="flex items-center justify-between">
                  <div>
                    <Label htmlFor="userRoles">Rôles utilisateur</Label>
                    <p className="text-sm text-gray-600">Gestionnaire, Conducteur, Lecture seule</p>
                  </div>
                  <Switch 
                    id="userRoles" 
                    checked={userRoles}
                    onCheckedChange={handleUserRolesChange}
                    disabled={!multiUser}
                  />
                </div>
                <Button disabled={!multiUser}>Ajouter un utilisateur</Button>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="maintenance" className="space-y-6 mt-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Settings className="w-5 h-5" />
                  Configuration entretien
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="grid grid-cols-2 gap-6">
                  <div>
                    <Label htmlFor="defaultMaintenanceKm">Révision par défaut (km)</Label>
                    <Input id="defaultMaintenanceKm" type="number" defaultValue="15000" />
                  </div>
                  <div>
                    <Label htmlFor="defaultMaintenanceMonths">Révision par défaut (mois)</Label>
                    <Input id="defaultMaintenanceMonths" type="number" defaultValue="12" />
                  </div>
                </div>
                <div className="grid grid-cols-2 gap-6">
                  <div>
                    <Label htmlFor="ctValidityVp">Validité CT VP (ans)</Label>
                    <Input id="ctValidityVp" type="number" defaultValue="2" />
                  </div>
                  <div>
                    <Label htmlFor="ctValidityUtilitaire">Validité CT Utilitaire (ans)</Label>
                    <Input id="ctValidityUtilitaire" type="number" defaultValue="1" />
                  </div>
                </div>
                <div className="flex items-center justify-between">
                  <div>
                    <Label htmlFor="autoMaintenanceCalc">Calcul automatique</Label>
                    <p className="text-sm text-gray-600">Calculer automatiquement les prochaines révisions</p>
                  </div>
                  <Switch id="autoMaintenanceCalc" defaultChecked />
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="voiture" className="space-y-6 mt-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Car className="w-5 h-5" />
                  Configuration véhicules
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="grid grid-cols-1 gap-6">
                  <div>
                    <Label htmlFor="criticalMileageLimit">Limite kilométrage critique (km)</Label>
                    <Input 
                      id="criticalMileageLimit" 
                      type="number" 
                      defaultValue="150000"
                      placeholder="Ex: 150000"
                    />
                    <p className="text-sm text-gray-600 mt-1">
                      Une notification "Kilométrage élevé" sera générée lorsqu'un véhicule dépasse cette limite
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="security" className="space-y-6 mt-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Shield className="w-5 h-5" />
                  Sécurité et confidentialité
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="flex items-center justify-between">
                  <div>
                    <Label htmlFor="twoFactor">Authentification à deux facteurs</Label>
                    <p className="text-sm text-gray-600">Sécurité renforcée pour la connexion</p>
                  </div>
                  <Switch id="twoFactor" />
                </div>
                <div className="flex items-center justify-between">
                  <div>
                    <Label htmlFor="dataEncryption">Chiffrement des données</Label>
                    <p className="text-sm text-gray-600">Chiffrer les données sensibles</p>
                  </div>
                  <Switch id="dataEncryption" defaultChecked />
                </div>
                <div className="grid grid-cols-2 gap-6">
                  <div>
                    <Label htmlFor="sessionTimeout">Timeout session (minutes)</Label>
                    <Input id="sessionTimeout" type="number" defaultValue="30" />
                  </div>
                  <div>
                    <Label htmlFor="passwordMinLength">Longueur mot de passe min</Label>
                    <Input id="passwordMinLength" type="number" defaultValue="8" />
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="export" className="space-y-6 mt-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Database className="w-5 h-5" />
                  Export et sauvegarde
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="grid grid-cols-1 gap-4">
                  <Button variant="outline" className="justify-start">
                    <FileText className="w-4 h-4 mr-2" />
                    Exporter toutes les données (CSV)
                  </Button>
                  <Button variant="outline" className="justify-start">
                    <FileText className="w-4 h-4 mr-2" />
                    Exporter rapport mensuel (PDF)
                  </Button>
                  <Button variant="outline" className="justify-start">
                    <Database className="w-4 h-4 mr-2" />
                    Sauvegarder la base de données
                  </Button>
                  <Button variant="outline" className="justify-start">
                    <Database className="w-4 h-4 mr-2" />
                    Restaurer une sauvegarde
                  </Button>
                </div>
                <div className="flex items-center justify-between">
                  <div>
                    <Label htmlFor="autoExport">Export automatique mensuel</Label>
                    <p className="text-sm text-gray-600">Générer automatiquement un rapport chaque mois</p>
                  </div>
                  <Switch id="autoExport" />
                </div>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
};

export default SettingsPage;
