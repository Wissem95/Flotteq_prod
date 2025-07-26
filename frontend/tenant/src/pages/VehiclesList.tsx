// 📁 src/pages/VehiclesList.tsx

import React, { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import {
  Calendar,
  Car,
  ExternalLink,
  MoreHorizontal,
  Search,
  BarChart2,        // j’utilise BarChart2 pour la stat
  // Tool,          // ← supprimé, ce n’existe pas dans lucide-react
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem,
} from "@/components/ui/select";
import { Badge } from "@/components/ui/badge";

import AddVehicleModal from "@/components/vehicles/AddVehicleModal";
import EditVehicleModal from "@/components/vehicles/EditVehicleModal";
import DeleteVehicleModal from "@/components/vehicles/DeleteVehicleModal";
import HistoryVehicleModal from "@/components/vehicles/HistoryVehicleModal";

import { fetchVehicles, updateVehicleStatus, Vehicle } from "@/services/vehicleService";
import StatusDropdown from "@/components/vehicles/StatusDropdown";

const VehiclesList: React.FC = () => {
  const [vehicles, setVehicles] = useState<Vehicle[]>([]);
  const [searchTerm, setSearchTerm] = useState("");
  const [statusFilter, setStatusFilter] = useState<"all" | string>("all");

  const [showAddModal, setShowAddModal] = useState(false);
  const [editVehicleId, setEditVehicleId] = useState<number | null>(null);
  const [deleteVehicleId, setDeleteVehicleId] = useState<number | null>(null);
  const [historyVehicleId, setHistoryVehicleId] = useState<number | null>(null);

  const loadVehicles = async () => {
    try {
      console.log("🔄 Rechargement de la liste des véhicules...");
      const data = await fetchVehicles();
      console.log("📋 Véhicules chargés :", data);
      console.log("📋 Détail des véhicules :");
      data.forEach((v, i) => {
        console.log(`  [${i}] ID: ${v.id} - ${v.marque} ${v.modele} (${v.immatriculation}) - Année: ${v.annee}`);
      });
      setVehicles(Array.isArray(data) ? data : []);
    } catch (err) {
      console.error("❌ Erreur chargement véhicules :", err);
      setVehicles([]);
    }
  };

  useEffect(() => {
    loadVehicles();
  }, []);

  const filtered = vehicles.filter((v) => {
    const txt = `${v.marque} ${v.modele} ${v.immatriculation}`.toLowerCase();
    const okSearch = txt.includes(searchTerm.toLowerCase());
    const okStatus = statusFilter === "all" || v.status === statusFilter;
    return okSearch && okStatus;
  });

  const fmtDate = (d?: string | null) =>
    d ? new Intl.DateTimeFormat("fr-FR").format(new Date(d)) : "-";

  const isCtSoon = (d?: string | null) => {
    if (!d) return false;
    const diff = (new Date(d).getTime() - Date.now()) / (1000*60*60*24);
    return diff <= 30;
  };

  // Fonction pour mettre à jour le statut d'un véhicule
  const handleStatusChange = async (vehicleId: number, newStatus: string) => {
    try {
      await updateVehicleStatus(vehicleId, newStatus);
      // Recharger la liste des véhicules
      setVehicles((prevVehicles) =>
        prevVehicles.map((v) =>
          v.id === vehicleId ? { ...v, status: newStatus } : v
        )
      );
    } catch (error) {
      console.error("Erreur lors du changement de statut:", error);
    }
  };

  return (
    <div className="space-y-6 p-4">
      {/* Recherche + filtre + bouton Ajouter */}
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div className="relative flex-1 max-w-md">
          <Search
            size={18}
            className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
          />
          <Input
            className="pl-10"
            placeholder="Rechercher marque, modèle, plaque…"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>
        <div className="flex items-center gap-2">
          <Select
            value={statusFilter}
            onValueChange={(v) => setStatusFilter(v)}
          >
            <SelectTrigger className="w-[140px]">
              <SelectValue placeholder="État" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">Tous</SelectItem>
              <SelectItem value="active">En service</SelectItem>
              <SelectItem value="maintenance">En maintenance</SelectItem>
              <SelectItem value="inactive">Hors service</SelectItem>
              <SelectItem value="warning">Attention requise</SelectItem>
            </SelectContent>
          </Select>
          {/* Statistiques (BarChart2) */}
          <Button variant="outline" size="icon">
            <BarChart2 size={18} />
          </Button>
          <Button
            onClick={() => setShowAddModal(true)}
            className="flex items-center bg-flotteq-blue hover:bg-flotteq-navy"
          >
            <Car size={18} className="mr-2" />
            Ajouter un véhicule
          </Button>
        </div>
      </div>

      {/* Tableau */}
      <div className="overflow-x-auto bg-white rounded-lg border">
        <table className="w-full text-sm">
          <thead className="bg-slate-50 text-slate-600">
            <tr>
              <th className="py-3 px-4 text-left">Marque / Modèle</th>
              <th className="py-3 px-4 text-left">Immatriculation</th>
              <th className="py-3 px-4 text-left">État</th>
              <th className="py-3 px-4 text-left">Prochain CT</th>
              <th className="py-3 px-4 text-left">KM</th>
              <th className="py-3 px-4 text-center">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100">
            {filtered.length === 0 ? (
              <tr>
                <td colSpan={6} className="py-6 text-center text-slate-500">
                  Aucun véhicule trouvé.
                </td>
              </tr>
            ) : (
              filtered.map((v) => (
                <tr key={v.id}>
                  <td className="py-3 px-4 font-medium">
                    {v.marque} / {v.modele}
                  </td>
                  <td className="py-3 px-4 font-medium">{v.immatriculation}</td>
                  <td className="py-3 px-4">
                    <StatusDropdown 
                      currentStatus={v.status || "active"}
                      vehicleId={v.id}
                      vehicleInfo={{
                        marque: v.marque,
                        modele: v.modele,
                        immatriculation: v.immatriculation
                      }}
                      onStatusChange={handleStatusChange}
                    />
                  </td>
                  <td className="py-3 px-4">
                    <Calendar
                      size={16}
                      className="inline mr-1 text-slate-500"
                    />
                    -
                  </td>
                  <td className="py-3 px-4">
                    {v.kilometrage?.toLocaleString("fr-FR")} km
                  </td>
                  <td className="py-3 px-4 text-center flex justify-center gap-2">
                    <Link to={`/vehicle/${v.id}`}>
                      <Button variant="ghost" size="sm">
                        <ExternalLink size={16} />
                      </Button>
                    </Link>
                    <DropdownMenu>
                      <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="sm">
                          <MoreHorizontal size={16} />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end">
                        <DropdownMenuItem onClick={() => setEditVehicleId(v.id)}>
                          ✏️ Modifier
                        </DropdownMenuItem>
                        <DropdownMenuItem
                          onClick={() => setHistoryVehicleId(v.id)}
                        >
                          📜 Historique
                        </DropdownMenuItem>
                        <DropdownMenuItem
                          className="text-red-500"
                          onClick={() => setDeleteVehicleId(v.id)}
                        >
                          🗑️ Supprimer
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {/* Modales */}
      {showAddModal && (
        <AddVehicleModal
          isOpen={showAddModal}
          onClose={() => setShowAddModal(false)}
          onCreated={() => {
            loadVehicles();
            setShowAddModal(false);
          }}
        />
      )}
      {editVehicleId != null && (
        <EditVehicleModal
          isOpen={!!editVehicleId}
          onClose={() => setEditVehicleId(null)}
          vehicleId={editVehicleId}
          onUpdated={() => {
            console.log("🔄 Véhicule modifié, rechargement de la liste...");
            loadVehicles();
            setEditVehicleId(null);
          }}
        />
      )}
      {deleteVehicleId != null && (
        <DeleteVehicleModal
  isOpen={deleteVehicleId != null}
  onClose={() => setDeleteVehicleId(null)}
  vehicleId={deleteVehicleId!}
  onDeleted={() => {
    loadVehicles();                // ← on recharge la liste
    setDeleteVehicleId(null);
  }}
/>
      )}
      {historyVehicleId != null && (
        <HistoryVehicleModal
          isOpen={!!historyVehicleId}
          onClose={() => setHistoryVehicleId(null)}
          vehicleId={historyVehicleId}
        />
      )}
    </div>
  );
};

export default VehiclesList;

