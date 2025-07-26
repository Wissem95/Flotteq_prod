// server/models/ControleTechnique.js

const { DataTypes } = require('sequelize');
const sequelize = require('../config/db');

const ControleTechnique = sequelize.define('ControleTechnique', {
  date: {
    type: DataTypes.DATEONLY,
    allowNull: false
  },
  prochainControle: {
    type: DataTypes.DATEONLY,
    allowNull: false
  },
  contreVisite: {
    type: DataTypes.BOOLEAN,
    defaultValue: false
  },
  kilometrage: {
    type: DataTypes.INTEGER,
    allowNull: false
  },
  fichier: {
    type: DataTypes.STRING,
    allowNull: true
  },
  vehicleId: {
    type: DataTypes.INTEGER,
    allowNull: false
  }
});

module.exports = ControleTechnique;

