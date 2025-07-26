// server/controllers/profileController.js
const User = require('../models/User');
const bcrypt = require('bcrypt');

// 🔒 Voir son propre profil
const getMyProfile = async (req, res) => {
  try {
    const user = await User.findByPk(req.user.id, {
      attributes: ['id', 'username', 'email', 'createdAt']
    });
    if (!user) return res.status(404).json({ error: 'Utilisateur introuvable' });
    res.json(user);
  } catch (error) {
    console.error('Erreur profil :', error);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

// 🔒 Modifier le mot de passe
const changePassword = async (req, res) => {
  const { currentPassword, newPassword } = req.body;

  try {
    const user = await User.findByPk(req.user.id);
    if (!user) return res.status(404).json({ error: 'Utilisateur introuvable' });

    const isMatch = await bcrypt.compare(currentPassword, user.mot_de_passe);
    if (!isMatch) return res.status(401).json({ error: 'Mot de passe actuel incorrect' });

    const hashedNewPassword = await bcrypt.hash(newPassword, 10);
    user.mot_de_passe = hashedNewPassword;
    await user.save();

    res.json({ message: 'Mot de passe mis à jour avec succès' });
  } catch (error) {
    console.error('Erreur modification mot de passe :', error);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

// 🔒 Modifier son propre profil
const updateMyProfile = async (req, res) => {
  try {
    const user = await User.findByPk(req.user.id);
    if (!user) return res.status(404).json({ error: 'Utilisateur introuvable' });

    const fields = [
      'prenom', 'nom', 'username', 'email', 'phone', 'birthdate', 'gender', 'address', 'postalCode', 'city', 'country'
    ];
    fields.forEach(field => {
      if (req.body[field] !== undefined) {
        user[field] = req.body[field];
      }
    });
    await user.save();
    res.json(user);
  } catch (error) {
    console.error('Erreur update profil :', error);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

module.exports = {
  getMyProfile,
  changePassword,
  updateMyProfile
};

