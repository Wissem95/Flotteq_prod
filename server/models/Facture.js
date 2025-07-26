// server/models/Facture.js

const { DataTypes } = require('sequelize');
const sequelize = require('../config/db');
const User = require('./User'); // si une facture est liée à un user
const Vehicle = require('./Vehicle');

const Facture = sequelize.define('Facture', {
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true,
  },
  montant: {
    type: DataTypes.FLOAT,
    allowNull: false,
  },
  date: {
    type: DataTypes.DATEONLY,
    allowNull: false,
  },
  type: {
    type: DataTypes.STRING,
    allowNull: true,
  },
  fichier: {
    type: DataTypes.STRING,
    allowNull: true,
  },
}, {
  tableName: 'factures',
  timestamps: true,
});

// Une facture appartient à un véhicule
Facture.belongsTo(Vehicle, { foreignKey: 'vehicleId' });
Vehicle.hasMany(Facture, { foreignKey: 'vehicleId' });

module.exports = Facture;

