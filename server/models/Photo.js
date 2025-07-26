// server/models/Photo.js

const { DataTypes } = require('sequelize');
const sequelize = require('../config/db');
const Vehicle = require('./Vehicle');

const Photo = sequelize.define('Photo', {
  filename: {
    type: DataTypes.STRING,
    allowNull: false
  },
  path: {
    type: DataTypes.STRING,
    allowNull: false
  }
});

// Relation : un véhicule peut avoir plusieurs photos
Vehicle.hasMany(Photo, { foreignKey: 'vehicleId' });
Photo.belongsTo(Vehicle, { foreignKey: 'vehicleId' });

module.exports = Photo;

