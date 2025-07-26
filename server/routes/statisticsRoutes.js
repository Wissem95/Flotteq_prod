// server/routes/statisticsRoutes.js

const express = require('express');
const router = express.Router();
const statisticsController = require('../controllers/statisticsController');
const { verifyToken } = require('../middlewares/authMiddleware');

// 🔐 Statistiques protégées par token
router.get('/', verifyToken, statisticsController.getStatistics);

module.exports = router;

