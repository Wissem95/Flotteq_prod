# FlotteQ - API Backend (Node.js + PostgreSQL)

Ce backend alimente la plateforme FlotteQ, permettant la gestion de véhicules, réparations, factures, entretiens, contrôles techniques, profils utilisateurs, et plus encore.

---

## 📦 Stack technique

- Node.js + Express
- PostgreSQL + Sequelize ORM
- Multer (upload fichiers)
- JWT pour authentification
- Docker & Docker Compose

---

## 🚀 Démarrage (en local ou Docker)

### 🔧 Avec Docker

### ```bash
### docker-compose up --build





//
suivi_flotte/
├── server/                          # ✅ Backend Node.js/Express
│   ├── controllers/                 # ✅ Logique des API (vehicles, factures, etc.)
│   │   ├── vehicleController.js     # ✅ API véhicules
│   │   ├── factureController.js     # ✅ API factures
│   │   ├── authController.js        # ✅ Connexion / Inscription
│   │   ├── maintenanceController.js # ✅ Suivi des maintenances
│   │   └── adminController.js       # ✅ Gestion admin (utilisateurs, logs)
│   ├── models/                      # ✅ Modèles Sequelize (tables BDD)
│   │   ├── User.js                  # ✅ Modèle utilisateur
│   │   ├── Vehicle.js               # ✅ Modèle véhicule
│   │   ├── Repair.js                # ✅ Modèle réparation
│   │   ├── Facture.js               # ✅ Modèle facture
│   │   ├── ControleTechnique.js     # ✅ Modèle CT
│   │   ├── Piece.js                 # ✅ Modèle pièce
│   │   ├── PieceChangee.js          # ✅ Modèle pièce changée
│   │   └── Log.js                   # ✅ Historique d'actions
│   ├── routes/                      # ✅ Routes API Express
│   │   ├── authRoutes.js             # ✅ /login /register
│   │   ├── vehicleRoutes.js          # ✅ /vehicles CRUD
│   │   ├── factureRoutes.js          # ✅ /factures CRUD
│   │   ├── maintenanceRoutes.js      # ✅ /maintenances CRUD
│   │   └── adminRoutes.js            # ✅ /admin/users /logs
│   ├── middlewares/                 # ✅ Sécurité et gestion des erreurs
│   │   ├── authMiddleware.js         # ✅ Vérifie le token utilisateur
│   │   └── errorHandler.js           # ✅ Gestion centralisée des erreurs
│   ├── uploads/                     # ✅ Fichiers utilisateurs (photos, factures)
│   │   ├── vehicules/                # ✅ Photos des véhicules
│   │   ├── factures/                 # ✅ Factures PDF
│   │   └── pvs/                      # ✅ PV de contrôle technique
│   ├── config/                      # ✅ Configurations
│   │   ├── db.js                     # ✅ Connexion PostgreSQL ou MongoDB
│   │   └── config.js                 # ✅ Variables d'environnement
│   ├── Dockerfile                   # ✅ Dockerfile backend
│   ├── server.js                    # ✅ Entrée principale Express
│   └── package.json                 # ✅ Dépendances backend
│
├── client/                           # ✅ Frontend React
│   ├── public/                       # ✅ Static public files (favicon, logo, manifest)
│   ├── src/
│   │   ├── assets/                   # ✅ Images, icônes
│   │   ├── components/               # ✅ Composants réutilisables
│   │   │   ├── layout/               # ✅ Navbar, Sidebar
│   │   │   ├── vehicles/             # ✅ Listes & fiches véhicules
│   │   │   ├── factures/             # ✅ Listes & ajout factures
│   │   │   ├── auth/                 # ✅ Login / Register
│   │   │   ├── dashboard/            # ✅ Dashboard utilisateur
│   │   │   ├── admin/                # ✅ Admin dashboard + users
│   │   │   └── maintenance/          # ✅ Entretien, CT
│   │   ├── services/                 # ✅ Appels API backend (axios)
│   │   │   ├── authService.ts        # ✅ Connexion / Inscription API
│   │   │   ├── vehicleService.ts     # ✅ API véhicules
│   │   │   ├── factureService.ts     # ✅ API factures
│   │   │   └── maintenanceService.ts # ✅ API maintenance
│   │   ├── pages/                    # ✅ Pages React
│   │   │   ├── Login.tsx             # ✅ Page login
│   │   │   ├── Register.tsx          # ✅ Page inscription
│   │   │   ├── Dashboard.tsx         # ✅ Accueil utilisateur
│   │   │   ├── Vehicles.tsx          # ✅ Liste véhicules
│   │   │   ├── VehicleDetail.tsx     # ✅ Détail véhicule
│   │   │   ├── Factures.tsx          # ✅ Liste factures
│   │   │   ├── AddVehicle.tsx        # ✅ Ajouter un véhicule
│   │   │   ├── AddFacture.tsx        # ✅ Ajouter une facture
│   │   │   ├── CTPage.tsx            # ✅ Contrôle technique
│   │   │   ├── MaintenancePage.tsx   # ✅ Entretien / réparations
│   │   │   └── AdminDashboard.tsx    # ✅ Espace admin
│   │   ├── utils/                    # ✅ Fonctions utilitaires
│   │   │   ├── helpers.ts            # ✅ Helpers globaux
│   │   │   └── validators.ts         # ✅ Validation champs/formulaires
│   │   ├── App.tsx                   # ✅ Routing et layout global
│   │   ├── main.tsx                  # ✅ Point d’entrée React
│   │   └── vite.config.ts            # ✅ Config vite.js
│   ├── Dockerfile                    # ✅ Dockerfile frontend
│   └── package.json                  # ✅ Dépendances frontend
│
├── docker-compose.yml                # ✅ Compose API + Web + DB
├── .env                               # ✅ Variables d'environnement globales
├── README.md                          # ✅ Documentation projet
└── migrations/                        # ✅ Scripts SQL pour la base (optionnel)
│   ├── 001_init.sql                   # ✅ Création des tables
│   ├── 002_add_columns.sql             # ✅ Colonnes supplémentaires
│   └── 003_fix_indexes.sql             # ✅ Corrections d’index
└── README.md                          # ✅ Documentation du projet

