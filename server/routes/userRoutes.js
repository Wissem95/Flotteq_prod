// 📁 server/routes/userRoutes.js
const express = require('express');
const router = express.Router();

const { verifyToken } = require('../middlewares/authMiddleware');
const can = require('../middlewares/checkPermission');
const userController = require('../controllers/userController');

/**
 * GET /api/users/company
 * Liste les utilisateurs de la même entreprise que l’utilisateur connecté.
 * (tout utilisateur authentifié peut y accéder)
 */
router.get(
  '/company',
  verifyToken,
  userController.getUsersByCompany  // à implémenter dans userController
);

/**
 * GET /api/users
 * Liste tous les utilisateurs (admin / manager disposant de la permission user.view).
 */
router.get(
  '/',
  verifyToken,
  can('user.view'),
  userController.getAllUsers
);

/**
 * GET /api/users/:id
 * Détail d’un utilisateur (permission user.view nécessaire).
 */
router.get(
  '/:id',
  verifyToken,
  can('user.view'),
  userController.getUserById
);

/**
 * POST /api/users
 * Création d’un nouvel utilisateur (permission user.create).
 */
router.post(
  '/',
  verifyToken,
  can('user.create'),
  userController.createUser
);

/**
 * PUT /api/users/:id
 * Mise à jour d’un utilisateur :
 *  - si c’est son propre profil, il peut éditer sans permission spécifique
 *  - sinon, permission user.update nécessaire
 */
router.put(
  '/:id',
  verifyToken,
  (req, res, next) => {
    if (req.user.id === Number(req.params.id)) {
      return next();
    }
    return can('user.update')(req, res, next);
  },
  userController.updateUser
);

/**
 * DELETE /api/users/:id
 * Suppression d’un utilisateur (permission user.delete).
 */
router.delete(
  '/:id',
  verifyToken,
  can('user.delete'),
  userController.deleteUser
);

module.exports = router;

