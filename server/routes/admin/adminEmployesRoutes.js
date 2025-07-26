// 📁 server/routes/adminEmployesRoutes.js

const express = require("express");
const router = express.Router();
const { User } = require("../../models");
const { verifyToken } = require("../../middlewares/authMiddleware");
const verifyInternalAdmin = require("../../middlewares/internal/verifyInternalAdmin");


// ⚠️ Toutes les routes ici sont réservées aux administrateurs internes Flotteq
router.use(verifyToken, verifyInternalAdmin);

// 🔍 GET : liste des employés internes
router.get("/", async (req, res) => {
  try {
    const employes = await User.findAll({
      where: { isInternal: true },
      attributes: ["id", "prenom", "nom", "email", "internalRole", "createdAt"]
    });
    res.json(employes);
  } catch (err) {
    console.error("❌ Erreur GET /admin/employes :", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
});

// ➕ POST : ajouter un nouvel employé interne
router.post("/", async (req, res) => {
  try {
    const { prenom, nom, email, mot_de_passe, internalRole } = req.body;

    const existing = await User.findOne({ where: { email } });
    if (existing) return res.status(400).json({ error: "Email déjà utilisé." });

    const newUser = await User.create({
      prenom,
      nom,
      email,
      username: email,
      mot_de_passe,
      internalRole,
      isInternal: true,
      entrepriseId: null,
      role: "admin"
    });

    res.status(201).json(newUser);
  } catch (err) {
    console.error("❌ Erreur POST /admin/employes :", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
});

// ✏️ PUT : modifier un employé interne
router.put("/:id", async (req, res) => {
  try {
    const employe = await User.findByPk(req.params.id);
    if (!employe || !employe.isInternal) return res.status(404).json({ error: "Employé introuvable." });

    const { prenom, nom, email, internalRole } = req.body;

    await employe.update({ prenom, nom, email, internalRole });
    res.json({ message: "Employé mis à jour." });
  } catch (err) {
    console.error("❌ Erreur PUT /admin/employes :", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
});

// ❌ DELETE : supprimer un employé interne
router.delete("/:id", async (req, res) => {
  try {
    const employe = await User.findByPk(req.params.id);
    if (!employe || !employe.isInternal) return res.status(404).json({ error: "Employé introuvable." });

    await employe.destroy();
    res.json({ message: "Employé supprimé." });
  } catch (err) {
    console.error("❌ Erreur DELETE /admin/employes :", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
});

module.exports = router;


