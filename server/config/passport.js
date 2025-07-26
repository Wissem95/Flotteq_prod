// 📁 server/config/passport.js
const passport = require('passport');
const { Strategy: GoogleStrategy } = require('passport-google-oauth20');
const { User, Entreprise }   = require('../models');

const {
  GOOGLE_CLIENT_ID,
  GOOGLE_CLIENT_SECRET,
  GOOGLE_CALLBACK_URL
} = process.env;

if (!GOOGLE_CLIENT_ID || !GOOGLE_CLIENT_SECRET) {
  console.warn('⚠️ Google SSO désactivé: variables non définies.');
} else {
  passport.use(new GoogleStrategy({
      clientID:     GOOGLE_CLIENT_ID,
      clientSecret: GOOGLE_CLIENT_SECRET,
      callbackURL:  GOOGLE_CALLBACK_URL
    },
    async (accessToken, refreshToken, profile, done) => {
      try {
        const email = profile.emails[0].value;
        let user = await User.findOne({ where: { email } });
        if (!user) {
          const entreprise = await Entreprise.create({
            nom: `${profile.displayName} - GoogleEntreprise`
          });
          user = await User.create({
            email,
            username:       profile.id,
            prenom:         profile.name.givenName || '',
            nom:            profile.name.familyName || '',
            mot_de_passe:   'GOOGLE_SSO', // factice
            entrepriseId:   entreprise.id,
            role:           'admin',
          });
        }
        done(null, user);
      } catch (err) {
        done(err, null);
      }
    }
  ));

  // Sérialisation pour session (optionnel)
  passport.serializeUser((user, done) => done(null, user.id));
  passport.deserializeUser(async (id, done) => {
    try {
      const u = await User.findByPk(id);
      done(null, u);
    } catch (e) {
      done(e);
    }
  });
}

module.exports = passport;

