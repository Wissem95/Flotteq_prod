
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { Shield, Plus, Edit, Trash2, Users, Settings } from 'lucide-react';

interface Permission {
  id: string;
  name: string;
  description: string;
  category: 'dashboard' | 'vehicles' | 'maintenance' | 'settings' | 'users';
}

interface Role {
  id: string;
  name: string;
  description: string;
  permissions: string[];
  userCount: number;
  isDefault: boolean;
}

const RoleManagementPage: React.FC = () => {
  const [roles, setRoles] = useState<Role[]>([
    {
      id: '1',
      name: 'Gestionnaire',
      description: 'Accès complet à toutes les fonctionnalités',
      permissions: ['all'],
      userCount: 1,
      isDefault: true,
    },
    {
      id: '2',
      name: 'Conducteur',
      description: 'Accès aux véhicules et alertes uniquement',
      permissions: ['dashboard.view', 'vehicles.view', 'vehicles.update', 'maintenance.view'],
      userCount: 3,
      isDefault: true,
    },
    {
      id: '3',
      name: 'Lecture seule',
      description: 'Consultation uniquement, aucune modification',
      permissions: ['dashboard.view', 'vehicles.view'],
      userCount: 0,
      isDefault: true,
    },
  ]);

  const availablePermissions: Permission[] = [
    // Dashboard
    { id: 'dashboard.view', name: 'Consulter le dashboard', description: 'Voir les données du tableau de bord', category: 'dashboard' },
    { id: 'dashboard.export', name: 'Exporter les données', description: 'Exporter les rapports et données', category: 'dashboard' },
    
    // Véhicules
    { id: 'vehicles.view', name: 'Consulter les véhicules', description: 'Voir la liste des véhicules', category: 'vehicles' },
    { id: 'vehicles.create', name: 'Ajouter des véhicules', description: 'Créer de nouveaux véhicules', category: 'vehicles' },
    { id: 'vehicles.update', name: 'Modifier les véhicules', description: 'Éditer les informations des véhicules', category: 'vehicles' },
    { id: 'vehicles.delete', name: 'Supprimer des véhicules', description: 'Supprimer des véhicules de la flotte', category: 'vehicles' },
    
    // Maintenance
    { id: 'maintenance.view', name: 'Consulter la maintenance', description: 'Voir les données de maintenance', category: 'maintenance' },
    { id: 'maintenance.create', name: 'Planifier la maintenance', description: 'Créer des interventions de maintenance', category: 'maintenance' },
    { id: 'maintenance.update', name: 'Modifier la maintenance', description: 'Éditer les interventions', category: 'maintenance' },
    
    // Paramètres
    { id: 'settings.view', name: 'Consulter les paramètres', description: 'Voir la configuration', category: 'settings' },
    { id: 'settings.update', name: 'Modifier les paramètres', description: 'Changer la configuration', category: 'settings' },
    
    // Utilisateurs
    { id: 'users.view', name: 'Consulter les utilisateurs', description: 'Voir la liste des utilisateurs', category: 'users' },
    { id: 'users.create', name: 'Ajouter des utilisateurs', description: 'Créer de nouveaux utilisateurs', category: 'users' },
    { id: 'users.update', name: 'Modifier les utilisateurs', description: 'Éditer les utilisateurs', category: 'users' },
    { id: 'users.delete', name: 'Supprimer des utilisateurs', description: 'Supprimer des utilisateurs', category: 'users' },
  ];

  const [newRole, setNewRole] = useState({
    name: '',
    description: '',
    permissions: [] as string[],
  });

  const [editingRole, setEditingRole] = useState<string | null>(null);
  const [isAddingRole, setIsAddingRole] = useState(false);

  const handleAddRole = () => {
    if (newRole.name && newRole.description) {
      const role: Role = {
        id: Date.now().toString(),
        ...newRole,
        userCount: 0,
        isDefault: false,
      };
      setRoles([...roles, role]);
      setNewRole({ name: '', description: '', permissions: [] });
      setIsAddingRole(false);
    }
  };

  const handleDeleteRole = (roleId: string) => {
    const role = roles.find(r => r.id === roleId);
    if (role && !role.isDefault) {
      setRoles(roles.filter(r => r.id !== roleId));
    }
  };

  const togglePermission = (permissionId: string) => {
    const updatedPermissions = newRole.permissions.includes(permissionId)
      ? newRole.permissions.filter(p => p !== permissionId)
      : [...newRole.permissions, permissionId];
    
    setNewRole({ ...newRole, permissions: updatedPermissions });
  };

  const getCategoryColor = (category: string) => {
    switch (category) {
      case 'dashboard': return 'bg-blue-100 text-blue-800';
      case 'vehicles': return 'bg-green-100 text-green-800';
      case 'maintenance': return 'bg-yellow-100 text-yellow-800';
      case 'settings': return 'bg-purple-100 text-purple-800';
      case 'users': return 'bg-pink-100 text-pink-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const groupedPermissions = availablePermissions.reduce((acc, permission) => {
    if (!acc[permission.category]) {
      acc[permission.category] = [];
    }
    acc[permission.category].push(permission);
    return acc;
  }, {} as Record<string, Permission[]>);

  return (
    <div className="flex-1 p-6 bg-gray-50">
      <div className="max-w-7xl mx-auto">
        <div className="flex items-center justify-between mb-8">
          <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-2">
            <Shield className="w-8 h-8" />
            Gestion des rôles
          </h1>
          <Button onClick={() => setIsAddingRole(true)}>
            <Plus className="w-4 h-4 mr-2" />
            Créer un rôle
          </Button>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          {/* Liste des rôles existants */}
          <Card>
            <CardHeader>
              <CardTitle>Rôles existants</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {roles.map((role) => (
                <div key={role.id} className="p-4 border rounded-lg">
                  <div className="flex items-center justify-between mb-2">
                    <div className="flex items-center gap-2">
                      <h3 className="font-medium">{role.name}</h3>
                      {role.isDefault && (
                        <Badge variant="outline" className="text-xs">Par défaut</Badge>
                      )}
                    </div>
                    <div className="flex items-center gap-2">
                      <Badge variant="outline">
                        <Users className="w-3 h-3 mr-1" />
                        {role.userCount}
                      </Badge>
                      {!role.isDefault && (
                        <>
                          <Button variant="outline" size="sm">
                            <Edit className="w-3 h-3" />
                          </Button>
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handleDeleteRole(role.id)}
                            className="text-red-600 hover:text-red-700"
                          >
                            <Trash2 className="w-3 h-3" />
                          </Button>
                        </>
                      )}
                    </div>
                  </div>
                  <p className="text-sm text-gray-600 mb-2">{role.description}</p>
                  <div className="text-xs text-gray-500">
                    {role.permissions.includes('all') ? (
                      <span className="text-green-600 font-medium">Toutes les permissions</span>
                    ) : (
                      <span>{role.permissions.length} permission(s) accordée(s)</span>
                    )}
                  </div>
                </div>
              ))}
            </CardContent>
          </Card>

          {/* Création d'un nouveau rôle */}
          {isAddingRole && (
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Plus className="w-5 h-5" />
                  Nouveau rôle
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-6">
                <div>
                  <Label htmlFor="roleName">Nom du rôle</Label>
                  <Input
                    id="roleName"
                    value={newRole.name}
                    onChange={(e) => setNewRole({ ...newRole, name: e.target.value })}
                    placeholder="Ex: Superviseur"
                  />
                </div>

                <div>
                  <Label htmlFor="roleDescription">Description</Label>
                  <Input
                    id="roleDescription"
                    value={newRole.description}
                    onChange={(e) => setNewRole({ ...newRole, description: e.target.value })}
                    placeholder="Description du rôle et de ses responsabilités"
                  />
                </div>

                <div>
                  <Label>Permissions</Label>
                  <div className="space-y-4 mt-2">
                    {Object.entries(groupedPermissions).map(([category, permissions]) => (
                      <div key={category}>
                        <h4 className="font-medium mb-2 flex items-center gap-2">
                          <Badge className={getCategoryColor(category)}>
                            {category.charAt(0).toUpperCase() + category.slice(1)}
                          </Badge>
                        </h4>
                        <div className="space-y-2 ml-4">
                          {permissions.map((permission) => (
                            <div key={permission.id} className="flex items-center justify-between">
                              <div>
                                <Label htmlFor={permission.id} className="text-sm">
                                  {permission.name}
                                </Label>
                                <p className="text-xs text-gray-500">{permission.description}</p>
                              </div>
                              <Switch
                                id={permission.id}
                                checked={newRole.permissions.includes(permission.id)}
                                onCheckedChange={() => togglePermission(permission.id)}
                              />
                            </div>
                          ))}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>

                <div className="flex gap-4">
                  <Button onClick={handleAddRole} className="flex-1">
                    <Plus className="w-4 h-4 mr-2" />
                    Créer le rôle
                  </Button>
                  <Button
                    variant="outline"
                    onClick={() => setIsAddingRole(false)}
                    className="flex-1"
                  >
                    Annuler
                  </Button>
                </div>
              </CardContent>
            </Card>
          )}
        </div>
      </div>
    </div>
  );
};

export default RoleManagementPage;
