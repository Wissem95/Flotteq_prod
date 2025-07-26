// server/models/Repair.js

const { DataTypes } = require('sequelize');
const sequelize = require('../config/db');

const Repair = sequelize.define('Repair', {
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true
  },
  type: {
    type: DataTypes.STRING,
    allowNull: false
  },
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
  description: {
    type: DataTypes.TEXT
  },
  vehicleId: {
    type: DataTypes.INTEGER,
    allowNull: false
  }
}, {
  tableName: 'repairs'
});

module.exports = Repair;

