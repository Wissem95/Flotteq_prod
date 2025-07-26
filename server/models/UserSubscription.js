// 📁 server/models/UserSubscription.js

const { DataTypes } = require("sequelize");
const sequelize = require("../config/db");

const UserSubscription = sequelize.define(
  "UserSubscription",
  {
    id: {
      type: DataTypes.INTEGER,
      autoIncrement: true,
      primaryKey: true,
    },
    userId: {
      type: DataTypes.INTEGER,
      allowNull: false,
    },
    subscriptionId: {
      type: DataTypes.INTEGER,
      allowNull: false,
    },
    startDate: {
      type: DataTypes.DATEONLY,
      defaultValue: DataTypes.NOW,
    },
    endDate: {
      type: DataTypes.DATEONLY,
      allowNull: true,
    },
    isActive: {
      type: DataTypes.BOOLEAN,
      defaultValue: true,
    },
  },
  {
    tableName: "user_subscriptions",
    timestamps: true,
  }
);

UserSubscription.associate = (models) => {
  UserSubscription.belongsTo(models.User, {
    foreignKey: "userId",
    as: "User",
  });
  UserSubscription.belongsTo(models.Subscription, {
    foreignKey: "subscriptionId",
    as: "Subscription",
  });
};

module.exports = UserSubscription;

