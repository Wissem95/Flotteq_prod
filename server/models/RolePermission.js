// server/models/RolePermission.js
module.exports = (sequelize, DataTypes) => {
  return sequelize.define("RolePermission", {
    roleId: DataTypes.INTEGER,
    permissionId: DataTypes.INTEGER,
  }, {
    timestamps: false,
  });
};
