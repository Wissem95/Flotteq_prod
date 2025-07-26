// server/routes/ctRoutes.js

const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const ctController = require('../controllers/ctController');

// 📁 Emplacement des fichiers PV de contrôle technique
const storage = multer.diskStorage({
  destination: function (req, file, cb) {
    cb(null, 'uploads/pvs');
  },
  filename: function (req, file, cb) {
    const uniqueName = Date.now() + '-' + file.originalname;
    cb(null, uniqueName);
  }
});

const upload = multer({ storage: storage });

router.get('/', ctController.getAllCT);
router.post('/', upload.single('fichier'), ctController.createCT);
router.delete('/:id', ctController.deleteCT);

module.exports = router;

