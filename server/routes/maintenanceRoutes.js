// routes/maintenanceRoutes.js

const express = require("express");
const router = express.Router();
const { Maintenance, Vehicle } = require("../models");
const { verifyToken } = require("../middlewares/authMiddleware");
const multer = require("multer");
const path = require("path");

// 📁 Configuration de Multer
const storage = multer.diskStorage({
  destination: function (req, file, cb) {
    cb(null, "uploads/factures/");
  },
  filename: function (req, file, cb) {
    const unique = Date.now() + "-" + Math.round(Math.random() * 1e9);
    cb(null, unique + path.extname(file.originalname));
  },
});
const upload = multer({ storage });

// ✅ GET toutes les maintenances
router.get("/", verifyToken, async (req, res) => {
  try {
    const maintenances = await Maintenance.findAll({
      include: {
        model: Vehicle,
        as: "Vehicle",
        attributes: ["marque", "modele", "immatriculation"],
      },
      order: [["date", "DESC"]],
    });

    const formatted = maintenances.map((m) => ({
      id: m.id,
      date: m.date,
      type: m.type,
      garage: m.garage,
      kilometrage: m.kilometrage,
      montant: m.montant,
      pieces: m.pieces,
      facture: m.facture,
      vehicle: m.Vehicle
        ? {
            marque: m.Vehicle.marque,
            modele: m.Vehicle.modele,
            plaque: m.Vehicle.plaque,
          }
        : null,
    }));

    res.json(formatted);
  } catch (err) {
    console.error("❌ Erreur API /maintenances :", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
});


// ✅ Route GET par ID
router.get('/:id', verifyToken, async (req, res) => {
  try {
    const maintenance = await Maintenance.findByPk(req.params.id);

    if (!maintenance) {
      return res.status(404).json({ error: "Maintenance non trouvée" });
    }

    res.json(maintenance);
  } catch (err) {
    console.error("❌ Erreur API /maintenances/:id :", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
});
 

// ✅ POST : Ajouter une maintenance avec fichier
router.post("/", verifyToken, upload.single("facture"), async (req, res) => {
  try {
    const data = req.body;
    const newMaintenance = await Maintenance.create({
      ...data,
      facture: req.file?.filename || null,
    });

    res.status(201).json(newMaintenance);
  } catch (err) {
    console.error("Erreur POST /api/maintenances :", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
});

// ✅ DELETE : Supprimer une maintenance
router.delete("/:id", verifyToken, async (req, res) => {
  try {
    const m = await Maintenance.findByPk(req.params.id);
    if (!m) return res.status(404).json({ error: "Introuvable" });

    await m.destroy();
    res.json({ message: "Maintenance supprimée" });
  } catch (err) {
    console.error("Erreur DELETE /maintenances :", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
});


// ✅ PUT : Modifier une maintenance
router.put("/:id", verifyToken, upload.single("facture"), async (req, res) => {
  try {
    const maintenance = await Maintenance.findByPk(req.params.id);
    if (!maintenance) {
      return res.status(404).json({ error: "Maintenance introuvable" });
    }

    const data = req.body;

    // Si un nouveau fichier est envoyé, on remplace l'ancien
    if (req.file) {
      data.facture = req.file.filename;
    }

    await maintenance.update(data);

    res.json({ message: "Maintenance mise à jour", maintenance });
  } catch (err) {
    console.error("Erreur PUT /api/maintenances/:id :", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
});


// ✅ Mise à jour d'une maintenance
router.put('/:id', verifyToken, upload.single("facture"), async (req, res) => {
  try {
    const maintenance = await Maintenance.findByPk(req.params.id);
    if (!maintenance) {
      return res.status(404).json({ error: "Maintenance introuvable" });
    }

    const data = req.body;

    // Si une nouvelle facture est uploadée, on la remplace
    if (req.file) {
      data.facture = req.file.filename;
    }

    await maintenance.update(data);
    res.json(maintenance);
  } catch (err) {
    console.error("❌ Erreur PUT /api/maintenances/:id :", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
});

// ✅ Route de modification
router.put("/:id", verifyToken, upload.single("facture"), async (req, res) => {
  try {
    const { id } = req.params;
    const maintenance = await Maintenance.findByPk(id);

    if (!maintenance) {
      return res.status(404).json({ error: "Maintenance introuvable" });
    }

    // Fusionner les données reçues (body) + nouvelle facture si fournie
    const updatedData = {
      ...req.body,
      facture: req.file?.filename || maintenance.facture, // conserver l'existante si non remplacée
    };

    await maintenance.update(updatedData);
    res.json(maintenance);
  } catch (err) {
    console.error("❌ Erreur PUT /api/maintenances/:id :", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
});
 
module.exports = router;

