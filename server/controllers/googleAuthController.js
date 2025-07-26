const passport = require("passport");
const jwt = require("jsonwebtoken");
const { User } = require("../models");

const FRONT_URL = process.env.FRONT_URL || "https://flotteq.belprelocation.fr";
const JWT_SECRET = process.env.JWT_SECRET || "secret";

// ➕ Création ou récupération d'un utilisateur Google
exports.googleCallback = async (req, res) => {
  try {
    const profile = req.user;
    let user = await User.findOne({ where: { email: profile.emails[0].value } });

    if (!user) {
      user = await User.create({
        email: profile.emails[0].value,
        username: profile.displayName.replace(/\s/g, "").toLowerCase(),
        prenom: profile.name?.givenName || "Google",
        nom: profile.name?.familyName || "User",
        role: "user",
        mot_de_passe: "google", // valeur factice (ne sera pas utilisée)
      });
    }

    const token = jwt.sign(
      {
        id: user.id,
        email: user.email,
        role: user.role,
        entrepriseId: user.entrepriseId || null,
      },
      JWT_SECRET,
      { expiresIn: "7d" }
    );

    // ✅ Redirection avec le token vers le frontend
    res.redirect(`${FRONT_URL}/login-success?token=${token}`);
  } catch (error) {
    console.error("❌ Erreur Google SSO :", error);
    res.redirect(`${FRONT_URL}/login?error=google-auth`);
  }
};

