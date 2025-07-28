// PartnersOverview.tsx - Vue d'ensemble des partenaires FlotteQ

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
  MapPin,
  Phone,
  Mail,
  Globe,
  MoreHorizontal,
  Edit,
  Eye,
  Trash2,
  CheckCircle,
  XCircle,
  Clock,
  Wrench,
  Shield,
  Star,
} from "lucide-react";
import { Partner, PartnerFilters } from "@/services/partnersService";

const PartnersOverview: React.FC = () => {
  const [partners, setPartners] = useState<Partner[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState("");
  const [filters, setFilters] = useState<PartnerFilters>({});
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  // Données simulées
  const mockPartners: Partner[] = [
    {
      id: 1,
      name: "Garage Central",
      type: "garage",
      email: "contact@garagecentral.fr",
      phone: "01 23 45 67 89",
      website: "https://garagecentral.fr",
      status: "active",
      address: {
        street: "123 Rue de la République",
        city: "Paris",
        postal_code: "75001",
        country: "France",
        latitude: 48.8566,
        longitude: 2.3522,
      },
      services: ["Révision", "Réparation", "Pneus"],
      rating: { average: 4.5, total_reviews: 127 },
      created_at: "2024-01-15",
      updated_at: "2024-07-15",
      availability: {
        monday: { open: "08:00", close: "18:00" },
        tuesday: { open: "08:00", close: "18:00" },
        wednesday: { open: "08:00", close: "18:00" },
        thursday: { open: "08:00", close: "18:00" },
        friday: { open: "08:00", close: "18:00" },
        saturday: { open: "09:00", close: "17:00" },
        sunday: null,
      },
    },
    {
      id: 2,
      name: "AutoVision CT",
      type: "controle_technique",
      email: "rdv@autovision-ct.fr",
      phone: "01 98 76 54 32",
      status: "active",
      address: {
        street: "45 Avenue des Tilleuls",
        city: "Lyon",
        postal_code: "69000",
        country: "France",
        latitude: 45.7640,
        longitude: 4.8357,
      },
      services: ["Contrôle technique", "Contre-visite"],
      rating: { average: 4.2, total_reviews: 89 },
      created_at: "2024-02-20",
      updated_at: "2024-07-10",
      availability: {
        monday: { open: "08:00", close: "19:00" },
        tuesday: { open: "08:00", close: "19:00" },
        wednesday: { open: "08:00", close: "19:00" },
        thursday: { open: "08:00", close: "19:00" },
        friday: { open: "08:00", close: "19:00" },
        saturday: { open: "08:00", close: "16:00" },
        sunday: null,
      },
    },
    {
      id: 3,
      name: "AXA Assurances Pro",
      type: "assurance",
      email: "pro@axa.fr",
      phone: "08 00 12 34 56",
      website: "https://axa.fr/pro",
      status: "pending",
      address: {
        street: "10 Boulevard Haussmann",
        city: "Paris",
        postal_code: "75009",
        country: "France",
      },
      services: ["Assurance flotte", "Responsabilité civile", "Tous risques"],
      created_at: "2024-07-01",
      updated_at: "2024-07-25",
      availability: {
        monday: { open: "09:00", close: "18:00" },
        tuesday: { open: "09:00", close: "18:00" },
        wednesday: { open: "09:00", close: "18:00" },
        thursday: { open: "09:00", close: "18:00" },
        friday: { open: "09:00", close: "18:00" },
        saturday: null,
        sunday: null,
      },
    },
  ];

  useEffect(() => {
    loadPartners();
  }, [currentPage, filters, searchTerm]);

  const loadPartners = async () => {
    setLoading(true);
    // TODO: Remplacer par un vrai appel API
    await new Promise(resolve => setTimeout(resolve, 500));
    
    let filteredPartners = [...mockPartners];
    
    // Filtrage par terme de recherche
    if (searchTerm) {
      filteredPartners = filteredPartners.filter(partner =>
        partner.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        partner.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
        partner.address.city.toLowerCase().includes(searchTerm.toLowerCase())
      );
    }
    
    // Filtrage par type
    if (filters.type) {
      filteredPartners = filteredPartners.filter(partner => partner.type === filters.type);
    }
    
    // Filtrage par statut
    if (filters.status) {
      filteredPartners = filteredPartners.filter(partner => partner.status === filters.status);
    }
    
    setPartners(filteredPartners);
    setTotalPages(Math.ceil(filteredPartners.length / 10));
    setLoading(false);
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'active':
        return <Badge className="bg-green-100 text-green-800"><CheckCircle className="w-3 h-3 mr-1" />Actif</Badge>;
      case 'inactive':
        return <Badge className="bg-gray-100 text-gray-800"><XCircle className="w-3 h-3 mr-1" />Inactif</Badge>;
      case 'pending':
        return <Badge className="bg-yellow-100 text-yellow-800"><Clock className="w-3 h-3 mr-1" />En attente</Badge>;
      default:
        return <Badge variant="secondary">{status}</Badge>;
    }
  };

  const getTypeIcon = (type: string) => {
    switch (type) {
      case 'garage':
        return <Wrench className="w-4 h-4" />;
      case 'controle_technique':
        return <CheckCircle className="w-4 h-4" />;
      case 'assurance':
        return <Shield className="w-4 h-4" />;
      default:
        return null;
    }
  };

  const getTypeLabel = (type: string) => {
    switch (type) {
      case 'garage':
        return 'Garage';
      case 'controle_technique':
        return 'Contrôle Technique';
      case 'assurance':
        return 'Assurance';
      default:
        return type;
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">Partenaires</h1>
          <p className="text-gray-600">Gestion des partenaires FlotteQ</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" className="flex items-center gap-2">
            <MapPin className="w-4 h-4" />
            Carte interactive
          </Button>
          <Button className="flex items-center gap-2">
            <Plus className="w-4 h-4" />
            Ajouter un partenaire
          </Button>
        </div>
      </div>

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
                  placeholder="Rechercher un partenaire..."
                  className="pl-10"
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                />
              </div>
            </div>
            
            <Select onValueChange={(value) => setFilters(prev => ({ ...prev, type: value === 'all' ? undefined : value as any }))}>
              <SelectTrigger className="w-[180px]">
                <SelectValue placeholder="Type" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Tous les types</SelectItem>
                <SelectItem value="garage">Garages</SelectItem>
                <SelectItem value="controle_technique">Contrôle Technique</SelectItem>
                <SelectItem value="assurance">Assurances</SelectItem>
              </SelectContent>
            </Select>
            
            <Select onValueChange={(value) => setFilters(prev => ({ ...prev, status: value === 'all' ? undefined : value as any }))}>
              <SelectTrigger className="w-[150px]">
                <SelectValue placeholder="Statut" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Tous les statuts</SelectItem>
                <SelectItem value="active">Actif</SelectItem>
                <SelectItem value="inactive">Inactif</SelectItem>
                <SelectItem value="pending">En attente</SelectItem>
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

      {/* Liste des partenaires */}
      <Card>
        <CardHeader>
          <CardTitle>Liste des partenaires ({partners.length})</CardTitle>
          <CardDescription>
            Gérez tous vos partenaires depuis cette interface
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
                  <TableHead>Partenaire</TableHead>
                  <TableHead>Type</TableHead>
                  <TableHead>Localisation</TableHead>
                  <TableHead>Contact</TableHead>
                  <TableHead>Statut</TableHead>
                  <TableHead>Évaluation</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {partners.map((partner) => (
                  <TableRow key={partner.id}>
                    <TableCell>
                      <div>
                        <div className="font-medium">{partner.name}</div>
                        <div className="text-sm text-gray-600">
                          {partner.services.slice(0, 2).join(", ")}
                          {partner.services.length > 2 && ` +${partner.services.length - 2}`}
                        </div>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center gap-2">
                        {getTypeIcon(partner.type)}
                        <span>{getTypeLabel(partner.type)}</span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center gap-1">
                        <MapPin className="w-3 h-3 text-gray-400" />
                        <span className="text-sm">
                          {partner.address.city}, {partner.address.postal_code}
                        </span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="space-y-1">
                        <div className="flex items-center gap-1 text-sm">
                          <Mail className="w-3 h-3 text-gray-400" />
                          <span>{partner.email}</span>
                        </div>
                        <div className="flex items-center gap-1 text-sm">
                          <Phone className="w-3 h-3 text-gray-400" />
                          <span>{partner.phone}</span>
                        </div>
                      </div>
                    </TableCell>
                    <TableCell>
                      {getStatusBadge(partner.status)}
                    </TableCell>
                    <TableCell>
                      {partner.rating ? (
                        <div className="flex items-center gap-1">
                          <Star className="w-4 h-4 text-yellow-400 fill-current" />
                          <span className="text-sm">
                            {partner.rating.average} ({partner.rating.total_reviews})
                          </span>
                        </div>
                      ) : (
                        <span className="text-sm text-gray-400">Aucune évaluation</span>
                      )}
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
                            <MapPin className="w-4 h-4 mr-2" />
                            Voir sur la carte
                          </DropdownMenuItem>
                          {partner.website && (
                            <DropdownMenuItem>
                              <Globe className="w-4 h-4 mr-2" />
                              Visiter le site
                            </DropdownMenuItem>
                          )}
                          <DropdownMenuSeparator />
                          <DropdownMenuItem className="text-red-600">
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
          
          {partners.length === 0 && !loading && (
            <div className="text-center py-8">
              <div className="text-gray-400 mb-2">Aucun partenaire trouvé</div>
              <Button variant="outline">
                <Plus className="w-4 h-4 mr-2" />
                Ajouter le premier partenaire
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

export default PartnersOverview; 