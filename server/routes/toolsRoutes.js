const express = require("express");
const router = express.Router();
const path = require("path");
const scanUserRoutes = require("../utils/scanUserRoutes");
const { verifyToken } = require("../middlewares/authMiddleware");

router.get("/scan-users", verifyToken, (req, res) => {
  // Limite cette route aux admins uniquement si besoin
  const srcPath = path.join(__dirname, "..", "..", "clients", "src");
  const results = scanUserRoutes(srcPath);
  res.json(results);
});

module.exports = router;

