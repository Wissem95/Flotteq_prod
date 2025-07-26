
// models/User.js

const { DataTypes } = require('sequelize');
const bcrypt = require('bcrypt');
const sequelize = require('../config/db');

const User = sequelize.define("User", {
  id: { type: DataTypes.INTEGER, autoIncrement: true, primaryKey: true },
  prenom: { type: DataTypes.STRING, allowNull: false },
  nom: { type: DataTypes.STRING, allowNull: false },
  username: { type: DataTypes.STRING, allowNull: false, unique: true },
  email: { type: DataTypes.STRING, allowNull: false, unique: true, validate: { isEmail: true } },
  mot_de_passe: { type: DataTypes.STRING, allowNull: false },
  phone: DataTypes.STRING,
  birthdate: DataTypes.DATEONLY,
  gender: DataTypes.STRING,
  address: DataTypes.STRING,
  postalCode: DataTypes.STRING,
  city: DataTypes.STRING,
  country: DataTypes.STRING,
  role: { type: DataTypes.STRING, defaultValue: "user" },
  entrepriseId: { type: DataTypes.INTEGER, allowNull: true },
  isInternal: { type: DataTypes.BOOLEAN, defaultValue: false },
  internalRole: { type: DataTypes.STRING, allowNull: true },
}, {
  tableName: 'users',
  timestamps: true,

  // ✅ Hooks bien placés ici
  hooks: {
    beforeCreate: async (user) => {
      if (user.mot_de_passe) {
        const salt = await bcrypt.genSalt(10);
        user.mot_de_passe = await bcrypt.hash(user.mot_de_passe, salt);
      }
    },
    beforeUpdate: async (user) => {
      if (user.changed('mot_de_passe')) {
        const salt = await bcrypt.genSalt(10);
        user.mot_de_passe = await bcrypt.hash(user.mot_de_passe, salt);
      }
    },
  },
});

// ✅ Fonction de vérification du mot de passe
User.prototype.verifyPassword = function (password) {
  return bcrypt.compare(password, this.mot_de_passe);
};

module.exports = User;

