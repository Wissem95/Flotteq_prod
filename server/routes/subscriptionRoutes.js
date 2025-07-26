// server/routes/subscriptionRoutes.js

const express = require('express');
const router = express.Router();
const subscriptionController = require('../controllers/subscriptionController');

router.get('/', subscriptionController.getAllPlans);
router.get('/:id', subscriptionController.getPlanById);
router.post('/', subscriptionController.createPlan); // Pour ajout via API

module.exports = router;

