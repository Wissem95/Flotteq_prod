// 📁 server/controllers/userController.js

const { User } = require('../models');

/**
 * GET /api/users/company
 * Récupère tous les utilisateurs de la même entreprise que l'utilisateur connecté.
 */
exports.getUsersByCompany = async (req, res) => {
  try {
    const users = await User.findAll({
      where: { entrepriseId: req.user.entrepriseId },
      attributes: [
        'id',
        'prenom',
        'nom',
        'email',
        'username',
        'role',
        'entrepriseId',
        'createdAt',
      ],
    });
    res.json(users);
  } catch (err) {
    console.error('Erreur getUsersByCompany :', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

/**
 * GET /api/users
 * Récupère tous les utilisateurs (admin/manager avec permission user.view).
 */
exports.getAllUsers = async (req, res) => {
  try {
    const users = await User.findAll({
      attributes: ['id', 'prenom', 'nom', 'email', 'username', 'role', 'createdAt'],
    });
    res.json(users);
  } catch (err) {
    console.error('Erreur getAllUsers :', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

/**
 * GET /api/users/:id
 * Détail d'un utilisateur par son ID (permission user.view).
 */
exports.getUserById = async (req, res) => {
  try {
    const user = await User.findByPk(req.params.id, {
      attributes: ['id', 'prenom', 'nom', 'email', 'username', 'role', 'createdAt'],
    });
    if (!user) {
      return res.status(404).json({ error: 'Utilisateur non trouvé' });
    }
    res.json(user);
  } catch (err) {
    console.error('Erreur getUserById :', err);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

/**
 * POST /api/users
 * Création d'un nouvel utilisateur (permission user.create).
 */
exports.createUser = async (req, res) => {
  try {
    const newUser = await User.create({
      prenom: req.body.prenom,
      nom: req.body.nom,
      email: req.body.email,
      username: req.body.username,
      password: req.body.password, // pensez au hashing en hook
      role: req.body.role,
      entrepriseId: req.user.entrepriseId, // ou fourni par le body selon besoin
    });
    res.status(201).json(newUser);
  } catch (err) {
    console.error('Erreur createUser :', err);
    res.status(500).json({ error: 'Erreur serveur lors de la création' });
  }
};

/**
 * PUT /api/users/:id
 * Mise à jour d'un utilisateur
 * - chacun peut éditer son propre profil
 * - sinon permission user.update nécessaire (checkPermission gère ça)
 */
exports.updateUser = async (req, res) => {
  try {
    const user = await User.findByPk(req.params.id);
    if (!user) {
      return res.status(404).json({ error: 'Utilisateur non trouvé' });
    }
    // si on souhaite empêcher de modifier hors entreprise
    if (
      req.user.entrepriseId &&
      user.entrepriseId !== req.user.entrepriseId
    ) {
      return res.status(403).json({ error: 'Accès interdit à cet utilisateur' });
    }
    await user.update(req.body);
    res.json({ message: 'Utilisateur mis à jour avec succès', user });
  } catch (err) {
    console.error('Erreur updateUser :', err);
    res.status(500).json({ error: 'Erreur serveur lors de la mise à jour' });
  }
};

/**
 * DELETE /api/users/:id
 * Suppression d'un utilisateur (permission user.delete).
 */
exports.deleteUser = async (req, res) => {
  try {
    const user = await User.findByPk(req.params.id);
    if (!user) {
      return res.status(404).json({ error: 'Utilisateur non trouvé' });
    }
    await user.destroy();
    res.json({ message: 'Utilisateur supprimé avec succès' });
  } catch (err) {
    console.error('Erreur deleteUser :', err);
    res.status(500).json({ error: 'Erreur serveur lors de la suppression' });
  }
};

