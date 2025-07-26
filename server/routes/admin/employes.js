// 📁 server/routes/admin/employes.js

const express = require("express");
const router = express.Router();
const { Op } = require("sequelize");
const { InternalUser } = require("../../models");
const verifyToken = require("../../middlewares/verifyToken");
const isAdminFlotteq = require("../../middlewares/isAdminFlotteq"); // middleware spécifique à Flotteq

// ✅ Tous les employés internes (Flotteq)
router.get("/", verifyToken, isAdminFlotteq, async (req, res) => {
  try {
    const users = await InternalUser.findAll({
      attributes: { exclude: ["mot_de_passe"] },
      order: [["prenom", "ASC"]],
    });
    res.json(users);
  } catch (err) {
    console.error("Erreur GET /admin/employes :", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
});

// ✅ Ajouter un employé
router.post("/", verifyToken, isAdminFlotteq, async (req, res) => {
  try {
    const { prenom, nom, email, username, mot_de_passe, roleInterne } = req.body;

    const existing = await InternalUser.findOne({ where: { [Op.or]: [{ email }, { username }] } });
    if (existing) return res.status(400).json({ error: "Email ou identifiant déjà utilisé" });

    const user = await InternalUser.create({ prenom, nom, email, username, mot_de_passe, roleInterne });
    res.status(201).json({ message: "Employé ajouté avec succès", userId: user.id });
  } catch (err) {
    console.error("Erreur POST /admin/employes :", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
});

// ✅ Modifier un employé
router.put("/:id", verifyToken, isAdminFlotteq, async (req, res) => {
  try {
    const { id } = req.params;
    const { prenom, nom, email, username, roleInterne, actif } = req.body;

    const user = await InternalUser.findByPk(id);
    if (!user) return res.status(404).json({ error: "Employé introuvable" });

    await user.update({ prenom, nom, email, username, roleInterne, actif });
    res.json({ message: "Employé mis à jour" });
  } catch (err) {
    console.error("Erreur PUT /admin/employes/:id :", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
});

// ✅ Supprimer un employé
router.delete("/:id", verifyToken, isAdminFlotteq, async (req, res) => {
  try {
    const user = await InternalUser.findByPk(req.params.id);
    if (!user) return res.status(404).json({ error: "Employé introuvable" });

    await user.destroy();
    res.json({ message: "Employé supprimé" });
  } catch (err) {
    console.error("Erreur DELETE /admin/employes/:id :", err);
    res.status(500).json({ error: "Erreur serveur" });
  }
});

module.exports = router;

