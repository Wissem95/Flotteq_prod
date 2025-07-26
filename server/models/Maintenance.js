// /server/models/Maintenance.js

module.exports = (sequelize, DataTypes) => {
  const Maintenance = sequelize.define('Maintenance', {
    date: {
      type: DataTypes.DATEONLY,
      allowNull: false,
    },
    type: {
      type: DataTypes.STRING,
      allowNull: false,
    },
    garage: {
      type: DataTypes.STRING,
    },
    kilometrage: {
      type: DataTypes.INTEGER,
    },
    montant: {
      type: DataTypes.FLOAT,
    },
    pieces: {
      type: DataTypes.TEXT,
    },
    facture: {
      type: DataTypes.STRING,
    },
    vehicle_id: {
      type: DataTypes.INTEGER,
      allowNull: false,
    }
  });

  // 🔗 Association
  Maintenance.associate = (models) => {
    Maintenance.belongsTo(models.Vehicle, {
      foreignKey: 'vehicle_id',
      as: 'Vehicle',
    });
  };

  return Maintenance;
};

