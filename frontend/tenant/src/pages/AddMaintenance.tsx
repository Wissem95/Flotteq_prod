// Clients/src/pages/AddMaintenance.tsx

import React, { useState, useEffect } from "react";
import { addMaintenance } from "../services/maintenanceService";

import axios from "@/lib/api"; // ✅ Très important !
interface Vehicle {
  id: number;
  marque: string;
  modele: string;
  immatriculation: string;
}

const AddMaintenance: React.FC = () => {
  const [vehicles, setVehicles] = useState<Vehicle[]>([]);
  const [formData, setFormData] = useState({
    maintenance_date: "",
    maintenance_type: "",
    mileage: "",
    workshop: "",
    cost: "",
    description: "",
    vehicle_id: "",
    facture: null as File | null,
  });

  const [message, setMessage] = useState("");

  useEffect(() => {
    axios
      .get("/api/vehicles")
      .then((res) => {
        console.log("✅ Véhicules reçus :", res.data);
        // S'assurer que data est un tableau
        const vehicleArray = Array.isArray(res.data) ? res.data : (res.data.data || []);
        setVehicles(vehicleArray);
      })
      .catch((err) => {
        console.error("❌ Erreur récupération véhicules :", err);
        setVehicles([]);
      });
  }, []);

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>
  ) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0] || null;
    setFormData((prev) => ({ ...prev, facture: file }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const data = new FormData();
    Object.entries(formData).forEach(([key, value]) => {
      if (value) data.append(key, value);
    });

    try {
      await axios.post("/api/maintenances", data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      setMessage("✅ Maintenance enregistrée avec succès.");
      setFormData({
        maintenance_date: "",
        maintenance_type: "",
        mileage: "",
        workshop: "",
        cost: "",
        description: "",
        vehicle_id: "",
        facture: null,
      });
    } catch (err) {
      setMessage("❌ Erreur lors de l'enregistrement.");
    }
  };

  return (
    <div className="p-6 max-w-2xl mx-auto bg-white shadow rounded">
      <h2 className="text-2xl font-bold mb-4">Ajouter une maintenance</h2>
      {message && <p className="mb-4 text-sm text-blue-600">{message}</p>}
      <form onSubmit={handleSubmit} className="space-y-4">

        <input
          type="date"
          name="maintenance_date"
          value={formData.maintenance_date}
          onChange={handleChange}
          className="w-full border p-2 rounded"
          required
        />

        <select
          name="maintenance_type"
          value={formData.maintenance_type}
          onChange={handleChange}
          className="w-full border p-2 rounded"
          required
        >
          <option value="">Sélectionner le type</option>
          <option value="oil_change">Vidange</option>
          <option value="revision">Révision</option>
          <option value="tires">Pneus</option>
          <option value="brakes">Freins</option>
          <option value="belt">Courroie</option>
          <option value="filters">Filtres</option>
          <option value="other">Autre</option>
        </select>

        <select
          name="vehicle_id"
          value={formData.vehicle_id}
          onChange={handleChange}
          className="w-full border p-2 rounded"
          required
        >
          <option value="">Sélectionner un véhicule</option>
          {vehicles.map((v) => (
            <option key={v.id} value={v.id}>
              {v.marque} {v.modele} - {v.immatriculation}
            </option>
          ))}
        </select>

        <input
          type="text"
          name="workshop"
          value={formData.workshop}
          onChange={handleChange}
          placeholder="Garage/Atelier"
          className="w-full border p-2 rounded"
          required
        />

        <input
          type="number"
          name="mileage"
          value={formData.mileage}
          onChange={handleChange}
          placeholder="Kilométrage"
          className="w-full border p-2 rounded"
          required
        />

        <input
          type="number"
          step="0.01"
          name="cost"
          value={formData.cost}
          onChange={handleChange}
          placeholder="Montant (€)"
          className="w-full border p-2 rounded"
          required
        />

        <textarea
          name="description"
          value={formData.description}
          onChange={handleChange}
          placeholder="Description des travaux effectués"
          className="w-full border p-2 rounded"
          rows={3}
          required
        />

        <input
          type="file"
          name="facture"
          accept="application/pdf,image/*"
          onChange={handleFileChange}
          className="w-full border p-2 rounded"
        />

        <button
          type="submit"
          className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
          Enregistrer
        </button>
      </form>
    </div>
  );
};

export default AddMaintenance;

