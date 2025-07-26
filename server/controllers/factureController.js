// server/controllers/factureController.js

const { Facture } = require('../models');
const path = require('path');
const fs = require('fs');

// ✅ Récupérer toutes les factures
exports.getAllFactures = async (req, res) => {
  try {
    const factures = await Facture.findAll();
    res.json(factures);
  } catch (err) {
    console.error('Erreur getAllFactures:', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

// ✅ Récupérer une facture par ID
exports.getFactureById = async (req, res) => {
  try {
    const facture = await Facture.findByPk(req.params.id);
    if (!facture) {
      return res.status(404).json({ error: 'Facture non trouvée' });
    }
    res.json(facture);
  } catch (err) {
    console.error('Erreur getFactureById:', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

// ✅ Ajouter une nouvelle facture avec fichier PDF
exports.createFacture = async (req, res) => {
  try {
    const { date, montant, type, vehicleId } = req.body;

    const fichier = req.file ? req.file.filename : null;

    const nouvelleFacture = await Facture.create({
      date,
      montant,
      type,
      fichier,
      vehicleId
    });

    res.status(201).json(nouvelleFacture);
  } catch (err) {
    console.error('Erreur createFacture:', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

// ✅ Supprimer une facture et son fichier PDF
exports.deleteFacture = async (req, res) => {
  try {
    const facture = await Facture.findByPk(req.params.id);
    if (!facture) {
      return res.status(404).json({ error: 'Facture non trouvée' });
    }

    // Supprimer le fichier associé si présent
    if (facture.fichier) {
      const filePath = path.join(__dirname, '..', 'uploads', 'factures', facture.fichier);
      if (fs.existsSync(filePath)) {
        fs.unlinkSync(filePath);
      }
    }

    await facture.destroy();
    res.json({ message: 'Facture supprimée' });
  } catch (err) {
    console.error('Erreur deleteFacture:', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

