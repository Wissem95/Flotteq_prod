// 📁/server/models/Vehicle.js

const { DataTypes } = require("sequelize");
const sequelize = require("../config/db");

const Vehicle = sequelize.define(
  "Vehicle",
  {
    id: {
      type: DataTypes.INTEGER,
      autoIncrement: true,
      primaryKey: true,
    },
    plaque: {
      type: DataTypes.STRING,
      allowNull: false,
      unique: true,
    },
    marque: {
      type: DataTypes.STRING,
      allowNull: false,
    },
    modele: {
      type: DataTypes.STRING,
      allowNull: false,
    },

    // === NOUVEAUX CHAMPS ===
    status: {
      type: DataTypes.ENUM("active", "maintenance", "inactive", "warning"),
      allowNull: false,
      defaultValue: "active",
    },
    nextCT: {
      type: DataTypes.DATEONLY,
      allowNull: true,
    },
    lastMaintenanceDate: {
      type: DataTypes.DATEONLY,
      allowNull: true,
    },
    pendingCount: {
      type: DataTypes.INTEGER,
      allowNull: true,
      defaultValue: 0,
    },
    // =======================

    annee: {
      type: DataTypes.INTEGER,
      allowNull: true,
    },
    kilometrage: {
      type: DataTypes.INTEGER,
      allowNull: true,
    },
    carburant: {
      type: DataTypes.STRING,
      allowNull: true,
    },
    type: {
      type: DataTypes.STRING,
      allowNull: true,
    },
    couleur: {
      type: DataTypes.STRING,
      allowNull: true,
    },
    numero_serie: {
      type: DataTypes.STRING,
      allowNull: true,
    },
    annee_mise_en_circulation: {
      type: DataTypes.INTEGER,
      allowNull: true,
    },
    annee_achat: {
      type: DataTypes.INTEGER,
      allowNull: true,
    },
    puissance: {
      type: DataTypes.STRING,
      allowNull: true,
    },

    // On rend userId facultatif pour éviter l’erreur FK
    userId: {
      type: DataTypes.INTEGER,
      allowNull: true,
      references: {
        model: "users",
        key: "id",
      },
      onDelete: "SET NULL",
      onUpdate: "CASCADE",
    },
  },
  {
    tableName: "vehicles",
    timestamps: true,
  }
);

Vehicle.associate = (models) => {
  Vehicle.belongsTo(models.User, {
    foreignKey: "userId",
    as: "User",
  });
  // (Tes autres associations : Repair, ControleTechnique, etc.)
};

module.exports = Vehicle;

