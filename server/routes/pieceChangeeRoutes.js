// server/routes/pieceChangeeRoutes.js

const express = require('express');
const router = express.Router();
const controller = require('../controllers/pieceChangeeController');

router.get('/', controller.getAllChangedParts);
router.post('/', controller.addChangedPart);
router.delete('/:id', controller.deleteChangedPart);

module.exports = router;

