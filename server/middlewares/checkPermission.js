// 📁 server/middlewares/checkPermission.js
const db = require("../models");

module.exports = (permissionKey) => async (req, res, next) => {
  try {
    const userId = req.user.id;
    // Récupère toutes les permissions de l’utilisateur via ses rôles
    const perms = await db.Permission.findAll({
      attributes: ["key"],
      include: {
        model: db.Role,
        attributes: [],
        through: { attributes: [] },
        include: {
          model: db.User,
          attributes: [],
          where: { id: userId },
          through: { attributes: [] },
        },
      },
    });
    const userKeys = perms.map((p) => p.key);
    if (!userKeys.includes(permissionKey)) {
      return res.status(403).json({ error: "Accès refusé" });
    }
    next();
  } catch (err) {
    console.error("🚫 checkPermission error:", err);
    res.status(500).json({ error: "Erreur interne" });
  }
};

