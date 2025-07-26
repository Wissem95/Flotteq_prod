// server/routes/factureRoutes.js

const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const factureController = require('../controllers/factureController');

// Config stockage fichiers
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, path.join(__dirname, '..', 'uploads', 'factures'));
  },
  filename: (req, file, cb) => {
    const timestamp = Date.now();
    cb(null, `${timestamp}-${file.originalname}`);
  },
});
const upload = multer({ storage });

// Routes API
router.get('/', factureController.getAllFactures);
router.post('/', upload.single('fichier'), factureController.createFacture);
router.delete('/:id', factureController.deleteFacture);

module.exports = router;

