// server/routes/paymentRoutes.js

const express = require('express');
const router = express.Router();
const paymentController = require('../controllers/paymentController');

router.post('/subscribe', paymentController.subscribeToPlan);
router.post('/check-limit', paymentController.checkVehicleLimit);

module.exports = router;

