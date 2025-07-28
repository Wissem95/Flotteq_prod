// SupportDashboard.tsx - Tableau de bord principal du support client FlotteQ

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
  Clock,
  AlertTriangle,
  CheckCircle,
  XCircle,
  MoreHorizontal,
  Eye,
  Edit,
  User,
  MessageSquare,
  Calendar,
  TrendingUp,
  Building2,
  Mail,
} from "lucide-react";
import { SupportTicket, SupportFilters, SupportStats } from "@/services/supportService";

const SupportDashboard: React.FC = () => {
  const [tickets, setTickets] = useState<SupportTicket[]>([]);
  const [stats, setStats] = useState<SupportStats | null>(null);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState("");
  const [filters, setFilters] = useState<SupportFilters>({});
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  // Données simulées pour la démonstration
  const mockStats: SupportStats = {
    total: 156,
    by_status: {
      open: 23,
      in_progress: 31,
      resolved: 67,
      closed: 35,
    },
    by_priority: {
      low: 45,
      medium: 78,
      high: 28,
      urgent: 5,
    },
    average_response_time_hours: 2.4,
    average_resolution_time_hours: 18.6,
    open_tickets_older_than_24h: 8,
    satisfaction_rating: 4.2,
  };

  const mockTickets: SupportTicket[] = [
    {
      id: 1,
      ticket_number: "SUP-2024-001",
      subject: "Problème de connexion API",
      description: "Impossible de se connecter à l'API depuis hier matin...",
      status: "open",
      priority: "high",
      category: "technical",
      tenant_id: 1,
      tenant_name: "Entreprise ABC",
      user_id: 123,
      user_name: "Jean Dupont",
      user_email: "jean.dupont@abc.com",
      assigned_to: 1,
      assigned_to_name: "Sophie Martin",
      messages: [],
      tags: ["api", "connexion"],
      created_at: "2024-07-28T08:30:00Z",
      updated_at: "2024-07-28T10:15:00Z",
      response_time_hours: 1.75,
    },
    {
      id: 2,
      ticket_number: "SUP-2024-002",
      subject: "Question sur la facturation",
      description: "Je souhaiterais comprendre les détails de ma facture du mois dernier...",
      status: "in_progress",
      priority: "medium",
      category: "billing",
      tenant_id: 2,
      tenant_name: "Transport XYZ",
      user_id: 456,
      user_name: "Marie Leroy",
      user_email: "marie.leroy@xyz.com",
      assigned_to: 2,
      assigned_to_name: "Paul Bernard",
      messages: [],
      tags: ["facturation"],
      created_at: "2024-07-27T14:20:00Z",
      updated_at: "2024-07-28T09:45:00Z",
      response_time_hours: 0.5,
    },
    {
      id: 3,
      ticket_number: "SUP-2024-003",
      subject: "Demande de fonctionnalité - Export PDF",
      description: "Serait-il possible d'ajouter un export PDF pour les rapports de maintenance ?",
      status: "resolved",
      priority: "low",
      category: "feature_request",
      tenant_id: 3,
      tenant_name: "FleetCorp",
      user_id: 789,
      user_name: "Pierre Moreau",
      user_email: "pierre.moreau@fleetcorp.com",
      assigned_to: 1,
      assigned_to_name: "Sophie Martin",
      messages: [],
      tags: ["fonctionnalité", "export", "pdf"],
      created_at: "2024-07-25T11:10:00Z",
      updated_at: "2024-07-27T16:30:00Z",
      resolved_at: "2024-07-27T16:30:00Z",
      response_time_hours: 4.2,
      resolution_time_hours: 53.3,
    },
  ];

  useEffect(() => {
    loadData();
  }, [currentPage, filters, searchTerm]);

  const loadData = async () => {
    setLoading(true);
    // TODO: Remplacer par de vrais appels API
    await new Promise(resolve => setTimeout(resolve, 500));
    
    let filteredTickets = [...mockTickets];
    
    // Filtrage par terme de recherche
    if (searchTerm) {
      filteredTickets = filteredTickets.filter(ticket =>
        ticket.subject.toLowerCase().includes(searchTerm.toLowerCase()) ||
        ticket.ticket_number.toLowerCase().includes(searchTerm.toLowerCase()) ||
        ticket.user_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        ticket.tenant_name.toLowerCase().includes(searchTerm.toLowerCase())
      );
    }
    
    // Filtrage par statut
    if (filters.status) {
      filteredTickets = filteredTickets.filter(ticket => ticket.status === filters.status);
    }
    
    // Filtrage par priorité
    if (filters.priority) {
      filteredTickets = filteredTickets.filter(ticket => ticket.priority === filters.priority);
    }
    
    setTickets(filteredTickets);
    setStats(mockStats);
    setTotalPages(Math.ceil(filteredTickets.length / 10));
    setLoading(false);
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'open':
        return <Badge className="bg-red-100 text-red-800"><AlertTriangle className="w-3 h-3 mr-1" />Ouvert</Badge>;
      case 'in_progress':
        return <Badge className="bg-blue-100 text-blue-800"><Clock className="w-3 h-3 mr-1" />En cours</Badge>;
      case 'resolved':
        return <Badge className="bg-green-100 text-green-800"><CheckCircle className="w-3 h-3 mr-1" />Résolu</Badge>;
      case 'closed':
        return <Badge className="bg-gray-100 text-gray-800"><XCircle className="w-3 h-3 mr-1" />Fermé</Badge>;
      default:
        return <Badge variant="secondary">{status}</Badge>;
    }
  };

  const getPriorityBadge = (priority: string) => {
    switch (priority) {
      case 'urgent':
        return <Badge variant="destructive">Urgent</Badge>;
      case 'high':
        return <Badge className="bg-orange-100 text-orange-800">Élevée</Badge>;
      case 'medium':
        return <Badge className="bg-yellow-100 text-yellow-800">Moyenne</Badge>;
      case 'low':
        return <Badge className="bg-green-100 text-green-800">Faible</Badge>;
      default:
        return <Badge variant="secondary">{priority}</Badge>;
    }
  };

  const getCategoryLabel = (category: string) => {
    switch (category) {
      case 'technical':
        return 'Technique';
      case 'billing':
        return 'Facturation';
      case 'general':
        return 'Général';
      case 'feature_request':
        return 'Fonctionnalité';
      case 'bug_report':
        return 'Bug';
      default:
        return category;
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const getTimeSinceCreation = (dateString: string) => {
    const created = new Date(dateString);
    const now = new Date();
    const diffHours = Math.floor((now.getTime() - created.getTime()) / (1000 * 60 * 60));
    
    if (diffHours < 1) return "< 1h";
    if (diffHours < 24) return `${diffHours}h`;
    const diffDays = Math.floor(diffHours / 24);
    return `${diffDays}j`;
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">Support Client</h1>
          <p className="text-gray-600">Gestion des tickets et support utilisateurs</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" className="flex items-center gap-2">
            <TrendingUp className="w-4 h-4" />
            Rapports
          </Button>
          <Button className="flex items-center gap-2">
            <Plus className="w-4 h-4" />
            Nouveau ticket
          </Button>
        </div>
      </div>

      {/* Statistiques */}
      {stats && (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Tickets ouverts</CardTitle>
              <AlertTriangle className="h-4 w-4 text-red-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-red-600">{stats.by_status.open}</div>
                             <p className="text-xs text-gray-600 mt-1">
                 {stats.open_tickets_older_than_24h} &gt; 24h
               </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">En cours</CardTitle>
              <Clock className="h-4 w-4 text-blue-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-blue-600">{stats.by_status.in_progress}</div>
              <p className="text-xs text-gray-600 mt-1">En traitement</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Temps de réponse</CardTitle>
              <MessageSquare className="h-4 w-4 text-green-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">{stats.average_response_time_hours}h</div>
              <p className="text-xs text-gray-600 mt-1">Moyenne</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Satisfaction</CardTitle>
              <CheckCircle className="h-4 w-4 text-yellow-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-yellow-600">{stats.satisfaction_rating}/5</div>
              <p className="text-xs text-gray-600 mt-1">Note moyenne</p>
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
                  placeholder="Rechercher un ticket..."
                  className="pl-10"
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                />
              </div>
            </div>
            
            <Select onValueChange={(value) => setFilters(prev => ({ ...prev, status: value === 'all' ? undefined : value as any }))}>
              <SelectTrigger className="w-[150px]">
                <SelectValue placeholder="Statut" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Tous les statuts</SelectItem>
                <SelectItem value="open">Ouvert</SelectItem>
                <SelectItem value="in_progress">En cours</SelectItem>
                <SelectItem value="resolved">Résolu</SelectItem>
                <SelectItem value="closed">Fermé</SelectItem>
              </SelectContent>
            </Select>
            
            <Select onValueChange={(value) => setFilters(prev => ({ ...prev, priority: value === 'all' ? undefined : value as any }))}>
              <SelectTrigger className="w-[150px]">
                <SelectValue placeholder="Priorité" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Toutes</SelectItem>
                <SelectItem value="urgent">Urgent</SelectItem>
                <SelectItem value="high">Élevée</SelectItem>
                <SelectItem value="medium">Moyenne</SelectItem>
                <SelectItem value="low">Faible</SelectItem>
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

      {/* Liste des tickets */}
      <Card>
        <CardHeader>
          <CardTitle>Tickets récents ({tickets.length})</CardTitle>
          <CardDescription>
            Derniers tickets de support client
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
                  <TableHead>Ticket</TableHead>
                  <TableHead>Client</TableHead>
                  <TableHead>Statut</TableHead>
                  <TableHead>Priorité</TableHead>
                  <TableHead>Assigné à</TableHead>
                  <TableHead>Créé</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {tickets.map((ticket) => (
                  <TableRow key={ticket.id}>
                    <TableCell>
                      <div>
                        <div className="font-medium">{ticket.ticket_number}</div>
                        <div className="text-sm text-gray-600 truncate max-w-xs">
                          {ticket.subject}
                        </div>
                        <div className="text-xs text-gray-500 mt-1">
                          {getCategoryLabel(ticket.category)}
                        </div>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div>
                        <div className="font-medium text-sm">{ticket.user_name}</div>
                        <div className="flex items-center gap-1 text-xs text-gray-600">
                          <Building2 className="w-3 h-3" />
                          <span>{ticket.tenant_name}</span>
                        </div>
                        <div className="flex items-center gap-1 text-xs text-gray-500">
                          <Mail className="w-3 h-3" />
                          <span>{ticket.user_email}</span>
                        </div>
                      </div>
                    </TableCell>
                    <TableCell>
                      {getStatusBadge(ticket.status)}
                    </TableCell>
                    <TableCell>
                      {getPriorityBadge(ticket.priority)}
                    </TableCell>
                    <TableCell>
                      {ticket.assigned_to_name ? (
                        <div className="flex items-center gap-1">
                          <User className="w-3 h-3 text-gray-400" />
                          <span className="text-sm">{ticket.assigned_to_name}</span>
                        </div>
                      ) : (
                        <span className="text-sm text-gray-400">Non assigné</span>
                      )}
                    </TableCell>
                    <TableCell>
                      <div>
                        <div className="text-sm">{formatDate(ticket.created_at)}</div>
                        <div className="text-xs text-gray-500">
                          Il y a {getTimeSinceCreation(ticket.created_at)}
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
                            Voir les détails
                          </DropdownMenuItem>
                          <DropdownMenuItem>
                            <Edit className="w-4 h-4 mr-2" />
                            Modifier
                          </DropdownMenuItem>
                          <DropdownMenuItem>
                            <User className="w-4 h-4 mr-2" />
                            Assigner
                          </DropdownMenuItem>
                          <DropdownMenuSeparator />
                          <DropdownMenuItem>
                            <MessageSquare className="w-4 h-4 mr-2" />
                            Répondre
                          </DropdownMenuItem>
                        </DropdownMenuContent>
                      </DropdownMenu>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
          
          {tickets.length === 0 && !loading && (
            <div className="text-center py-8">
              <div className="text-gray-400 mb-2">Aucun ticket trouvé</div>
              <Button variant="outline">
                <Plus className="w-4 h-4 mr-2" />
                Créer le premier ticket
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

export default SupportDashboard; 