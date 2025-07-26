// 📁 server/middlewares/lockOldPlate.js
const { Vehicle } = require("../models");

module.exports = async (req, res, next) => {
  const { id } = req.params;
  // Si l’utilisateur ne modifie pas la plaque, on laisse passer
  if (!("plaque" in req.body)) {
    return next();
  }

  const vehicle = await Vehicle.findByPk(id, { attributes: ["createdAt"] });
  if (!vehicle) {
    return res.status(404).json({ error: "Véhicule introuvable" });
  }

  const ageDays = (Date.now() - new Date(vehicle.createdAt).getTime()) / 86400000;
  if (ageDays > 3) {
    return res.status(403).json({ error: "La modification de la plaque est verrouillée après 3 jours." });
  }

  next();
};

