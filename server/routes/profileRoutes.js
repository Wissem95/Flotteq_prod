// 📁 server/routes/profileRoutes.js
const express = require('express');
const router = express.Router();

const profileController = require('../controllers/profileController');
const { verifyToken } = require('../middlewares/authMiddleware'); // ✅ Import ajouté ici

// 🔐 Routes protégées
router.get('/me', verifyToken, profileController.getMyProfile); // ✅ Correction du chemin aussi
router.put('/me', verifyToken, profileController.updateMyProfile);
router.put('/password', verifyToken, profileController.changePassword);

module.exports = router;

