// 📁 server/middlewares/tenant/permission.js
const db = require("../../models");

module.exports = (permissionKey) => async (req, res, next) => {
  try {
    const userId = req.user.id;

    // On récupère les permissions de l'utilisateur (via ses rôles)
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

    const keys = perms.map((p) => p.key);
    if (!keys.includes(permissionKey)) {
      return res.status(403).json({ error: "Permission refusée" });
    }

    next();
  } catch (err) {
    console.error("Erreur tenant/permission :", err);
    res.status(500).json({ error: "Erreur interne" });
  }
};

