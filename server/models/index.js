// ====================
// 📁 Fichier : server/models/index.js
// ====================

const sequelize = require('../config/db');
const { DataTypes } = require('sequelize');

const User = require('./User');
const Vehicle = require('./Vehicle');
const Facture = require('./Facture');
const ControleTechnique = require('./ControleTechnique');
const Repair = require('./Repair');
const Piece = require('./Piece');
const PieceChangee = require('./PieceChangee');
const Subscription = require('./Subscription');
const UserSubscription = require('./UserSubscription');
const Log = require('./Log');
const Photo = require('./Photo');
const Maintenance = require('./Maintenance');
const Role = require("./Role");
const Permission = require("./Permission");
const RolePermission = require("./RolePermission");
const UserRole = require("./UserRole");

// ✅ Charger Entreprise comme une fonction :
const EntrepriseFactory = require('./Entreprise');
const Entreprise = EntrepriseFactory(sequelize, DataTypes);

// ==================== ASSOCIATIONS GLOBALES ====================

Entreprise.hasMany(User, { foreignKey: 'entrepriseId', onDelete: 'CASCADE' });
User.belongsTo(Entreprise, { foreignKey: 'entrepriseId' });
// 🔐 Abonnements utilisateurs
User.hasMany(UserSubscription, { foreignKey: 'userId', onDelete: 'CASCADE' });
UserSubscription.belongsTo(User, { foreignKey: 'userId' });

Subscription.hasMany(UserSubscription, { foreignKey: 'subscriptionId', onDelete: 'CASCADE' });
UserSubscription.belongsTo(Subscription, { foreignKey: 'subscriptionId' });

// 🚗 Véhicules liés à un utilisateur
User.hasMany(Vehicle, { foreignKey: 'userId', onDelete: 'CASCADE' });
Vehicle.belongsTo(User, { foreignKey: 'userId' });

// 📄 Factures liées à un véhicule
Vehicle.hasMany(Facture, { foreignKey: 'vehicleId', onDelete: 'CASCADE' });
Facture.belongsTo(Vehicle, { foreignKey: 'vehicleId' });

// 🔧 Réparations liées à un véhicule
Vehicle.hasMany(Repair, { foreignKey: 'vehicleId', onDelete: 'CASCADE' });
Repair.belongsTo(Vehicle, { foreignKey: 'vehicleId' });

// 🔍 Contrôles techniques liés à un véhicule
Vehicle.hasMany(ControleTechnique, { foreignKey: 'vehicleId', onDelete: 'CASCADE' });
ControleTechnique.belongsTo(Vehicle, { foreignKey: 'vehicleId' });

// 🧩 Pièces changées lors d'une réparation
Repair.hasMany(PieceChangee, { foreignKey: 'repairId', onDelete: 'CASCADE' });
PieceChangee.belongsTo(Repair, { foreignKey: 'repairId' });

Piece.hasMany(PieceChangee, { foreignKey: 'pieceId' });
PieceChangee.belongsTo(Piece, { foreignKey: 'pieceId' });

// 📷 Photos liées à un véhicule
Vehicle.hasMany(Photo, { foreignKey: 'vehicleId', onDelete: 'CASCADE' });
Photo.belongsTo(Vehicle, { foreignKey: 'vehicleId' });

// 📝 Logs système liés à un utilisateur
User.hasMany(Log, { foreignKey: 'userId', onDelete: 'SET NULL' });
Log.belongsTo(User, { foreignKey: 'userId' });


// ==================== EXPORT DES MODÈLES ==================== //

module.exports = {
  sequelize,
  User,
  Vehicle,
  Facture,
  ControleTechnique,
  Repair,
  Piece,
  PieceChangee,
  Subscription,
  UserSubscription,
  Log,
  Photo,
  Entreprise,
  Maintenance,
  Role,
  Permission,
  RolePermission,
  UserRole,
};

