import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { Users, Plus, Edit, Trash2, Shield, Eye, EyeOff } from 'lucide-react';

interface User {
  id: string;
  name: string;
  email: string;
  role: 'Gestionnaire' | 'Conducteur' | 'Lecture seule';
  restrictions: {
    canDeleteVehicles: boolean;
    canAccessSettings: boolean;
    canAccessMaintenance: boolean;
    canModifyData: boolean;
  };
  isActive: boolean;
  createdAt: string;
}

const UserManagementPage: React.FC = () => {
  const [users, setUsers] = useState<User[]>([
    {
      id: '1',
      name: 'Admin Principal',
      email: 'admin@flotteq.com',
      role: 'Gestionnaire',
      restrictions: {
        canDeleteVehicles: true,
        canAccessSettings: true,
        canAccessMaintenance: true,
        canModifyData: true,
      },
      isActive: true,
      createdAt: '2024-01-01',
    },
    {
      id: '2',
      name: 'Jean Dupont',
      email: 'jean.dupont@flotteq.com',
      role: 'Conducteur',
      restrictions: {
        canDeleteVehicles: false,
        canAccessSettings: false,
        canAccessMaintenance: true,
        canModifyData: false,
      },
      isActive: true,
      createdAt: '2024-02-15',
    },
  ]);

  const [newUser, setNewUser] = useState<{
    name: string;
    email: string;
    role: 'Gestionnaire' | 'Conducteur' | 'Lecture seule';
    restrictions: {
      canDeleteVehicles: boolean;
      canAccessSettings: boolean;
      canAccessMaintenance: boolean;
      canModifyData: boolean;
    };
  }>({
    name: '',
    email: '',
    role: 'Conducteur',
    restrictions: {
      canDeleteVehicles: false,
      canAccessSettings: false,
      canAccessMaintenance: false,
      canModifyData: false,
    }
  });

  const [isAddingUser, setIsAddingUser] = useState(false);
  const [editingUser, setEditingUser] = useState<string | null>(null);

  const handleAddUser = () => {
    if (newUser.name && newUser.email) {
      const user: User = {
        id: Date.now().toString(),
        ...newUser,
        isActive: true,
        createdAt: new Date().toISOString().split('T')[0],
      };
      setUsers([...users, user]);
      setNewUser({
        name: '',
        email: '',
        role: 'Conducteur',
        restrictions: {
          canDeleteVehicles: false,
          canAccessSettings: false,
          canAccessMaintenance: false,
          canModifyData: false,
        }
      });
      setIsAddingUser(false);
    }
  };

  const handleDeleteUser = (userId: string) => {
    setUsers(users.filter(user => user.id !== userId));
  };

  const handleToggleUserStatus = (userId: string) => {
    setUsers(users.map(user => 
      user.id === userId ? { ...user, isActive: !user.isActive } : user
    ));
  };

  const getRoleColor = (role: string) => {
    switch (role) {
      case 'Gestionnaire': return 'bg-green-100 text-green-800';
      case 'Conducteur': return 'bg-blue-100 text-blue-800';
      case 'Lecture seule': return 'bg-gray-100 text-gray-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <div className="flex-1 p-6 bg-gray-50">
      <div className="max-w-7xl mx-auto">
        <div className="flex items-center justify-between mb-8">
          <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-2">
            <Users className="w-8 h-8" />
            Gestion des utilisateurs
          </h1>
          <Button onClick={() => setIsAddingUser(true)}>
            <Plus className="w-4 h-4 mr-2" />
            Ajouter un utilisateur
          </Button>
        </div>

        <Tabs defaultValue="users" className="w-full">
          <TabsList>
            <TabsTrigger value="users">Utilisateurs</TabsTrigger>
            <TabsTrigger value="add-user">Nouvel utilisateur</TabsTrigger>
          </TabsList>

          <TabsContent value="users" className="space-y-6 mt-6">
            <Card>
              <CardHeader>
                <CardTitle>Liste des utilisateurs</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {users.map((user) => (
                    <div key={user.id} className="flex items-center justify-between p-4 border rounded-lg">
                      <div className="flex items-center space-x-4">
                        <div className="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                          <Users className="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                          <div className="flex items-center gap-2">
                            <h3 className="font-medium">{user.name}</h3>
                            <Badge className={getRoleColor(user.role)}>{user.role}</Badge>
                            {user.isActive ? (
                              <Badge variant="outline" className="text-green-600 border-green-600">
                                <Eye className="w-3 h-3 mr-1" />
                                Actif
                              </Badge>
                            ) : (
                              <Badge variant="outline" className="text-red-600 border-red-600">
                                <EyeOff className="w-3 h-3 mr-1" />
                                Inactif
                              </Badge>
                            )}
                          </div>
                          <p className="text-sm text-gray-600">{user.email}</p>
                          <p className="text-xs text-gray-500">Créé le {user.createdAt}</p>
                        </div>
                      </div>
                      <div className="flex items-center space-x-2">
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleToggleUserStatus(user.id)}
                        >
                          {user.isActive ? 'Désactiver' : 'Activer'}
                        </Button>
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => setEditingUser(user.id)}
                        >
                          <Edit className="w-4 h-4" />
                        </Button>
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleDeleteUser(user.id)}
                          className="text-red-600 hover:text-red-700"
                        >
                          <Trash2 className="w-4 h-4" />
                        </Button>
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="add-user" className="space-y-6 mt-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Plus className="w-5 h-5" />
                  Ajouter un nouvel utilisateur
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="grid grid-cols-2 gap-6">
                  <div>
                    <Label htmlFor="userName">Nom complet</Label>
                    <Input
                      id="userName"
                      value={newUser.name}
                      onChange={(e) => setNewUser({ ...newUser, name: e.target.value })}
                      placeholder="Nom de l'utilisateur"
                    />
                  </div>
                  <div>
                    <Label htmlFor="userEmail">Email</Label>
                    <Input
                      id="userEmail"
                      type="email"
                      value={newUser.email}
                      onChange={(e) => setNewUser({ ...newUser, email: e.target.value })}
                      placeholder="email@exemple.com"
                    />
                  </div>
                </div>

                <div>
                  <Label htmlFor="userRole">Rôle</Label>
                  <Select
                    value={newUser.role}
                    onValueChange={(value: 'Gestionnaire' | 'Conducteur' | 'Lecture seule') => 
                      setNewUser({ ...newUser, role: value })
                    }
                  >
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="Gestionnaire">Gestionnaire - Accès total</SelectItem>
                      <SelectItem value="Conducteur">Conducteur - Accès limité</SelectItem>
                      <SelectItem value="Lecture seule">Lecture seule - Consultation uniquement</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <Card>
                  <CardHeader>
                    <CardTitle className="text-lg flex items-center gap-2">
                      <Shield className="w-5 h-5" />
                      Restrictions d'accès
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div className="flex items-center justify-between">
                      <div>
                        <Label htmlFor="canDeleteVehicles">Suppression de véhicules</Label>
                        <p className="text-sm text-gray-600">Autoriser la suppression de véhicules</p>
                      </div>
                      <Switch
                        id="canDeleteVehicles"
                        checked={newUser.restrictions.canDeleteVehicles}
                        onCheckedChange={(checked) =>
                          setNewUser({
                            ...newUser,
                            restrictions: { ...newUser.restrictions, canDeleteVehicles: checked }
                          })
                        }
                      />
                    </div>

                    <div className="flex items-center justify-between">
                      <div>
                        <Label htmlFor="canAccessSettings">Accès aux paramètres</Label>
                        <p className="text-sm text-gray-600">Autoriser l'accès aux paramètres</p>
                      </div>
                      <Switch
                        id="canAccessSettings"
                        checked={newUser.restrictions.canAccessSettings}
                        onCheckedChange={(checked) =>
                          setNewUser({
                            ...newUser,
                            restrictions: { ...newUser.restrictions, canAccessSettings: checked }
                          })
                        }
                      />
                    </div>

                    <div className="flex items-center justify-between">
                      <div>
                        <Label htmlFor="canAccessMaintenance">Accès à l'entretien</Label>
                        <p className="text-sm text-gray-600">Autoriser l'accès au module d'entretien</p>
                      </div>
                      <Switch
                        id="canAccessMaintenance"
                        checked={newUser.restrictions.canAccessMaintenance}
                        onCheckedChange={(checked) =>
                          setNewUser({
                            ...newUser,
                            restrictions: { ...newUser.restrictions, canAccessMaintenance: checked }
                          })
                        }
                      />
                    </div>

                    <div className="flex items-center justify-between">
                      <div>
                        <Label htmlFor="canModifyData">Modification des données</Label>
                        <p className="text-sm text-gray-600">Autoriser la modification des données</p>
                      </div>
                      <Switch
                        id="canModifyData"
                        checked={newUser.restrictions.canModifyData}
                        onCheckedChange={(checked) =>
                          setNewUser({
                            ...newUser,
                            restrictions: { ...newUser.restrictions, canModifyData: checked }
                          })
                        }
                      />
                    </div>
                  </CardContent>
                </Card>

                <div className="flex gap-4">
                  <Button onClick={handleAddUser} className="flex-1">
                    <Plus className="w-4 h-4 mr-2" />
                    Créer l'utilisateur
                  </Button>
                  <Button
                    variant="outline"
                    onClick={() => setIsAddingUser(false)}
                    className="flex-1"
                  >
                    Annuler
                  </Button>
                </div>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
};

export default UserManagementPage;
