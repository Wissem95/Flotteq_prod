// 📁 server/models/InternalUser.js

const { DataTypes } = require('sequelize');
const bcrypt = require('bcrypt');
const sequelize = require('../config/db');

const InternalUser = sequelize.define('InternalUser', {
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true,
  },
  prenom: {
    type: DataTypes.STRING,
    allowNull: false,
  },
  nom: {
    type: DataTypes.STRING,
    allowNull: false,
  },
  email: {
    type: DataTypes.STRING,
    allowNull: false,
    unique: true,
  },
  username: {
    type: DataTypes.STRING,
    allowNull: false,
    unique: true,
  },
  mot_de_passe: {
    type: DataTypes.STRING,
    allowNull: false,
  },
  roleInterne: {
    type: DataTypes.STRING,
    allowNull: false, // admin, support, commercial, etc.
  },
  avatar: {
    type: DataTypes.STRING,
    allowNull: true,
  },
  actif: {
    type: DataTypes.BOOLEAN,
    defaultValue: true,
  }
}, {
  tableName: 'internal_users',
  timestamps: true,

  hooks: {
    beforeCreate: async (user) => {
      const salt = await bcrypt.genSalt(10);
      user.mot_de_passe = await bcrypt.hash(user.mot_de_passe, salt);
    },
    beforeUpdate: async (user) => {
      if (user.changed('mot_de_passe')) {
        const salt = await bcrypt.genSalt(10);
        user.mot_de_passe = await bcrypt.hash(user.mot_de_passe, salt);
      }
    },
  },
});

InternalUser.prototype.verifyPassword = function (password) {
  return bcrypt.compare(password, this.mot_de_passe);
};

module.exports = InternalUser;

