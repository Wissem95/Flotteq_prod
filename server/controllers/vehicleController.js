// ─── 📁 server/controllers/vehicleController.js ───────────────────────────

const db = require("../models");
const { Vehicle, User, Repair, ControleTechnique, Maintenance, Facture } = db;

exports.getAllVehicles = async (req, res) => {
  try {
    const vehicles = await Vehicle.findAll();
    res.status(200).json(vehicles);
  } catch (err) {
    console.error("Erreur getAllVehicles:", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
};

exports.getVehicleById = async (req, res) => {
  try {
    const vehicle = await Vehicle.findByPk(req.params.id);
    if (!vehicle) return res.status(404).json({ error: "Véhicule introuvable" });
    res.status(200).json(vehicle);
  } catch (err) {
    console.error("Erreur getVehicleById:", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
};


exports.createVehicle = async (req, res) => {
  try {
    const { plaque } = req.body;

    // ❌ Empêcher les doublons
    const exists = await Vehicle.findOne({ where: { plaque } });
    if (exists) {
      return res.status(400).json({ error: "Une voiture avec cette plaque existe déjà." });
    }

    // … le reste de ta logique
    const vehicle = await Vehicle.create({ ...req.body, /* userId etc. */ });
    res.status(201).json(vehicle);
  } catch (error) {
    console.error("❌ Erreur lors de la création :", error);
    res.status(500).json({ error: "Erreur serveur" });
  }
};





exports.createVehicle = async (req, res) => {
  try {
    // Récupérer l’ID de l’utilisateur depuis le token, si présent
    const { id: userId } = req.user || {};

    // Vérifier si cet userId existe réellement dans la table `users`
    let finalUserId = null;
    if (userId) {
      const utilisateur = await User.findByPk(userId);
      if (utilisateur) {
        finalUserId = userId;
      } else {
        // Si l’utilisateur n’existe pas, on laisse finalUserId = null
        // (pas besoin de créer un user “vide” ici)
        console.warn(`Aucun utilisateur trouvé en base pour userId = ${userId}. On enregistrera sans userId.`);
      }
    }

    console.log("📩 Données reçues pour ajout :", req.body);
    const payload = {
      ...req.body,
      userId: finalUserId, // sera null si l’utilisateur n’existe pas
    };

    const vehicle = await Vehicle.create(payload);
    res.status(201).json(vehicle);
  } catch (error) {
    console.error("❌ Erreur lors de la création :", error);
    res.status(500).json({ error: "Erreur serveur" });
  }
};

exports.updateVehicle = async (req, res) => {
  try {
    const vehicle = await Vehicle.findByPk(req.params.id);
    if (!vehicle) return res.status(404).json({ error: "Véhicule introuvable" });

    // Pour la mise à jour, on n’impose pas la limite d’abonnement
    await vehicle.update(req.body);
    res.status(200).json(vehicle);
  } catch (err) {
    console.error("Erreur updateVehicle:", err);
    res.status(500).json({ error: "Erreur lors de la mise à jour" });
  }
};

exports.deleteVehicle = async (req, res) => {
  try {
    const vehicle = await Vehicle.findByPk(req.params.id);
    if (!vehicle) return res.status(404).json({ error: "Véhicule introuvable" });

    await vehicle.destroy();
    res.status(200).json({ message: "Véhicule supprimé avec succès" });
  } catch (err) {
    console.error("Erreur deleteVehicle:", err);
    res.status(500).json({ error: "Erreur lors de la suppression" });
  }
};

exports.getVehicleHistory = async (req, res) => {
  try {
    const userId = req.user.id;
    const vehicles = await Vehicle.findAll({
      where: { userId },
      include: [
        { model: Repair, required: false },
        { model: ControleTechnique, required: false },
        { model: Maintenance, required: false },
        { model: Facture, required: false },
      ],
      order: [["createdAt", "DESC"]],
    });
    res.status(200).json(vehicles);
  } catch (err) {
    console.error("Erreur getVehicleHistory:", err);
    res.status(500).json({ error: "Erreur lors de la récupération de l’historique" });
  }
};

