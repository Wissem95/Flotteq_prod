// server/controllers/adminController.js

const User = require('../models/User');
const Log = require('../models/Log');

/**
 * ✅ Liste tous les utilisateurs (sans les mots de passe)
 */
exports.getAllUsers = async (req, res) => {
  try {
    const users = await User.findAll({
      attributes: { exclude: ['password'] }
    });
    res.json(users);
  } catch (error) {
    res.status(500).json({ error: 'Erreur lors de la récupération des utilisateurs' });
  }
};



// server/controllers/adminController.js
exports.getDashboard = (req, res) => {
  res.status(200).json({ message: "Bienvenue sur le dashboard admin" });
};


/**
 * ✅ Liste tous les logs (triés par date)
 */
exports.getAllLogs = async (req, res) => {
  try {
    const logs = await Log.findAll({
      include: [{ model: User, attributes: ['id', 'username', 'email'] }],
      order: [['createdAt', 'DESC']]
    });
    res.json(logs);
  } catch (error) {
    res.status(500).json({ error: 'Erreur lors de la récupération des logs' });
  }
};

/**
 * ✅ Ajoute un log via une route API POST
 * (optionnel mais utile dans certains cas de debug manuel)
 */
exports.addLogFromRoute = async (req, res) => {
  const { userId, action, details } = req.body;
  try {
    const log = await Log.create({ userId, action, detail: details });
    res.status(201).json(log);
  } catch (error) {
    res.status(500).json({ error: 'Erreur lors de l’ajout du log via route' });
  }
};

/**
 * ✅ Ajoute un log depuis n'importe quel contrôleur
 * (appel interne sans passer par une route HTTP)
 */
exports.addLog = async (userId, action, detail = '') => {
  try {
    await Log.create({ userId, action, detail });
  } catch (error) {
    console.error('Erreur log :', error);
  }
};


/**
 * ✅ Retourne uniquement les logs récents avec pagination simple
 */
exports.getLogs = async (req, res) => {
  try {
    const limit = parseInt(req.query.limit) || 10;
    const offset = parseInt(req.query.offset) || 0;

    const logs = await Log.findAndCountAll({
      include: [{ model: User, attributes: ['id', 'username', 'email'] }],
      order: [['createdAt', 'DESC']],
      limit,
      offset
    });

    res.json({
      total: logs.count,
      logs: logs.rows
    });
  } catch (error) {
    res.status(500).json({ error: 'Erreur lors de la récupération des logs paginés' });
  }
};

