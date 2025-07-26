// server/models/UserRole.js
module.exports = (sequelize, DataTypes) => {
  return sequelize.define("UserRole", {
    userId: DataTypes.INTEGER,
    roleId: DataTypes.INTEGER,
  }, {
    timestamps: false,
  });
};
