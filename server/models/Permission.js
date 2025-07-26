

// Permission.js


module.exports = (sequelize, DataTypes) => {
  const Permission = sequelize.define("Permission", {
    key: { type: DataTypes.STRING, unique: true, allowNull: false },
    description: DataTypes.STRING,
  });

  Permission.associate = (models) => {
    Permission.belongsToMany(models.Role, {
      through: models.RolePermission,
      foreignKey: "permissionId",
      otherKey: "roleId",
    });
  };

  return Permission;
};

