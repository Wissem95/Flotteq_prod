// server/models/PieceChangee.js

const { DataTypes } = require('sequelize');
const sequelize = require('../config/db');
const Vehicle = require('./Vehicle');
const Repair = require('./Repair');
const Piece = require('./Piece');

const PieceChangee = sequelize.define('PieceChangee', {
  date: {
    type: DataTypes.DATEONLY,
    allowNull: false
  },
  kilometrage: {
    type: DataTypes.INTEGER,
    allowNull: false
  },
  cout: {
    type: DataTypes.FLOAT,
    allowNull: false
  },
  vehicleId: {
    type: DataTypes.INTEGER,
    allowNull: false
  },
  pieceId: {
    type: DataTypes.INTEGER,
    allowNull: false
  }
});

// Relations
PieceChangee.belongsTo(Vehicle, { foreignKey: 'vehicleId' });
PieceChangee.belongsTo(Piece, { foreignKey: 'pieceId' });

module.exports = PieceChangee;

