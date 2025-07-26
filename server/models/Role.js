module.exports = (sequelize, DataTypes) => {
  const Role = sequelize.define("Role", {
    name: { type: DataTypes.STRING, allowNull: false, unique: true },
    description: DataTypes.STRING,
    isInternal: { type: DataTypes.BOOLEAN, defaultValue: false }, // ✅ Si c’est un rôle Flotteq uniquement
  });

  Role.associate = (models) => {
    Role.belongsToMany(models.Permission, {
      through: models.RolePermission,
      foreignKey: "roleId",
      otherKey: "permissionId",
    });

    Role.belongsToMany(models.User, {
      through: models.UserRole,
      foreignKey: "roleId",
      otherKey: "userId",
    });
  };

  return Role;
};

