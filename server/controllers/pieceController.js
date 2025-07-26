// server/controllers/pieceController.js

const Piece = require('../models/Piece');
const PieceChangee = require('../models/PieceChangee');

exports.getAllPieces = async (req, res) => {
  const pieces = await Piece.findAll();
  res.json(pieces);
};

exports.addPiece = async (req, res) => {
  const { nom } = req.body;
  const piece = await Piece.create({ nom });
  res.json(piece);
};

exports.addPieceChangee = async (req, res) => {
  const { vehicleId, pieceId, dateChangement, kilometre } = req.body;
  const changee = await PieceChangee.create({ vehicleId, pieceId, dateChangement, kilometre });
  res.json(changee);
};

exports.getPieceChangeesByVehicle = async (req, res) => {
  const { vehicleId } = req.params;
  const result = await PieceChangee.findAll({
    where: { vehicleId },
    include: [{ model: Piece }]
  });
  res.json(result);
};

