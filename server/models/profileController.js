const User = require('../models/User');

const getProfile = async (req, res) => {
  try {
    const user = await User.findByPk(req.user.id, {
      attributes: { exclude: ['mot_de_passe'] } // exclut le mot de passe
    });

    if (!user) {
      return res.status(404).json({ error: "Utilisateur non trouvé" });
    }

    res.json(user);
  } catch (err) {
    console.error("Erreur lors de la récupération du profil :", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
};

module.exports = { getProfile };

