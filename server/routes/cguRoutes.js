// 📁 server/routes/cguRoutes.js
const express = require('express');
const router = express.Router();

// Route publique pour renvoyer le contenu statique des CGU (texte JSON ou HTML brut)
router.get('/', (req, res) => {
  res.json({
    title: 'Conditions Générales d\'Utilisation',
    sections: [
      { id: 1, heading: 'Objet', text: 'Ces CGU ont pour objet de définir les modalités...' },
      // ajouter les sections comme souhaité
    ]
  });
});

module.exports = router;
