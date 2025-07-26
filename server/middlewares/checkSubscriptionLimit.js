// 📁 server/middlewares/checkSubscriptionLimit.js

const { Op } = require("sequelize");
const { Vehicle, Subscription, UserSubscription } = require("../models");

module.exports = async function checkSubscriptionLimit(req, res, next) {
  // ─── Si ce n’est pas une requête POST, on n’applique PAS la vérification de quota ───
  // (un PUT /vehicles/:id ne doit pas être considéré comme “ajout”)
  if (req.method !== "POST") {
    return next();
  }

  try {
    // 1. Récupérer l’ID de l’utilisateur depuis req.user (verifyToken doit l’avoir mis)
    const userId = req.user.id;

    // 2. Compter le nombre de véhicules déjà créés par cet utilisateur
    const vehicleCount = await Vehicle.count({
      where: { userId: userId },
    });

    // 3. Tant que vehicleCount < 2, on laisse passer sans abonnement (2 véhicules gratuits)
    if (vehicleCount < 2) {
      return next();
    }

    // 4. Au-delà de 2, on vérifie qu’il ait un abonnement actif
    const today = new Date().toISOString().split("T")[0]; // YYYY-MM-DD

    const userSubs = await UserSubscription.findOne({
      where: {
        userId: userId,
        isActive: true,
        endDate: { [Op.gte]: today },
      },
    });

    if (!userSubs) {
      // Aucun abonnement actif : on bloque la création d’un 3e véhicule
      return res.status(403).json({
        error:
          "Aucun abonnement actif trouvé. Vous avez déjà utilisé vos 2 véhicules gratuits. Merci de souscrire pour ajouter un véhicule supplémentaire.",
      });
    }

    // 5. Si un abonnement existe, récupérer sa formule pour savoir son maxVehicles
    const plan = await Subscription.findByPk(userSubs.subscriptionId);
    if (!plan) {
      return res.status(500).json({
        error:
          "Erreur interne : formule d’abonnement introuvable. Contactez le support.",
      });
    }

    // 6. Vérifier que vehicleCount < plan.maxVehicles
    if (vehicleCount < plan.maxVehicles) {
      return next();
    }

    // 7. Sinon, l’utilisateur a atteint ou dépassé sa limite : bloquer
    return res.status(403).json({
      error: `Vous avez atteint la limite de véhicules pour votre abonnement (“${plan.name}” : max ${plan.maxVehicles} véhicules).`,
    });
  } catch (err) {
    console.error("❌ Erreur dans checkSubscriptionLimit :", err);
    return res.status(500).json({ error: "Erreur serveur" });
  }
};

