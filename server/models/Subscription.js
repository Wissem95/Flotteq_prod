// 📁 server/models/Subscription.js

const { DataTypes } = require("sequelize");
const sequelize = require("../config/db");

const Subscription = sequelize.define(
  "Subscription",
  {
    id: {
      type: DataTypes.INTEGER,
      autoIncrement: true,
      primaryKey: true,
    },
    name: {
      type: DataTypes.STRING,
      allowNull: false,
    },
    maxVehicles: {
      type: DataTypes.INTEGER,
      allowNull: false,
    },
    price: {
      type: DataTypes.FLOAT,
      allowNull: false,
    },
    description: {
      type: DataTypes.TEXT,
      allowNull: true,
    },
  },
  {
    tableName: "subscriptions",
    timestamps: true,
  }
);

Subscription.associate = (models) => {
  Subscription.hasMany(models.UserSubscription, {
    foreignKey: "subscriptionId",
    as: "UserSubs",
  });
};

module.exports = Subscription;

