// server/controllers/subscriptionController.js

const Subscription = require('../models/Subscription');

exports.getAllPlans = async (req, res) => {
  try {
    const plans = await Subscription.findAll({ order: [['price', 'ASC']] });
    res.json(plans);
  } catch (error) {
    res.status(500).json({ error: 'Erreur lors de la récupération des formules' });
  }
};

exports.getPlanById = async (req, res) => {
  const { id } = req.params;
  try {
    const plan = await Subscription.findByPk(id);
    if (!plan) return res.status(404).json({ error: 'Formule non trouvée' });
    res.json(plan);
  } catch (error) {
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

exports.createPlan = async (req, res) => {
  const { name, maxVehicles, price, description } = req.body;
  try {
    const newPlan = await Subscription.create({ name, maxVehicles, price, description });
    res.status(201).json(newPlan);
  } catch (error) {
    res.status(500).json({ error: 'Erreur lors de la création de la formule' });
  }
};

