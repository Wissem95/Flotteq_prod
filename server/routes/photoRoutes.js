// server/routes/photoRoutes.js

const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const photoController = require('../controllers/photoController');

// 📁 Stockage des photos dans /uploads/vehicules
const storage = multer.diskStorage({
  destination: function (req, file, cb) {
    cb(null, 'uploads/vehicules');
  },
  filename: function (req, file, cb) {
    const uniqueName = Date.now() + '-' + file.originalname;
    cb(null, uniqueName);
  }
});

const upload = multer({ storage: storage });

// ✅ Ajouter une photo (upload)
router.post('/upload', upload.single('photo'), photoController.uploadPhoto);

// ✅ Obtenir les photos d’un véhicule
router.get('/:vehicleId', photoController.getPhotosByVehicle);

// ✅ Supprimer une photo
router.delete('/:id', photoController.deletePhoto);

module.exports = router;

