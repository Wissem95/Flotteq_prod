// 📁 server/middlewares/tenant/checkCompanyUser.js
module.exports = (req, res, next) => {
  // Si jamais vous stockez entrepriseId dans le token
  if (!req.user.entrepriseId) {
    return res
      .status(403)
      .json({ error: "Accès réservé aux membres de votre entreprise" });
  }
  next();
};

