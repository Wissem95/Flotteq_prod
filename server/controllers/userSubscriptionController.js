const UserSubscription = require('../models/UserSubscription');
const Subscription = require('../models/Subscription');

// 🔄 Récupérer l’abonnement actif de l’utilisateur connecté
const getMySubscription = async (req, res) => {
  try {
    const current = await UserSubscription.findOne({
      where: { userId: req.user.id, isActive: true },
      include: [Subscription]
    });

    if (!current) return res.status(404).json({ error: 'Aucun abonnement actif' });

    res.json(current);
  } catch (err) {
    console.error('Erreur récupération abonnement:', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

// ➕ S’abonner à une nouvelle formule
const subscribe = async (req, res) => {
  const { subscriptionId } = req.body;

  try {
    // Désactive les anciens abonnements actifs
    await UserSubscription.update(
      { isActive: false },
      { where: { userId: req.user.id, isActive: true } }
    );

    const newSub = await UserSubscription.create({
      userId: req.user.id,
      subscriptionId,
      startDate: new Date(),
      isActive: true
    });

    res.status(201).json(newSub);
  } catch (err) {
    console.error('Erreur souscription:', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

// ✅ Récupérer l’abonnement actif avec détails
const getActiveSubscription = async (req, res) => {
  try {
    const subscription = await UserSubscription.findOne({
      where: { userId: req.user.id, isActive: true },
      include: [Subscription]
    });

    if (!subscription) {
      return res.status(404).json({ error: "Aucun abonnement actif trouvé" });
    }

    res.json(subscription);
  } catch (err) {
    console.error("Erreur getActiveSubscription:", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
};

module.exports = {
  getMySubscription,
  subscribe,
  getActiveSubscription
};

