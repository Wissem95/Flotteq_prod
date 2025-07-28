// EmployeesOverview.tsx - Vue d'ensemble de la gestion des employés FlotteQ

import React, { useState, useEffect } from "react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  Plus,
  Search,
  Filter,
  Users,
  MapPin,
  Phone,
  Mail,
  MoreHorizontal,
  Edit,
  Eye,
  Key,
  UserX,
  Calendar,
  Building2,
  Award,
  TrendingUp,
  Home,
  Coffee,
  Globe,
} from "lucide-react";
import { Employee, EmployeeFilters, EmployeeStats } from "@/services/employeesService";

const EmployeesOverview: React.FC = () => {
  const [employees, setEmployees] = useState<Employee[]>([]);
  const [stats, setStats] = useState<EmployeeStats | null>(null);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState("");
  const [filters, setFilters] = useState<EmployeeFilters>({});
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  // Données simulées pour la démonstration
  const mockStats: EmployeeStats = {
    total: 42,
    by_department: {
      administration: 8,
      support: 12,
      development: 15,
      marketing: 4,
      partnerships: 3,
    },
    by_role: {
      super_admin: 2,
      admin: 5,
      support: 12,
      partner_manager: 3,
      analyst: 6,
      developer: 14,
    },
    by_status: {
      active: 38,
      inactive: 2,
      on_leave: 2,
      terminated: 0,
    },
    by_work_location: {
      office: 15,
      remote: 18,
      hybrid: 9,
    },
    average_tenure_months: 24,
    new_hires_this_month: 3,
    departures_this_month: 1,
  };

  const mockEmployees: Employee[] = [
    {
      id: 1,
      first_name: "Sophie",
      last_name: "Martin",
      email: "sophie.martin@flotteq.com",
      phone: "+33 6 12 34 56 78",
      role: "super_admin",
      department: "administration",
      position: "Directrice Technique",
      hire_date: "2022-01-15",
      status: "active",
      permissions: ["all"],
      avatar: "/images/profiles/sophie.jpg",
      contract_type: "cdi",
      work_location: "hybrid",
      skills: ["Management", "Strategy", "Technology"],
      certifications: ["PMP", "AWS Solutions Architect"],
      last_login: "2024-07-28T09:30:00Z",
      created_at: "2022-01-15T10:00:00Z",
      updated_at: "2024-07-28T09:30:00Z",
    },
    {
      id: 2,
      first_name: "Paul",
      last_name: "Bernard",
      email: "paul.bernard@flotteq.com",
      phone: "+33 6 23 45 67 89",
      role: "support",
      department: "support",
      position: "Responsable Support Client",
      hire_date: "2022-03-10",
      status: "active",
      permissions: ["support.read", "support.write", "tickets.manage"],
      manager_id: 1,
      manager_name: "Sophie Martin",
      contract_type: "cdi",
      work_location: "office",
      skills: ["Customer Service", "Problem Solving", "Communication"],
      last_login: "2024-07-28T08:45:00Z",
      created_at: "2022-03-10T14:00:00Z",
      updated_at: "2024-07-28T08:45:00Z",
    },
    {
      id: 3,
      first_name: "Marie",
      last_name: "Dubois",
      email: "marie.dubois@flotteq.com",
      phone: "+33 6 34 56 78 90",
      role: "developer",
      department: "development",
      position: "Développeuse Frontend Senior",
      hire_date: "2021-09-20",
      status: "active",
      permissions: ["development.read", "development.write", "deployment.staging"],
      manager_id: 1,
      manager_name: "Sophie Martin",
      contract_type: "cdi",
      work_location: "remote",
      skills: ["React", "TypeScript", "UI/UX", "Tailwind CSS"],
      certifications: ["React Professional"],
      last_login: "2024-07-28T10:15:00Z",
      created_at: "2021-09-20T09:00:00Z",
      updated_at: "2024-07-28T10:15:00Z",
    },
    {
      id: 4,
      first_name: "Thomas",
      last_name: "Leroy",
      email: "thomas.leroy@flotteq.com",
      phone: "+33 6 45 67 89 01",
      role: "partner_manager",
      department: "partnerships",
      position: "Responsable Partenariats",
      hire_date: "2023-01-15",
      status: "on_leave",
      permissions: ["partners.read", "partners.write", "contracts.manage"],
      manager_id: 1,
      manager_name: "Sophie Martin",
      contract_type: "cdi",
      work_location: "hybrid",
      skills: ["Business Development", "Negotiation", "CRM"],
      last_login: "2024-07-20T16:30:00Z",
      created_at: "2023-01-15T11:00:00Z",
      updated_at: "2024-07-20T16:30:00Z",
    },
  ];

  useEffect(() => {
    loadData();
  }, [currentPage, filters, searchTerm]);

  const loadData = async () => {
    setLoading(true);
    // TODO: Remplacer par de vrais appels API
    await new Promise(resolve => setTimeout(resolve, 500));
    
    let filteredEmployees = [...mockEmployees];
    
    // Filtrage par terme de recherche
    if (searchTerm) {
      filteredEmployees = filteredEmployees.filter(employee =>
        `${employee.first_name} ${employee.last_name}`.toLowerCase().includes(searchTerm.toLowerCase()) ||
        employee.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
        employee.position.toLowerCase().includes(searchTerm.toLowerCase())
      );
    }
    
    // Filtrage par rôle
    if (filters.role) {
      filteredEmployees = filteredEmployees.filter(employee => employee.role === filters.role);
    }
    
    // Filtrage par département
    if (filters.department) {
      filteredEmployees = filteredEmployees.filter(employee => employee.department === filters.department);
    }
    
    // Filtrage par statut
    if (filters.status) {
      filteredEmployees = filteredEmployees.filter(employee => employee.status === filters.status);
    }
    
    setEmployees(filteredEmployees);
    setStats(mockStats);
    setTotalPages(Math.ceil(filteredEmployees.length / 10));
    setLoading(false);
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'active':
        return <Badge className="bg-green-100 text-green-800">Actif</Badge>;
      case 'inactive':
        return <Badge className="bg-gray-100 text-gray-800">Inactif</Badge>;
      case 'on_leave':
        return <Badge className="bg-yellow-100 text-yellow-800">En congé</Badge>;
      case 'terminated':
        return <Badge className="bg-red-100 text-red-800">Terminé</Badge>;
      default:
        return <Badge variant="secondary">{status}</Badge>;
    }
  };

  const getRoleBadge = (role: string) => {
    switch (role) {
      case 'super_admin':
        return <Badge variant="destructive">Super Admin</Badge>;
      case 'admin':
        return <Badge className="bg-purple-100 text-purple-800">Admin</Badge>;
      case 'support':
        return <Badge className="bg-blue-100 text-blue-800">Support</Badge>;
      case 'partner_manager':
        return <Badge className="bg-orange-100 text-orange-800">Partenariats</Badge>;
      case 'analyst':
        return <Badge className="bg-teal-100 text-teal-800">Analyst</Badge>;
      case 'developer':
        return <Badge className="bg-green-100 text-green-800">Développeur</Badge>;
      default:
        return <Badge variant="secondary">{role}</Badge>;
    }
  };

  const getDepartmentLabel = (department: string) => {
    switch (department) {
      case 'administration':
        return 'Administration';
      case 'support':
        return 'Support';
      case 'development':
        return 'Développement';
      case 'marketing':
        return 'Marketing';
      case 'sales':
        return 'Ventes';
      case 'partnerships':
        return 'Partenariats';
      default:
        return department;
    }
  };

  const getWorkLocationIcon = (location: string) => {
    switch (location) {
      case 'office':
        return <Building2 className="w-3 h-3" />;
      case 'remote':
        return <Home className="w-3 h-3" />;
      case 'hybrid':
        return <Coffee className="w-3 h-3" />;
      default:
        return <Globe className="w-3 h-3" />;
    }
  };

  const getWorkLocationLabel = (location: string) => {
    switch (location) {
      case 'office':
        return 'Bureau';
      case 'remote':
        return 'Télétravail';
      case 'hybrid':
        return 'Hybride';
      default:
        return location;
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    });
  };

  const calculateTenure = (hireDate: string) => {
    const hired = new Date(hireDate);
    const now = new Date();
    const diffMonths = (now.getFullYear() - hired.getFullYear()) * 12 + now.getMonth() - hired.getMonth();
    
    if (diffMonths < 12) {
      return `${diffMonths} mois`;
    }
    const years = Math.floor(diffMonths / 12);
    const remainingMonths = diffMonths % 12;
    return remainingMonths > 0 ? `${years}a ${remainingMonths}m` : `${years} ans`;
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">Employés FlotteQ</h1>
          <p className="text-gray-600">Gestion des employés et permissions</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" className="flex items-center gap-2">
            <TrendingUp className="w-4 h-4" />
            Rapports RH
          </Button>
          <Button className="flex items-center gap-2">
            <Plus className="w-4 h-4" />
            Nouvel employé
          </Button>
        </div>
      </div>

      {/* Statistiques */}
      {stats && (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Employés actifs</CardTitle>
              <Users className="h-4 w-4 text-green-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">{stats.by_status.active}</div>
              <p className="text-xs text-gray-600 mt-1">
                Sur {stats.total} employés
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Télétravail</CardTitle>
              <Home className="h-4 w-4 text-blue-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-blue-600">{stats.by_work_location.remote}</div>
              <p className="text-xs text-gray-600 mt-1">
                {stats.by_work_location.hybrid} hybride
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Ancienneté moyenne</CardTitle>
              <Calendar className="h-4 w-4 text-purple-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-purple-600">{Math.floor(stats.average_tenure_months / 12)}a</div>
              <p className="text-xs text-gray-600 mt-1">
                {stats.average_tenure_months % 12} mois
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Nouvelles recrues</CardTitle>
              <Award className="h-4 w-4 text-orange-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-orange-600">{stats.new_hires_this_month}</div>
              <p className="text-xs text-gray-600 mt-1">Ce mois-ci</p>
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
            <div className="flex-1 min-w-[200px]">
              <div className="relative">
                <Search className="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                <Input
                  placeholder="Rechercher un employé..."
                  className="pl-10"
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                />
              </div>
            </div>
            
            <Select onValueChange={(value) => setFilters(prev => ({ ...prev, department: value === 'all' ? undefined : value }))}>
              <SelectTrigger className="w-[180px]">
                <SelectValue placeholder="Département" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Tous</SelectItem>
                <SelectItem value="administration">Administration</SelectItem>
                <SelectItem value="support">Support</SelectItem>
                <SelectItem value="development">Développement</SelectItem>
                <SelectItem value="marketing">Marketing</SelectItem>
                <SelectItem value="partnerships">Partenariats</SelectItem>
              </SelectContent>
            </Select>
            
            <Select onValueChange={(value) => setFilters(prev => ({ ...prev, role: value === 'all' ? undefined : value }))}>
              <SelectTrigger className="w-[150px]">
                <SelectValue placeholder="Rôle" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Tous</SelectItem>
                <SelectItem value="super_admin">Super Admin</SelectItem>
                <SelectItem value="admin">Admin</SelectItem>
                <SelectItem value="support">Support</SelectItem>
                <SelectItem value="developer">Développeur</SelectItem>
                <SelectItem value="analyst">Analyst</SelectItem>
              </SelectContent>
            </Select>
            
            <Select onValueChange={(value) => setFilters(prev => ({ ...prev, status: value === 'all' ? undefined : value as any }))}>
              <SelectTrigger className="w-[120px]">
                <SelectValue placeholder="Statut" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Tous</SelectItem>
                <SelectItem value="active">Actif</SelectItem>
                <SelectItem value="inactive">Inactif</SelectItem>
                <SelectItem value="on_leave">En congé</SelectItem>
              </SelectContent>
            </Select>
            
            <Button variant="outline" onClick={() => {
              setSearchTerm("");
              setFilters({});
            }}>
              Réinitialiser
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Liste des employés */}
      <Card>
        <CardHeader>
          <CardTitle>Équipe FlotteQ ({employees.length})</CardTitle>
          <CardDescription>
            Gestion des employés et de leurs permissions
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
                  <TableHead>Employé</TableHead>
                  <TableHead>Rôle</TableHead>
                  <TableHead>Département</TableHead>
                  <TableHead>Statut</TableHead>
                  <TableHead>Localisation</TableHead>
                  <TableHead>Ancienneté</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {employees.map((employee) => (
                  <TableRow key={employee.id}>
                    <TableCell>
                      <div className="flex items-center gap-3">
                        <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                          <span className="text-sm font-medium text-blue-800">
                            {employee.first_name[0]}{employee.last_name[0]}
                          </span>
                        </div>
                        <div>
                          <div className="font-medium">
                            {employee.first_name} {employee.last_name}
                          </div>
                          <div className="text-sm text-gray-600">{employee.position}</div>
                          <div className="flex items-center gap-1 text-xs text-gray-500">
                            <Mail className="w-3 h-3" />
                            <span>{employee.email}</span>
                          </div>
                        </div>
                      </div>
                    </TableCell>
                    <TableCell>
                      {getRoleBadge(employee.role)}
                    </TableCell>
                    <TableCell>
                      <span className="text-sm">{getDepartmentLabel(employee.department)}</span>
                      {employee.manager_name && (
                        <div className="text-xs text-gray-500 mt-1">
                          Manager: {employee.manager_name}
                        </div>
                      )}
                    </TableCell>
                    <TableCell>
                      {getStatusBadge(employee.status)}
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center gap-1">
                        {getWorkLocationIcon(employee.work_location)}
                        <span className="text-sm">{getWorkLocationLabel(employee.work_location)}</span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div>
                        <div className="text-sm">{calculateTenure(employee.hire_date)}</div>
                        <div className="text-xs text-gray-500">
                          Depuis {formatDate(employee.hire_date)}
                        </div>
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
                          <DropdownMenuItem>
                            <Eye className="w-4 h-4 mr-2" />
                            Voir le profil
                          </DropdownMenuItem>
                          <DropdownMenuItem>
                            <Edit className="w-4 h-4 mr-2" />
                            Modifier
                          </DropdownMenuItem>
                          <DropdownMenuItem>
                            <Key className="w-4 h-4 mr-2" />
                            Permissions
                          </DropdownMenuItem>
                          <DropdownMenuSeparator />
                          <DropdownMenuItem>
                            <Key className="w-4 h-4 mr-2" />
                            Réinitialiser mot de passe
                          </DropdownMenuItem>
                          {employee.status === 'active' && (
                            <DropdownMenuItem className="text-orange-600">
                              <UserX className="w-4 h-4 mr-2" />
                              Désactiver
                            </DropdownMenuItem>
                          )}
                        </DropdownMenuContent>
                      </DropdownMenu>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
          
          {employees.length === 0 && !loading && (
            <div className="text-center py-8">
              <div className="text-gray-400 mb-2">Aucun employé trouvé</div>
              <Button variant="outline">
                <Plus className="w-4 h-4 mr-2" />
                Ajouter le premier employé
              </Button>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Pagination */}
      {totalPages > 1 && (
        <div className="flex justify-center gap-2">
          <Button
            variant="outline"
            disabled={currentPage === 1}
            onClick={() => setCurrentPage(currentPage - 1)}
          >
            Précédent
          </Button>
          <span className="flex items-center px-4">
            Page {currentPage} sur {totalPages}
          </span>
          <Button
            variant="outline"
            disabled={currentPage === totalPages}
            onClick={() => setCurrentPage(currentPage + 1)}
          >
            Suivant
          </Button>
        </div>
      )}
    </div>
  );
};

export default EmployeesOverview; 