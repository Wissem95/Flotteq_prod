// 📁 server/middlewares/tenant/checkCompanyAdmin.js
module.exports = (req, res, next) => {
  if (req.user.role !== 'admin') {
    return res.status(403).json({ error: 'Accès réservé aux administrateurs de votre entreprise' });
  }
  next();
};

