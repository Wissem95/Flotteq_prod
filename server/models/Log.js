const { DataTypes } = require('sequelize');
const sequelize = require('../config/db');

const Log = sequelize.define('Log', {
  action: {
    type: DataTypes.STRING,
    allowNull: false
  },
  detail: {
    type: DataTypes.TEXT,
    allowNull: true
  }
}, {
  tableName: 'Logs',
  timestamps: true
});

module.exports = Log;

