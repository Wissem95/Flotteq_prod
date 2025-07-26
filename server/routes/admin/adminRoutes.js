// server/routes/adminRoutes.js

const express = require("express");
const router = express.Router();
const adminController = require("../../controllers/adminController");
 // assure-toi que ce chemin est correct

 
router.get('/users', adminController.getAllUsers);
router.get('/logs', adminController.getAllLogs);
router.get('/logs/paginated', adminController.getLogs);
router.post('/logs', adminController.addLog); // Pour ajouter manuellement un log si besoin
router.get("/dashboard", adminController.getDashboard); // ← c’est ici que l’erreur est

module.exports = router;

