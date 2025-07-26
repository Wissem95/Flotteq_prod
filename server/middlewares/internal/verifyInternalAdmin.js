// server/middlewares/internal/verifyInternalAdmin.js

module.exports = (req, res, next) => {
  try {
    const user = req.user;

    if (!user) {
      return res.status(401).json({ error: "Non authentifié" });
    }

    if (user.isInternal !== true) {
      return res.status(403).json({ error: "Accès réservé aux employés Flotteq" });
    }

    if (user.role !== "admin") {
      return res.status(403).json({ error: "Accès réservé aux administrateurs internes" });
    }

    next();
  } catch (err) {
    console.error("❌ Erreur verifyInternalAdmin :", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
};


