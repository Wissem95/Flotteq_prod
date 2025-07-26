// server/routes/pieceRoutes.js

const express = require('express');
const router = express.Router();
const pieceController = require('../controllers/pieceController');

router.get('/pieces', pieceController.getAllPieces);
router.post('/pieces', pieceController.addPiece);
router.post('/changees', pieceController.addPieceChangee);
router.get('/changees/:vehicleId', pieceController.getPieceChangeesByVehicle);

module.exports = router;

