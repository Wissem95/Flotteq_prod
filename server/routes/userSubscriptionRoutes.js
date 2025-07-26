// server/routes/userSubscriptionRoutes.js

const express = require('express');
const router = express.Router();
const userSubscriptionController = require('../controllers/userSubscriptionController');
const { verifyToken } = require('../middlewares/authMiddleware'); // ✅ FIX ici

router.get('/me', verifyToken, userSubscriptionController.getMySubscription);
router.post('/subscribe', verifyToken, userSubscriptionController.subscribe);
router.get('/active', verifyToken, userSubscriptionController.getActiveSubscription);

module.exports = router;

