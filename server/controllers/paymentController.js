// server/controllers/paymentController.js

const UserSubscription = require('../models/UserSubscription');
const Subscription = require('../models/Subscription');

exports.subscribeToPlan = async (req, res) => {
  const { userId, subscriptionId } = req.body;

  try {
    const subscription = await Subscription.findByPk(subscriptionId);
    if (!subscription) {
      return res.status(404).json({ error: 'Formule non trouvée' });
    }

    const startDate = new Date();
    const endDate = new Date();
    endDate.setMonth(endDate.getMonth() + 1); // Exemple : abonnement mensuel

    const newSub = await UserSubscription.create({
      userId,
      subscriptionId,
      startDate,
      endDate
    });

    res.status(201).json(newSub);
  } catch (error) {
    console.error('Erreur abonnement :', error);
    res.status(500).json({ error: 'Erreur lors de la souscription' });
  }
};

exports.checkVehicleLimit = async (req, res) => {
  const { userId, currentVehicleCount } = req.body;

  try {
    const userSub = await UserSubscription.findOne({
      where: { userId },
      include: Subscription
    });

    if (!userSub) {
      return res.status(404).json({ error: 'Abonnement non trouvé' });
    }

    const allowed = userSub.Subscription.maxVehicles;
    const remaining = allowed - currentVehicleCount;

    res.json({
      maxVehicles: allowed,
      canAddMore: remaining > 0,
      remaining
    });
  } catch (error) {
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

