// server/controllers/ctController.js

const { ControleTechnique } = require('../models');
const path = require('path');
const fs = require('fs');

// ✅ Lister tous les contrôles techniques
exports.getAllCT = async (req, res) => {
  try {
    const cts = await ControleTechnique.findAll();
    res.json(cts);
  } catch (err) {
    console.error('Erreur getAllCT:', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

// ✅ Ajouter un contrôle technique
exports.createCT = async (req, res) => {
  try {
    const { date, prochainControle, contreVisite, kilometrage, vehicleId } = req.body;
    const fichier = req.file ? req.file.filename : null;

    const newCT = await ControleTechnique.create({
      date,
      prochainControle,
      contreVisite,
      kilometrage,
      fichier,
      vehicleId
    });

    res.status(201).json(newCT);
  } catch (err) {
    console.error('Erreur createCT:', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

// ✅ Supprimer un contrôle technique + fichier associé
exports.deleteCT = async (req, res) => {
  try {
    const ct = await ControleTechnique.findByPk(req.params.id);
    if (!ct) {
      return res.status(404).json({ error: 'CT non trouvé' });
    }

    if (ct.fichier) {
      const filePath = path.join(__dirname, '..', 'uploads', 'pvs', ct.fichier);
      if (fs.existsSync(filePath)) {
        fs.unlinkSync(filePath);
      }
    }

    await ct.destroy();
    res.json({ message: 'CT supprimé' });
  } catch (err) {
    console.error('Erreur deleteCT:', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

