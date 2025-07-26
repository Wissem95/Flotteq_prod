// /server/middlewares/isAdmin.js

const fs = require("fs");
const path = require("path");

const isAdmin = (req, res, next) => {
  const admins = JSON.parse(
    fs.readFileSync(path.join(__dirname, "../config/admins.json"), "utf8")
  );

  const userEmail = req.user?.email;
  if (!userEmail || !admins.includes(userEmail)) {
    return res.status(403).json({ error: "Accès réservé aux administrateurs." });
  }

  next();
};

module.exports = isAdmin;

