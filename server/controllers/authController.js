// ====================
// 📁 Fichier : server/controllers/authController.js
// ====================

const { User, Entreprise } = require('../models');
const { Op } = require('sequelize');
const bcrypt = require('bcrypt');

const jwt = require('jsonwebtoken');

const SECRET_KEY = process.env.JWT_SECRET || 'secret';

// 🔐 Génération du token JWT
const generateToken = (user) => {
  return jwt.sign(
    {
      id: user.id,
      email: user.email,
      role: user.role,
      entrepriseId: user.entrepriseId || null,
    },
    SECRET_KEY,
    { expiresIn: '7d' }
  );
};

// ✅ Inscription d’un admin (création d’une entreprise)
exports.register = async (req, res) => {
  try {
    const { email, mot_de_passe, username, prenom, nom } = req.body;

    const existingUser = await User.findOne({ where: { email } });
    if (existingUser) return res.status(400).json({ error: "Cet utilisateur existe déjà." });

    const entreprise = await Entreprise.create({
      nom: `${prenom} ${nom} Entreprise`,
    });

    const newUser = await User.create({
      email,
      mot_de_passe,
      username,
      prenom,
      nom,
      entrepriseId: entreprise.id,
      role: 'admin',
    });

    const token = generateToken(newUser);
    res.status(201).json({ message: "Inscription réussie", token, user: newUser });
  } catch (error) {
    console.error("❌ Erreur lors de l'inscription :", error);
    res.status(500).json({ error: "Erreur serveur" });
  }
};

// ✅ Connexion avec email OU username
exports.login = async (req, res) => {
  try {
    const { identifiant, password } = req.body;

    if (!identifiant || !password) {
      return res.status(400).json({ error: "Identifiant et mot de passe requis." });
    }

    const user = await User.findOne({
      where: {
        [Op.or]: [
          { email: identifiant },
          { username: identifiant }
        ]
      }
    });

    if (!user || !(await user.verifyPassword(password))) {
      return res.status(401).json({ error: "Identifiant ou mot de passe incorrect." });
    }

    const token = generateToken(user);
    res.status(200).json({ message: "Connexion réussie", token, user });
  } catch (error) {
    console.error("❌ Erreur login :", error);
    res.status(500).json({ error: "Erreur lors de la connexion." });
  }
};

// 👤 Voir son propre profil
exports.getProfile = async (req, res) => {
  try {
    const user = await User.findByPk(req.user.id, {
      attributes: { exclude: ['mot_de_passe'] },
    });
    res.status(200).json(user);
  } catch (error) {
    console.error("❌ Erreur profil utilisateur :", error);
    res.status(500).json({ error: "Erreur serveur" });
  }
};

// 👥 Voir tous les utilisateurs de SA propre entreprise
exports.getAllUsers = async (req, res) => {
  try {
    const users = await User.findAll({
      where: { entrepriseId: req.user.entrepriseId },
      attributes: { exclude: ['mot_de_passe'] },
    });
    res.status(200).json(users);
  } catch (error) {
    console.error("❌ Erreur récupération utilisateurs :", error);
    res.status(500).json({ error: "Erreur serveur" });
  }
};

// 👤 Voir un utilisateur spécifique de SA propre entreprise
exports.getUserById = async (req, res) => {
  try {
    const user = await User.findOne({
      where: {
        id: req.params.id,
        entrepriseId: req.user.entrepriseId,
      },
      attributes: { exclude: ['mot_de_passe'] },
    });

    if (!user) return res.status(404).json({ error: "Utilisateur introuvable." });

    res.status(200).json(user);
  } catch (error) {
    console.error("❌ Erreur getUserById :", error);
    res.status(500).json({ error: "Erreur serveur" });
  }
};

// ➕ Ajouter un utilisateur à SON entreprise
exports.createUser = async (req, res) => {
  try {
    const { email, username, mot_de_passe, prenom, nom, role } = req.body;

    const existing = await User.findOne({ where: { email } });
    if (existing) {
      return res.status(400).json({ error: "Un utilisateur avec cet email existe déjà." });
    }

    const user = await User.create({
      email,
      username,
      mot_de_passe,
      prenom,
      nom,
      role: role || "user",
      entrepriseId: req.user.entrepriseId,
    });

    res.status(201).json(user);
  } catch (error) {
    console.error("❌ Erreur création utilisateur :", error);
    res.status(500).json({ error: "Erreur serveur" });
  }
};

// ✏️ Modifier un utilisateur de SA propre entreprise
exports.updateUser = async (req, res) => {
  try {
    const user = await User.findOne({
      where: {
        id: req.params.id,
        entrepriseId: req.user.entrepriseId,
      },
    });

    if (!user) return res.status(404).json({ error: "Utilisateur introuvable." });

    await user.update(req.body);
    res.status(200).json(user);
  } catch (error) {
    console.error("❌ Erreur updateUser :", error);
    res.status(500).json({ error: "Erreur serveur" });
  }
};

// ❌ Supprimer un utilisateur de SA propre entreprise
exports.deleteUser = async (req, res) => {
  try {
    const user = await User.findOne({
      where: {
        id: req.params.id,
        entrepriseId: req.user.entrepriseId,
      },
    });

    if (!user) return res.status(404).json({ error: "Utilisateur introuvable." });

    await user.destroy();
    res.status(200).json({ message: "Utilisateur supprimé avec succès." });
  } catch (error) {
    console.error("❌ Erreur deleteUser :", error);
    res.status(500).json({ error: "Erreur serveur" });
  }
};

