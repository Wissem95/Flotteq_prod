// server/controllers/pieceChangeeController.js

const PieceChangee = require('../models/PieceChangee');
const Piece = require('../models/Piece');

exports.getAllChangedParts = async (req, res) => {
  try {
    const data = await PieceChangee.findAll({
      include: [{ model: Piece }],
      order: [['date', 'DESC']]
    });
    res.json(data);
  } catch (err) {
    console.error('Erreur récupération pièces changées :', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

exports.addChangedPart = async (req, res) => {
  const { date, kilometrage, cout, vehicleId, pieceId } = req.body;
  try {
    const ajout = await PieceChangee.create({ date, kilometrage, cout, vehicleId, pieceId });
    res.status(201).json(ajout);
  } catch (err) {
    console.error('Erreur ajout pièce changée :', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

exports.deleteChangedPart = async (req, res) => {
  try {
    const target = await PieceChangee.findByPk(req.params.id);
    if (!target) return res.status(404).json({ error: 'Non trouvée' });

    await target.destroy();
    res.json({ message: 'Pièce changée supprimée' });
  } catch (err) {
    console.error('Erreur suppression :', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

