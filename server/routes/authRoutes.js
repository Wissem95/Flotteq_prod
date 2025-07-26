// 📁 Fichier : server/routes/authRoutes.js

const express      = require('express');
const router       = express.Router();
const authController = require('../controllers/authController');
const { verifyToken } = require("../middlewares/authMiddleware");
const passport     = require('../config/passport');

// On importe ici la génération de token pour le callback Google
const { generateToken } = authController;

// 🔓 Authentification publique
router.post('/register', authController.register);    // ✅ Inscription
router.post('/login',    authController.login);       // ✅ Connexion

// 🔐 Routes protégées
router.get('/me',       verifyToken, authController.getProfile);         // ✅ Voir son propre profil
router.get('/users',    verifyToken, authController.getAllUsers);        // ✅ Voir les utilisateurs de son entreprise
router.get('/users/:id',verifyToken, authController.getUserById);        // ✅ Voir un utilisateur précis
router.post('/users',   verifyToken, authController.createUser);         // ✅ Ajouter un utilisateur à son entreprise
router.put('/users/:id',verifyToken, authController.updateUser);         // ✅ Modifier un utilisateur
router.delete('/users/:id',verifyToken, authController.deleteUser);      // ✅ Supprimer un utilisateur

// Démarrer l'auth Google
router.get('/google', passport.authenticate('google', { scope: ['profile', 'email'] }));

// Callback après login Google
router.get('/google/callback',
  passport.authenticate('google', { failureRedirect: '/login', session: false }),
  (req, res) => {
    // ici generateToken est bien défini
    const token = generateToken(req.user);
    res.redirect(`https://flotteq.belprelocation.fr/login-success?token=${token}`);
  }
);

// 🧪 Test route
router.get('/test', (req, res) => {
  res.json({ message: 'API accessible 🎉' });
});

module.exports = router;

