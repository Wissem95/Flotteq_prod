// 📁 server/server.js
require('dotenv').config();  
const express   = require('express');
const cors      = require('cors');
const dotenv    = require('dotenv');
const morgan    = require('morgan');
const path      = require('path');
const session   = require('express-session');
const passport  = require('./config/passport');

const { sequelize } = require('./models');

// Chargement des variables d'environnement
dotenv.config();

// Initialisation de l'application
const app = express();

// Middleware CORS
app.use(cors({
  origin: [
    'http://localhost:9092',
    'http://192.168.2.12:9092',
    'http://localhost:8000',
    'https://flotteq.belprelocation.fr'
  ],
  methods: ['GET','POST','PUT','DELETE','OPTIONS'],
  allowedHeaders: ['Content-Type','Authorization'],
  credentials: true
}));

// Renforce les en-têtes CORS
app.use((req, res, next) => {
  res.setHeader("Access-Control-Allow-Origin", req.headers.origin || "*");
  res.setHeader("Access-Control-Allow-Credentials", "true");
  res.setHeader("Access-Control-Allow-Methods", "GET, POST, PUT, PATCH, DELETE, OPTIONS");
  res.setHeader("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept, Authorization");
  next();
});

// Session + Passport (Google SSO)
app.use(session({
  secret: 'flotteq_secret',
  resave: false,
  saveUninitialized: false
}));
app.use(passport.initialize());
app.use(passport.session());

// Autres middlewares
app.use(express.json());
app.use(morgan('dev'));

// Route de test
app.get('/', (req, res) => {
  res.send("✅ Bienvenue sur l'API FlotteQ");
});

// Import des routes
const cguRoutes              = require('./routes/cguRoutes');
const authRoutes             = require('./routes/authRoutes');
const userRoutes             = require('./routes/userRoutes');
const adminRoutes            = require('./routes/admin/adminRoutes');
const adminEmployesRoutes    = require('./routes/admin/adminEmployesRoutes');
const vehicleRoutes          = require('./routes/vehicleRoutes');
const factureRoutes          = require('./routes/factureRoutes');
const maintenanceRoutes      = require('./routes/maintenanceRoutes');
const ctRoutes               = require('./routes/ctRoutes');
const repairRoutes           = require('./routes/repairRoutes');
const pieceRoutes            = require('./routes/pieceRoutes');
const subscriptionRoutes     = require('./routes/subscriptionRoutes');
const paymentRoutes          = require('./routes/paymentRoutes');
const photoRoutes            = require('./routes/photoRoutes');
const profileRoutes          = require('./routes/profileRoutes');
const pieceChangeeRoutes     = require('./routes/pieceChangeeRoutes');
const userSubscriptionRoutes = require('./routes/userSubscriptionRoutes');
const statisticsRoutes       = require('./routes/statisticsRoutes');
const toolsRoutes            = require('./routes/toolsRoutes');

// Déclaration des routes
app.use('/api/cgu',                 cguRoutes);
app.use('/api/auth',                authRoutes);
app.use('/api/users',               userRoutes);
app.use('/api/admin',               adminRoutes);
app.use('/api/admin/employes',      adminEmployesRoutes);
app.use('/api/vehicles',            vehicleRoutes);
app.use('/api/factures',            factureRoutes);
app.use('/api/maintenances',        maintenanceRoutes);
app.use('/api/ct',                  ctRoutes);
app.use('/api/repairs',             repairRoutes);
app.use('/api/pieces',              pieceRoutes);
app.use('/api/subscriptions',       subscriptionRoutes);
app.use('/api/payment',             paymentRoutes);
app.use('/api/photos',              photoRoutes);
app.use('/api/profile',             profileRoutes);
app.use('/api/pieces-changees',     pieceChangeeRoutes);
app.use('/api/my-subscription',     userSubscriptionRoutes);
app.use('/api/vehicles/statistics', statisticsRoutes);
app.use('/api/tools',               toolsRoutes);

// Fichiers statiques (uploads)
app.use('/uploads', express.static(path.join(__dirname, 'uploads')));

// Middleware 404
app.use((req, res) => {
  res.status(404).json({ error: 'Ressource non trouvée' });
});

// Middleware global d'erreur
app.use((err, req, res, next) => {
  console.error('❌ Erreur serveur :', err);
  res.status(500).json({ error: 'Erreur interne du serveur' });
});

// Démarrage du serveur
const PORT = process.env.PORT || 5000;

// === Ici, on passe `alter: true` pour demander à Sequelize
//      d’appliquer automatiquement les ALTER TABLE nécessaires ===
sequelize.sync({ alter: true })
  .then(() => {
    console.log('📦 Synchronisation "alter" des modèles terminée');
    return sequelize.authenticate();
  })
  .then(() => {
    console.log('🚀 Connexion PostgreSQL établie avec succès');
    app.listen(PORT, () => {
      console.log(`✅ Serveur démarré sur le port ${PORT}`);
    });
  })
  .catch((err) => {
    console.error('❌ Erreur au démarrage du serveur :', err);
  });

