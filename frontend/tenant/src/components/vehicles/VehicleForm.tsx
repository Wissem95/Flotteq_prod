// 📁 src/components/vehicles/VehicleForm.tsx
import React, { useState, useEffect, ChangeEvent } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Separator } from "@/components/ui/separator";
import { Save } from "lucide-react";
import {
  createVehicle,
  updateVehicle,
  Vehicle,
  VehiclePayload,
} from "@/services/vehicleService";
import DatePicker from "@/components/DatePicker";

interface VehicleFormProps {
  initialData?: Vehicle;
  onSuccess?: () => void;
  onCancel?: () => void;
}

export const VehicleForm: React.FC<VehicleFormProps> = ({
  initialData,
  onSuccess,
  onCancel,
}) => {
  const isEditing = Boolean(initialData);
  console.log("🔍 Mode édition détecté :", isEditing, "initialData présent :", !!initialData);
  const [isFunction, setIsFunction] = useState(
    initialData?.status === "function"
  );

  const [formData, setFormData] = useState<
    VehiclePayload & { photos: File[]; documents: File[]; driver?: string; lastCT?: string | null; nextCT?: string | null; }
  >({
    immatriculation: "",
    marque: "",
    modele: "",
    vin: null,
    annee: null,
    kilometrage: null,
    carburant: "",
    transmission: "",
    couleur: null,
    puissance: null,
    purchase_date: null,
    purchase_price: null,
    lastCT: null,
    nextCT: null,
    driver: null,
    status: isFunction ? "active" : "active",
    notes: null,
    photos: [],
    documents: [],
  });

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // Initialisation en édition
  useEffect(() => {
    if (initialData) {
      console.log("🚗 Initialisation du formulaire avec les données :", initialData);
      setIsFunction(initialData.status === "active");
      setFormData({
        immatriculation: initialData.immatriculation || "",
        marque: initialData.marque || "",
        modele: initialData.modele || "",
        vin: initialData.vin || "",
        annee: initialData.annee || null,
        kilometrage: initialData.kilometrage || null,
        carburant: initialData.carburant || "",
        transmission: initialData.transmission || "",
        couleur: initialData.couleur || "",
        puissance: initialData.puissance || null,
        purchase_date: initialData.purchase_date || null,
        purchase_price: initialData.purchase_price || null,
        lastCT: null, // Frontend-only fields
        nextCT: null,
        driver: null,
        status: initialData.status || "active",
        notes: initialData.notes || "",
        photos: [],
        documents: [],
      });
    }
  }, [initialData]);

  // Gestion textes / nombres
  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]:
        ["annee", "kilometrage", "puissance", "annee_mise_en_circulation", "annee_achat"].includes(
          name
        )
          ? value === ""
            ? null
            : parseInt(value, 10)
          : value,
    }));
  };

  // Sélecteurs (carburant, driver, etc.)
  const handleSelect = (field: keyof VehiclePayload | "driver", value: string) => {
    setFormData((prev) => ({
      ...prev,
      [field]:
        ["annee", "kilometrage", "puissance", "annee_mise_en_circulation", "annee_achat"].includes(
          field
        )
          ? value === ""
            ? null
            : parseInt(value, 10)
          : value,
    }));
  };

  // DatePicker
  const handleDateChange = (field: keyof VehiclePayload, date: Date | null) => {
    setFormData((prev) => ({
      ...prev,
      [field]: date ? date.toISOString().split("T")[0] : null,
    }));
  };

  // Photos (drag & drop support natif via input)
  const handlePhotos = (e: ChangeEvent<HTMLInputElement>) => {
    if (!e.target.files) return;
    setFormData((prev) => ({ ...prev, photos: Array.from(e.target.files) }));
  };

  // Documents PDF
  const handleDocuments = (e: ChangeEvent<HTMLInputElement>) => {
    if (!e.target.files) return;
    setFormData((prev) => ({
      ...prev,
      documents: Array.from(e.target.files),
    }));
  };

  // Soumission
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setLoading(true);
    try {
      const data = new FormData();
      Object.entries(formData).forEach(([key, val]) => {
        if (val == null) return;
        if (key === "photos" && Array.isArray(val)) {
          val.forEach((file) => data.append("photos", file));
        } else if (key === "documents" && Array.isArray(val)) {
          val.forEach((file) => data.append("documents", file));
        } else {
          data.append(key, String(val));
        }
      });

      let result;
      if (isEditing && initialData) {
        console.log("📝 Mise à jour du véhicule ID:", initialData.id);
        result = await updateVehicle(initialData.id, data as any);
        console.log("✅ Véhicule mis à jour :", result);
      } else {
        console.log("➕ Création d'un nouveau véhicule");
        result = await createVehicle(data as any);
        console.log("✅ Véhicule créé :", result);
      }
      onSuccess?.();
    } catch (err: any) {
      console.error("❌ Erreur lors de l’enregistrement :", err);
      setError(err.response?.data?.error ?? "Erreur serveur");
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {/* ―― INFORMATIONS TECHNIQUES & ADMINISTRATIVES ―― */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <Label>Marque*</Label>
          <Input
            name="marque"
            value={formData.marque ?? ""}
            onChange={handleChange}
            required
            placeholder="Ex : Peugeot"
          />
        </div>
        <div>
          <Label>Modèle*</Label>
          <Input
            name="modele"
            value={formData.modele ?? ""}
            onChange={handleChange}
            required
            placeholder="Ex : 208"
          />
        </div>
        <div>
          <Label>Immatriculation*</Label>
          <Input
            name="immatriculation"
            value={formData.immatriculation ?? ""}
            onChange={handleChange}
            required
            placeholder="Ex : AB-123-CD"
          />
        </div>
        <div>
          <Label>Numéro VIN</Label>
          <Input
            name="vin"
            value={formData.vin ?? ""}
            onChange={handleChange}
            placeholder="Numéro d’identification"
          />
        </div>
        <div>
          <Label>Couleur</Label>
          <Input
            name="couleur"
            value={formData.couleur ?? ""}
            onChange={handleChange}
            placeholder="Ex : Blanc"
          />
        </div>
        <div>
          <Label>Type de carburant</Label>
          <select
            name="carburant"
            value={formData.carburant}
            onChange={(e) => handleSelect("carburant", e.target.value)}
            className="w-full rounded-md border border-input p-2"
          >
            <option value="">Sélectionner</option>
            <option value="essence">Essence</option>
            <option value="diesel">Diesel</option>
            <option value="hybride">Hybride</option>
            <option value="electrique">Électrique</option>
          </select>
        </div>
        <div>
          <Label>Transmission*</Label>
          <select
            name="transmission"
            value={formData.transmission || ""}
            onChange={(e) => handleSelect("transmission", e.target.value)}
            className="w-full rounded-md border border-input p-2"
            required
          >
            <option value="">Sélectionner</option>
            <option value="manuelle">Manuelle</option>
            <option value="automatique">Automatique</option>
          </select>
        </div>
        <div>
          <Label>Kilométrage</Label>
          <Input
            name="kilometrage"
            type="number"
            value={formData.kilometrage ?? ""}
            onChange={handleChange}
            placeholder="Ex : 50000"
          />
        </div>
        <div>
          <Label>Année</Label>
          <Input
            name="annee"
            type="number"
            value={formData.annee ?? ""}
            onChange={handleChange}
            placeholder="Ex : 2020"
          />
        </div>
        <div>
          <Label>Puissance (ch)</Label>
          <Input
            name="puissance"
            type="number"
            value={formData.puissance ?? ""}
            onChange={handleChange}
            placeholder="Ex : 90"
          />
        </div>
      </div>

      <Separator />

      {/* ―― DATES, NOTES & TYPE ―― */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <Label>Date d’achat</Label>
          <DatePicker
            value={formData.purchaseDate ?? undefined}
            onChange={(d) => handleDateChange("purchaseDate", d)}
            placeholder="jj/MM/aaaa"
          />
        </div>
        <div>
          <Label>Prix d’achat</Label>
          <Input
            name="purchasePrice"
            type="number"
            value={formData.purchasePrice ?? ""}
            onChange={handleChange}
            placeholder="Ex : 15000"
          />
        </div>
        <div>
          <Label>Date du dernier CT</Label>
          <DatePicker
            value={formData.lastCT ? new Date(formData.lastCT) : undefined}
            onChange={(d) => setFormData(prev => ({ ...prev, lastCT: d ? d.toISOString().split("T")[0] : null }))}
            placeholder="Sélectionner une date"
          />
        </div>
        <div>
          <Label>Date du prochain CT</Label>
          <DatePicker
            value={formData.nextCT ? new Date(formData.nextCT) : undefined}
            onChange={(d) => setFormData(prev => ({ ...prev, nextCT: d ? d.toISOString().split("T")[0] : null }))}
            placeholder="Sélectionner une date"
          />
        </div>
      </div>

      {/* Affichage conditionnel du champ Conducteur */}
      {isFunction && (
        <>
          <Separator />
          <div>
            <Label>Conducteur assigné</Label>
            <Input
              name="driver"
              value={formData.driver ?? ""}
              onChange={handleChange}
              placeholder="Sélectionner le conducteur"
            />
          </div>
        </>
      )}

      <Separator />

      {/* ―― DOCUMENTS (PDF) ―― */}
      <div className="relative">
        <Label>Documents (PDF)</Label>
        <div className="border border-dashed border-gray-300 rounded-lg p-4 text-center">
          <input
            type="file"
            multiple
            accept="application/pdf"
            className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            onChange={handleDocuments}
          />
          <p className="text-sm text-gray-500">
            Glissez-déposez ou cliquez pour importer vos PDF.<br />
            (Carte grise, Certificat d’assurance, CT, Facture…)
          </p>
        </div>
      </div>

      <Separator />

      {/* ―― PHOTOS DU VÉHICULE ―― */}
      <div className="relative">
        <Label>Photos du véhicule</Label>
        <div className="border border-dashed border-gray-300 rounded-lg p-4 text-center">
          <input
            type="file"
            multiple
            accept="image/*"
            className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            onChange={handlePhotos}
          />
          <p className="text-sm text-gray-500">
            Glissez-déposez ou cliquez pour ajouter vos photos
          </p>
        </div>
      </div>

      {error && (
        <p className="text-red-500 text-center text-sm mt-2">{error}</p>
      )}

      {/* ―― BOUTONS ANNULER / ENREGISTRER ―― */}
      <div className="flex justify-end space-x-2">
        <Button
          type="button"
          variant="outline"
          onClick={() => onCancel?.()}
          disabled={loading}
        >
          Annuler
        </Button>
        <Button type="submit" disabled={loading}>
          {loading ? (
            "Enregistrement…"
          ) : (
            <>
              <Save size={16} className="mr-1" /> Enregistrer
            </>
          )}
        </Button>
      </div>
    </form>
  );
};

