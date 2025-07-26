// 📁 server/models/Entreprise.js

module.exports = (sequelize, DataTypes) => {
  return sequelize.define('Entreprise', {
    nom: { type: DataTypes.STRING, allowNull: false, unique: true },
    siret: { type: DataTypes.STRING, allowNull: true },
    adresse: { type: DataTypes.STRING, allowNull: true },
    codePostal: { type: DataTypes.STRING, allowNull: true },
    ville: { type: DataTypes.STRING, allowNull: true },
    pays: { type: DataTypes.STRING, allowNull: true },
    telephone: { type: DataTypes.STRING, allowNull: true },
    emailContact: {
      type: DataTypes.STRING,
      allowNull: true,
      validate: { isEmail: true },
    },
  }, {
    tableName: 'entreprises',
    timestamps: true,
  });
};

