// server/controllers/repairController.js

const Repair = require('../models/Repair');

exports.getAllRepairs = async (req, res) => {
  try {
    const repairs = await Repair.findAll();
    res.json(repairs);
  } catch (err) {
    console.error('Erreur récupération réparations :', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

exports.getRepairById = async (req, res) => {
  try {
    const repair = await Repair.findByPk(req.params.id);
    if (!repair) {
      return res.status(404).json({ error: 'Réparation non trouvée' });
    }
    res.json(repair);
  } catch (err) {
    console.error('Erreur récupération réparation :', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

exports.createRepair = async (req, res) => {
  try {
    const repair = await Repair.create(req.body);
    res.status(201).json(repair);
  } catch (err) {
    console.error('Erreur création réparation :', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

exports.updateRepair = async (req, res) => {
  try {
    const repair = await Repair.findByPk(req.params.id);
    if (!repair) {
      return res.status(404).json({ error: 'Réparation non trouvée' });
    }
    await repair.update(req.body);
    res.json(repair);
  } catch (err) {
    console.error('Erreur mise à jour réparation :', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

exports.deleteRepair = async (req, res) => {
  try {
    const repair = await Repair.findByPk(req.params.id);
    if (!repair) {
      return res.status(404).json({ error: 'Réparation non trouvée' });
    }
    await repair.destroy();
    res.json({ message: 'Réparation supprimée' });
  } catch (err) {
    console.error('Erreur suppression réparation :', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

