const { Vehicle, Repair, Maintenance } = require('../models');

exports.getStatistics = async (req, res) => {
  try {
    const totalVehicles = await Vehicle.count();

    const totalActive = await Vehicle.count({ where: { statut: 'active' } });
    const totalInactive = await Vehicle.count({ where: { statut: 'inactive' } });
    const totalMaintenance = await Vehicle.count({ where: { statut: 'maintenance' } });

    const totalKilometrage = await Vehicle.sum('kilometrage');

    const totalEntretien = await Maintenance.count();
    const totalReparations = await Repair.count();

    const sumEntretien = await Maintenance.sum('montant');
    const sumReparations = await Repair.sum('montant');

    const totalDepenses = (sumEntretien || 0) + (sumReparations || 0);

    res.status(200).json({
      totalVehicles,
      totalActive,
      totalInactive,
      totalMaintenance,
      totalKilometrage: totalKilometrage || 0,
      totalEntretien,
      totalReparations,
      totalDepenses
    });
  } catch (error) {
    console.error("❌ Erreur dans getStatistics:", error);
    res.status(500).json({ error: 'Erreur lors du calcul des statistiques' });
  }
};

