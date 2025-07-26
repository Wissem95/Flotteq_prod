// 📁 server/controllers/maintenanceController.js
const { Maintenance, Vehicle } = require('../models');

exports.getAllMaintenances = async (req, res) => {
  try {
    // Si tu veux filtrer par utilisateur :
    // const maints = await Maintenance.findAll({ where: { userId: req.user.id } });
    const maints = await Maintenance.findAll({
      include: [{ model: Vehicle, attributes: ['id', 'plaque', 'marque', 'modele'] }],
      order: [['date', 'DESC']],
    });
    res.status(200).json(maints);
  } catch (error) {
    console.error('❌ Erreur getAllMaintenances :', error);
    res.status(500).json({ error: 'Erreur serveur lors de la récupération des maintenances' });
  }
};

exports.getMaintenanceById = async (req, res) => {
  try {
    const m = await Maintenance.findByPk(req.params.id, {
      include: Vehicle
    });
    if (!m) return res.status(404).json({ error: 'Maintenance introuvable' });
    res.json(m);a
  } catch (error) {
    console.error('❌ Erreur getMaintenanceById :', error);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

exports.createMaintenance = async (req, res) => {
  try {
    const { vehicleId, type, date, cout, remarques } = req.body;
    // Optionnel : vérifier que le vehicle existe et appartient à l'user
    const m = await Maintenance.create({ vehicleId, type, date, cout, remarques });
    res.status(201).json(m);
  } catch (error) {
    console.error('❌ Erreur createMaintenance :', error);
    res.status(500).json({ error: 'Erreur serveur lors de la création' });
  }
};

exports.updateMaintenance = async (req, res) => {
  try {
    const m = await Maintenance.findByPk(req.params.id);
    if (!m) return res.status(404).json({ error: 'Maintenance introuvable' });
    await m.update(req.body);
    res.json(m);
  } catch (error) {
    console.error('❌ Erreur updateMaintenance :', error);
    res.status(500).json({ error: 'Erreur serveur lors de la mise à jour' });
  }
};

exports.deleteMaintenance = async (req, res) => {
  try {
    const m = await Maintenance.findByPk(req.params.id);
    if (!m) return res.status(404).json({ error: 'Maintenance introuvable' });
    await m.destroy();
    res.json({ message: 'Maintenance supprimée' });
  } catch (error) {
    console.error('❌ Erreur deleteMaintenance :', error);
    res.status(500).json({ error: 'Erreur serveur lors de la suppression' });
  }
};

