// ==============================
// 📁 Fichier : src/components/users/EditUserModal.tsx
// ==============================

import React, { useState, useEffect } from "react";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from "@flotteq/shared";
import { Input } from "@flotteq/shared";
import { Button } from "@flotteq/shared";
import { Label } from "@flotteq/shared";
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem,
} from "@flotteq/shared";
import axios from "@/lib/api";

interface EditUserModalProps {
  user: {
    id: number;
    prenom: string;
    nom: string;
    email: string;
    username: string;
    role: string;
  };
  onClose: () => void;
  onUserUpdated: () => void;
}

const EditUserModal: React.FC<EditUserModalProps> = ({
  user,
  onClose,
  onUserUpdated,
}) => {
  const [formData, setFormData] = useState({
    prenom: "",
    nom: "",
    email: "",
    username: "",
    role: "user",
  });

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (user) {
      setFormData({
        prenom: user.prenom,
        nom: user.nom,
        email: user.email,
        username: user.username,
        role: user.role,
      });
    }
  }, [user]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData((prev) => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleRoleChange = (value: string) => {
    setFormData((prev) => ({ ...prev, role: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      await axios.put(`/users/${user.id}`, formData);
      onUserUpdated();
      onClose();
    } catch (err: any) {
      console.error("❌ Erreur modification utilisateur :", err);
      setError(err?.response?.data?.error || "Erreur inconnue.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open onOpenChange={onClose}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Modifier l’utilisateur</DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="space-y-3">
          <div className="grid grid-cols-2 gap-2">
            <div>
              <Label>Prénom</Label>
              <Input
                name="prenom"
                value={formData.prenom}
                onChange={handleChange}
                required
              />
            </div>
            <div>
              <Label>Nom</Label>
              <Input
                name="nom"
                value={formData.nom}
                onChange={handleChange}
                required
              />
            </div>
          </div>

          <div>
            <Label>Email</Label>
            <Input
              type="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              required
            />
          </div>

          <div>
            <Label>Nom d'utilisateur</Label>
            <Input
              name="username"
              value={formData.username}
              onChange={handleChange}
              required
            />
          </div>

          <div>
            <Label>Rôle</Label>
            <Select value={formData.role} onValueChange={handleRoleChange}>
              <SelectTrigger>
                <SelectValue placeholder="Choisir un rôle" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="user">Utilisateur</SelectItem>
                <SelectItem value="admin">Administrateur</SelectItem>
              </SelectContent>
            </Select>
          </div>

          {error && <p className="text-red-500 text-sm">{error}</p>}

          <div className="flex justify-end pt-2">
            <Button type="submit" disabled={loading}>
              {loading ? "Enregistrement..." : "Enregistrer"}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
};

export default EditUserModal;

