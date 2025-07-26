// server/models/Piece.js

const { DataTypes } = require('sequelize');
const sequelize = require('../config/db');

const Piece = sequelize.define('Piece', {
  nom: {
    type: DataTypes.STRING,
    allowNull: false
  }
});

module.exports = Piece;

