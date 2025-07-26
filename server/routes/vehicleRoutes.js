// 📁 server/routes/vehicleRoutes.js
const express = require("express");
const router = express.Router();
const vehicleController = require("../controllers/vehicleController");

const { verifyToken } = require("../middlewares/authMiddleware");
const checkSubscriptionLimit = require("../middlewares/checkSubscriptionLimit");
const can = require("../middlewares/checkPermission");
const lockOldPlate = require("../middlewares/lockOldPlate");

// ── ROUTES ACCESSIBLES À TOUS LES MEMBRES AUTHENTIFIÉS DE L’ENTREPRISE ──
// Consultation de la liste des véhicules
router.get(
  "/",
  verifyToken,
  vehicleController.getAllVehicles
);

// Consultation du détail d’un véhicule
router.get(
  "/:id",
  verifyToken,
  vehicleController.getVehicleById
);

// ── ROUTES SOUMISES À DES DROITS SPÉCIFIQUES ──
// Création d’un véhicule (2 gratuits, puis abonnement)
router.post(
  "/",
  verifyToken,
  checkSubscriptionLimit,
  can("vehicle.create"),
  vehicleController.createVehicle
);

// Mise à jour d’un véhicule
// • Verrouillage de la modification de la plaque au-delà de 3 jours  
// • Vérification de la permission métier
router.put(
  "/:id",
  verifyToken,
  lockOldPlate,
  can("vehicle.update"),
  vehicleController.updateVehicle
);

// Suppression d’un véhicule
router.delete(
  "/:id",
  verifyToken,
  can("vehicle.delete"),
  vehicleController.deleteVehicle
);

module.exports = router;

