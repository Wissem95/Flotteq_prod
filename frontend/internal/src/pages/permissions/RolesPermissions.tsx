// RolesPermissions.tsx - Gestion des rôles et permissions FlotteQ

import React, { useState, useEffect } from "react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Switch } from "@/components/ui/switch";
import { Checkbox } from "@/components/ui/checkbox";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, } from "@/components/ui/dialog";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from "@/components/ui/table";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger, } from "@/components/ui/dropdown-menu";
import { Plus, Edit, Trash2, Users, Shield, Key, MoreHorizontal, Eye, UserCheck, Settings, Building2, } from "lucide-react";

interface Permission {
  id: string;
  name: string;
  description: string;
  category: string;
  resource: string;
  action: string;
}

interface Role {
  id: string;
  name: string;
  description: string;
  permissions: string[];
  users_count: number;
  is_system: boolean;
  created_at: string;
  updated_at: string;
}

interface User {
  id: number;
  name: string;
  email: string;
  role_id: string;
  role_name: string;
  status: 'active' | 'inactive';
  last_login: string;
}

const RolesPermissions: React.FC = () => {
  const [roles, setRoles] = useState<Role[]>([]);
  const [permissions, setPermissions] = useState<Permission[]>([]);
  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState(true);
  const [showCreateRoleModal, setShowCreateRoleModal] = useState(false);
  const [editingRole, setEditingRole] = useState<Role | null>(null);
  const [newRole, setNewRole] = useState({
    name: '',
    description: '',
    permissions: [] as string[]
  });

  // Permissions mockées organisées par catégorie
  const mockPermissions: Permission[] = [
    // Dashboard
    { id: "dashboard.view", name: "Voir le dashboard", description: "Accès à la vue d'ensemble", category: "Dashboard", resource: "dashboard", action: "view" },
    { id: "dashboard.analytics", name: "Analytics avancés", description: "Accès aux métriques détaillées", category: "Dashboard", resource: "dashboard", action: "analytics" },
    
    // Tenants
    { id: "tenants.view", name: "Voir les tenants", description: "Consulter la liste des tenants", category: "Tenants", resource: "tenants", action: "view" },
    { id: "tenants.create", name: "Créer des tenants", description: "Ajouter de nouveaux tenants", category: "Tenants", resource: "tenants", action: "create" },
    { id: "tenants.edit", name: "Modifier les tenants", description: "Éditer les informations des tenants", category: "Tenants", resource: "tenants", action: "edit" },
    { id: "tenants.delete", name: "Supprimer des tenants", description: "Supprimer des comptes tenants", category: "Tenants", resource: "tenants", action: "delete" },
    { id: "tenants.suspend", name: "Suspendre des tenants", description: "Suspendre temporairement des comptes", category: "Tenants", resource: "tenants", action: "suspend" },
    
    // Support
    { id: "support.view", name: "Voir les tickets", description: "Consulter les tickets de support", category: "Support", resource: "support", action: "view" },
    { id: "support.respond", name: "Répondre aux tickets", description: "Répondre aux demandes de support", category: "Support", resource: "support", action: "respond" },
    { id: "support.assign", name: "Assigner des tickets", description: "Assigner des tickets à des agents", category: "Support", resource: "support", action: "assign" },
    { id: "support.close", name: "Fermer des tickets", description: "Clôturer des tickets de support", category: "Support", resource: "support", action: "close" },
    
    // Employés
    { id: "employees.view", name: "Voir les employés", description: "Consulter la liste des employés", category: "Employés", resource: "employees", action: "view" },
    { id: "employees.create", name: "Créer des employés", description: "Ajouter de nouveaux employés", category: "Employés", resource: "employees", action: "create" },
    { id: "employees.edit", name: "Modifier les employés", description: "Éditer les profils d'employés", category: "Employés", resource: "employees", action: "edit" },
    { id: "employees.delete", name: "Supprimer des employés", description: "Supprimer des comptes d'employés", category: "Employés", resource: "employees", action: "delete" },
    
    // Partenaires
    { id: "partners.view", name: "Voir les partenaires", description: "Consulter la liste des partenaires", category: "Partenaires", resource: "partners", action: "view" },
    { id: "partners.create", name: "Créer des partenaires", description: "Ajouter de nouveaux partenaires", category: "Partenaires", resource: "partners", action: "create" },
    { id: "partners.edit", name: "Modifier les partenaires", description: "Éditer les informations des partenaires", category: "Partenaires", resource: "partners", action: "edit" },
    { id: "partners.approve", name: "Approuver des partenaires", description: "Valider les demandes de partenariat", category: "Partenaires", resource: "partners", action: "approve" },
    
    // Abonnements
    { id: "subscriptions.view", name: "Voir les abonnements", description: "Consulter les abonnements", category: "Abonnements", resource: "subscriptions", action: "view" },
    { id: "subscriptions.create", name: "Créer des abonnements", description: "Créer de nouveaux abonnements", category: "Abonnements", resource: "subscriptions", action: "create" },
    { id: "subscriptions.edit", name: "Modifier les abonnements", description: "Éditer les abonnements existants", category: "Abonnements", resource: "subscriptions", action: "edit" },
    { id: "subscriptions.cancel", name: "Annuler des abonnements", description: "Annuler des abonnements", category: "Abonnements", resource: "subscriptions", action: "cancel" },
    
    // Analytics
    { id: "analytics.view", name: "Voir les analytics", description: "Accès aux tableaux de bord analytics", category: "Analytics", resource: "analytics", action: "view" },
    { id: "analytics.export", name: "Exporter les données", description: "Exporter les rapports analytics", category: "Analytics", resource: "analytics", action: "export" },
    
    // Paramètres
    { id: "settings.view", name: "Voir les paramètres", description: "Consulter les paramètres système", category: "Paramètres", resource: "settings", action: "view" },
    { id: "settings.edit", name: "Modifier les paramètres", description: "Modifier la configuration système", category: "Paramètres", resource: "settings", action: "edit" },
    
    // Permissions
    { id: "permissions.manage", name: "Gérer les permissions", description: "Gérer les rôles et permissions", category: "Sécurité", resource: "permissions", action: "manage" },
  ];

  // Rôles mockés
  const mockRoles: Role[] = [
    {
      id: "super_admin",
      name: "Super Administrateur",
      description: "Accès complet à toutes les fonctionnalités de la plateforme",
      permissions: mockPermissions.map(p => p.id),
      users_count: 2,
      is_system: true,
      created_at: "2024-01-01",
      updated_at: "2024-07-20",
    },
    {
      id: "admin",
      name: "Administrateur",
      description: "Accès administratif avec restrictions sur les paramètres critiques",
      permissions: mockPermissions.filter(p => !['settings.edit', 'permissions.manage'].includes(p.id)).map(p => p.id),
      users_count: 5,
      is_system: true,
      created_at: "2024-01-01",
      updated_at: "2024-07-20",
    },
    {
      id: "support",
      name: "Support Client",
      description: "Accès aux fonctionnalités de support et consultation",
      permissions: [
        "dashboard.view",
        "tenants.view",
        "support.view", "support.respond", "support.assign", "support.close",
        "partners.view",
        "subscriptions.view",
      ],
      users_count: 8,
      is_system: true,
      created_at: "2024-01-01",
      updated_at: "2024-07-20",
    },
    {
      id: "partner_manager",
      name: "Gestionnaire Partenaires",
      description: "Gestion des partenaires et validation des demandes",
      permissions: [
        "dashboard.view",
        "partners.view", "partners.create", "partners.edit", "partners.approve",
        "support.view", "support.respond",
        "analytics.view",
      ],
      users_count: 3,
      is_system: false,
      created_at: "2024-02-15",
      updated_at: "2024-07-20",
    },
    {
      id: "analyst",
      name: "Analyste",
      description: "Accès en lecture aux analytics et rapports",
      permissions: [
        "dashboard.view", "dashboard.analytics",
        "tenants.view",
        "subscriptions.view",
        "analytics.view", "analytics.export",
      ],
      users_count: 4,
      is_system: false,
      created_at: "2024-03-01",
      updated_at: "2024-07-20",
    },
  ];

  // Utilisateurs mockés
  const mockUsers: User[] = [
    { id: 1, name: "John Doe", email: "john@flotteq.com", role_id: "super_admin", role_name: "Super Administrateur", status: "active", last_login: "2024-07-28T10:30:00Z" },
    { id: 2, name: "Jane Smith", email: "jane@flotteq.com", role_id: "admin", role_name: "Administrateur", status: "active", last_login: "2024-07-28T09:15:00Z" },
    { id: 3, name: "Mike Johnson", email: "mike@flotteq.com", role_id: "support", role_name: "Support Client", status: "active", last_login: "2024-07-28T11:45:00Z" },
    { id: 4, name: "Sarah Wilson", email: "sarah@flotteq.com", role_id: "partner_manager", role_name: "Gestionnaire Partenaires", status: "active", last_login: "2024-07-27T16:20:00Z" },
    { id: 5, name: "Tom Brown", email: "tom@flotteq.com", role_id: "analyst", role_name: "Analyste", status: "inactive", last_login: "2024-07-25T14:10:00Z" },
  ];

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    setLoading(true);
    try {
      // Simulation d'appels API
      await new Promise(resolve => setTimeout(resolve, 500));
      
      setPermissions(mockPermissions);
      setRoles(mockRoles);
      setUsers(mockUsers);
    } catch (error) {
      console.error("Erreur lors du chargement des données:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleCreateRole = async () => {
    try {
      const role: Role = {
        id: `role_${Date.now()}`,
        name: newRole.name,
        description: newRole.description,
        permissions: newRole.permissions,
        users_count: 0,
        is_system: false,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
      };
      
      setRoles(prev => [...prev, role]);
      setShowCreateRoleModal(false);
      setNewRole({ name: '', description: '', permissions: [] });
    } catch (error) {
      console.error("Erreur lors de la création du rôle:", error);
    }
  };

  const handleDeleteRole = async (roleId: string) => {
    if (roles.find(r => r.id === roleId)?.is_system) {
      alert("Impossible de supprimer un rôle système");
      return;
    }
    
    try {
      setRoles(prev => prev.filter(r => r.id !== roleId));
    } catch (error) {
      console.error("Erreur lors de la suppression du rôle:", error);
    }
  };

  const togglePermission = (permissionId: string) => {
    setNewRole(prev => ({
      ...prev,
      permissions: prev.permissions.includes(permissionId)
        ? prev.permissions.filter(id => id !== permissionId)
        : [...prev.permissions, permissionId]
    }));
  };

  const getRoleBadge = (role: Role) => {
    if (role.is_system) {
      return <Badge variant="secondary">Système</Badge>;
    }
    return <Badge variant="outline">Personnalisé</Badge>;
  };

  const getStatusBadge = (status: string) => {
    return status === 'active' 
      ? <Badge variant="default" className="bg-green-100 text-green-800">Actif</Badge>
      : <Badge variant="secondary">Inactif</Badge>;
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('fr-FR');
  };

  const permissionsByCategory = permissions.reduce((acc, permission) => {
    if (!acc[permission.category]) {
      acc[permission.category] = [];
    }
    acc[permission.category].push(permission);
    return acc;
  }, {} as Record<string, Permission[]>);

  return (
    <div className="space-y-6">
      {/* En-tête */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Rôles et Permissions</h1>
          <p className="text-gray-600">Gestion des accès et autorisations de la plateforme FlotteQ</p>
        </div>
        <Dialog open={showCreateRoleModal} onOpenChange={setShowCreateRoleModal}>
          <DialogTrigger asChild>
            <Button className="flex items-center gap-2">
              <Plus className="w-4 h-4" />
              Créer un rôle
            </Button>
          </DialogTrigger>
        </Dialog>
      </div>

      {/* Onglets */}
      <Tabs defaultValue="roles" className="space-y-4">
        <TabsList className="grid w-full grid-cols-3">
          <TabsTrigger value="roles">Rôles ({roles.length})</TabsTrigger>
          <TabsTrigger value="permissions">Permissions ({permissions.length})</TabsTrigger>
          <TabsTrigger value="users">Utilisateurs ({users.length})</TabsTrigger>
        </TabsList>

        {/* Rôles */}
        <TabsContent value="roles">
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {loading ? (
              <div className="col-span-full flex items-center justify-center h-64">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              </div>
            ) : (
              roles.map((role) => (
                <Card key={role.id}>
                  <CardHeader>
                    <div className="flex items-center justify-between">
                      <CardTitle className="text-lg">{role.name}</CardTitle>
                      <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                          <Button variant="ghost" size="icon">
                            <MoreHorizontal className="w-4 h-4" />
                          </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                          <DropdownMenuItem className="flex items-center gap-2">
                            <Eye className="w-4 h-4" />
                            Voir détails
                          </DropdownMenuItem>
                          {!role.is_system && (
                            <>
                              <DropdownMenuItem className="flex items-center gap-2">
                                <Edit className="w-4 h-4" />
                                Modifier
                              </DropdownMenuItem>
                              <DropdownMenuSeparator />
                              <DropdownMenuItem 
                                onClick={() => handleDeleteRole(role.id)}
                                className="flex items-center gap-2 text-red-600"
                              >
                                <Trash2 className="w-4 h-4" />
                                Supprimer
                              </DropdownMenuItem>
                            </>
                          )}
                        </DropdownMenuContent>
                      </DropdownMenu>
                    </div>
                    <CardDescription>{role.description}</CardDescription>
                  </CardHeader>
                  <CardContent className="space-y-3">
                    <div className="flex items-center justify-between">
                      <span className="text-sm text-gray-600">Type</span>
                      {getRoleBadge(role)}
                    </div>
                    
                    <div className="flex items-center justify-between">
                      <span className="text-sm text-gray-600">Utilisateurs</span>
                      <div className="flex items-center gap-1">
                        <Users className="w-4 h-4 text-gray-500" />
                        <span className="font-medium">{role.users_count}</span>
                      </div>
                    </div>
                    
                    <div className="flex items-center justify-between">
                      <span className="text-sm text-gray-600">Permissions</span>
                      <div className="flex items-center gap-1">
                        <Key className="w-4 h-4 text-gray-500" />
                        <span className="font-medium">{role.permissions.length}</span>
                      </div>
                    </div>
                    
                    <div className="text-xs text-gray-500">
                      Créé le {formatDate(role.created_at)}
                    </div>
                  </CardContent>
                </Card>
              ))
            )}
          </div>
        </TabsContent>

        {/* Permissions */}
        <TabsContent value="permissions">
          <div className="space-y-6">
            {Object.entries(permissionsByCategory).map(([category, categoryPermissions]) => (
              <Card key={category}>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Shield className="w-5 h-5" />
                    {category}
                  </CardTitle>
                  <CardDescription>
                    {categoryPermissions.length} permission(s) dans cette catégorie
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="grid gap-3 md:grid-cols-2">
                    {categoryPermissions.map((permission) => (
                      <div key={permission.id} className="flex items-start gap-3 p-3 border rounded-lg">
                        <div className="w-5 h-5 mt-0.5">
                          <Key className="w-4 h-4 text-gray-500" />
                        </div>
                        <div className="flex-1">
                          <div className="font-medium">{permission.name}</div>
                          <div className="text-sm text-gray-600">{permission.description}</div>
                          <div className="text-xs text-gray-500 mt-1">
                            {permission.resource}.{permission.action}
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        </TabsContent>

        {/* Utilisateurs */}
        <TabsContent value="users">
          <Card>
            <CardHeader>
              <CardTitle>Attribution des rôles</CardTitle>
              <CardDescription>Gestion des rôles assignés aux utilisateurs</CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Utilisateur</TableHead>
                    <TableHead>Rôle</TableHead>
                    <TableHead>Statut</TableHead>
                    <TableHead>Dernière connexion</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {users.map((user) => (
                    <TableRow key={user.id}>
                      <TableCell>
                        <div>
                          <div className="font-medium">{user.name}</div>
                          <div className="text-sm text-gray-500">{user.email}</div>
                        </div>
                      </TableCell>
                      <TableCell>
                        <Badge variant="outline">{user.role_name}</Badge>
                      </TableCell>
                      <TableCell>
                        {getStatusBadge(user.status)}
                      </TableCell>
                      <TableCell>
                        <div className="text-sm">
                          {new Date(user.last_login).toLocaleString('fr-FR')}
                        </div>
                      </TableCell>
                      <TableCell>
                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="icon">
                              <MoreHorizontal className="w-4 h-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <DropdownMenuItem className="flex items-center gap-2">
                              <UserCheck className="w-4 h-4" />
                              Changer le rôle
                            </DropdownMenuItem>
                            <DropdownMenuItem className="flex items-center gap-2">
                              <Eye className="w-4 h-4" />
                              Voir permissions
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>

      {/* Modal de création de rôle */}
      <Dialog open={showCreateRoleModal} onOpenChange={setShowCreateRoleModal}>
        <DialogContent className="max-w-4xl max-h-[80vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>Créer un nouveau rôle</DialogTitle>
            <DialogDescription>
              Configurez les permissions pour ce nouveau rôle
            </DialogDescription>
          </DialogHeader>
          
          <div className="space-y-4">
            <div className="grid gap-4 md:grid-cols-2">
              <div>
                <Label htmlFor="role_name">Nom du rôle</Label>
                <Input
                  id="role_name"
                  value={newRole.name}
                  onChange={(e) => setNewRole(prev => ({ ...prev, name: e.target.value }))}
                  placeholder="Ex: Gestionnaire Marketing"
                />
              </div>
              
              <div>
                <Label htmlFor="role_description">Description</Label>
                <Textarea
                  id="role_description"
                  value={newRole.description}
                  onChange={(e) => setNewRole(prev => ({ ...prev, description: e.target.value }))}
                  placeholder="Description du rôle et de ses responsabilités"
                  rows={3}
                />
              </div>
            </div>
            
            <div>
              <Label className="text-base font-medium">Permissions</Label>
              <p className="text-sm text-gray-600 mb-4">
                Sélectionnez les permissions à accorder à ce rôle
              </p>
              
              <div className="space-y-4">
                {Object.entries(permissionsByCategory).map(([category, categoryPermissions]) => (
                  <div key={category} className="border rounded-lg p-4">
                    <div className="flex items-center gap-2 mb-3">
                      <Shield className="w-4 h-4" />
                      <h4 className="font-medium">{category}</h4>
                      <Badge variant="outline" className="ml-auto">
                        {categoryPermissions.filter(p => newRole.permissions.includes(p.id)).length} / {categoryPermissions.length}
                      </Badge>
                    </div>
                    
                    <div className="grid gap-2 md:grid-cols-2">
                      {categoryPermissions.map((permission) => (
                        <div key={permission.id} className="flex items-center space-x-2">
                          <Checkbox
                            id={permission.id}
                            checked={newRole.permissions.includes(permission.id)}
                            onCheckedChange={() => togglePermission(permission.id)}
                          />
                          <Label htmlFor={permission.id} className="text-sm">
                            {permission.name}
                          </Label>
                        </div>
                      ))}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
          
          <DialogFooter>
            <Button variant="outline" onClick={() => setShowCreateRoleModal(false)}>
              Annuler
            </Button>
            <Button 
              onClick={handleCreateRole}
              disabled={!newRole.name || newRole.permissions.length === 0}
            >
              Créer le rôle
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default RolesPermissions; 