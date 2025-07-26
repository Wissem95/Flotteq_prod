import React, { useEffect, useState } from "react";
import axios from "@/lib/api";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { BarChart2, Car, Wrench, Euro } from "lucide-react";

interface Vehicle {
  id: number;
  statut: string;
}

interface StatistiquesData {
  totalVehicles: number;
  totalActive: number;
  totalInactive: number;
  totalMaintenance: number;
  totalKilometrage: number;
  totalEntretien: number;
  totalReparations: number;
  totalDepenses: number;
}

const Statistics: React.FC = () => {
  const [data, setData] = useState<StatistiquesData | null>(null);
  const [message, setMessage] = useState("");

  const fetchStats = async () => {
    try {
      const res = await axios.get("/vehicles/statistics");
      setData(res.data);
    } catch (err) {
      console.error("❌ Erreur lors du chargement :", err);
      setMessage("Erreur lors du chargement des statistiques.");
    }
  };

  useEffect(() => {
    fetchStats();
  }, []);

  return (
    <div className="max-w-6xl mx-auto p-6 space-y-6">
      <h2 className="text-2xl font-bold mb-4">Statistiques globales</h2>

      {message && <p className="text-red-500">{message}</p>}

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Car size={20} /> Véhicules
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p>Total : {data?.totalVehicles || 0}</p>
            <p>En service : {data?.totalActive || 0}</p>
            <p>Hors service : {data?.totalInactive || 0}</p>
            <p>En maintenance : {data?.totalMaintenance || 0}</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <BarChart2 size={20} /> Kilométrage total
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p>{data?.totalKilometrage?.toLocaleString() || 0} km</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Wrench size={20} /> Entretien & Réparations
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p>Entretiens : {data?.totalEntretien || 0}</p>
            <p>Réparations : {data?.totalReparations || 0}</p>
          </CardContent>
        </Card>

        <Card className="col-span-1 sm:col-span-2 lg:col-span-1">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Euro size={20} /> Dépenses totales
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p>{data?.totalDepenses?.toLocaleString(undefined, { minimumFractionDigits: 2 }) || "0.00"} €</p>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default Statistics;

