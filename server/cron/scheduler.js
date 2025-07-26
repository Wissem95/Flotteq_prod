// server/cron/scheduler.js

const cron = require('node-cron');
const { Op } = require('sequelize');
const ControleTechnique = require('../models/ControleTechnique');
const UserSubscription = require('../models/UserSubscription');

// 🧪 Fonction de notification générique (à implémenter plus tard)
// Tu pourras y intégrer Twilio, Nodemailer, Sendinblue, etc.
const sendNotification = async ({ type, to, subject, message }) => {
  /*
    type : 'email' ou 'sms'
    to : adresse e-mail ou numéro de téléphone
    subject : sujet du message (utilisé pour les mails)
    message : contenu principal
  */
  console.log(`🔔 Notification ${type} prévue vers ${to} : ${subject}`);
};

// ✅ Chargement initial du planificateur
console.log('⏰ Cron scheduler chargé');

// ─────────────────────────────────────────────────────────────
// 🔁 Tâche 1 : Vérifie chaque jour à 7h les CT à venir dans 30 jours
// ─────────────────────────────────────────────────────────────
cron.schedule('0 7 * * *', async () => {
  const now = new Date();
  const dans30Jours = new Date();
  dans30Jours.setDate(now.getDate() + 30);

  try {
    const cts = await ControleTechnique.findAll({
      where: {
        prochainControle: {
          [Op.between]: [now, dans30Jours]
        }
      }
    });

    if (cts.length > 0) {
      console.log(`📣 ${cts.length} CT à venir dans les 30 jours`);

      /*
        🔔 EXEMPLE de notification à activer plus tard :
        for (const ct of cts) {
          const userEmail = await récupérerEmailUtilisateur(ct.vehicleId);
          await sendNotification({
            type: 'email',
            to: userEmail,
            subject: 'Contrôle technique imminent',
            message: `Votre véhicule doit passer le CT avant le ${ct.prochainControle}.`
          });
        }
      */
    }
  } catch (error) {
    console.error('❌ Erreur CRON CT :', error);
  }
});

// ─────────────────────────────────────────────────────────────
// 🔒 Tâche 2 : Chaque nuit à minuit → désactive les abonnements expirés
// ─────────────────────────────────────────────────────────────
cron.schedule('0 0 * * *', async () => {
  console.log('🔁 Vérification des abonnements expirés...');

  try {
    const now = new Date();

    const result = await UserSubscription.update(
      { isActive: false },
      {
        where: {
          endDate: { [Op.lt]: now },
          isActive: true
        }
      }
    );

    console.log(`🔒 ${result[0]} abonnement(s) désactivé(s) automatiquement.`);

    /*
      🔔 EXEMPLE pour ajouter des notifications :
      const user = await User.findByPk(userId);
      await sendNotification({
        type: 'sms',
        to: user.phone,
        message: 'Votre abonnement a expiré. Merci de le renouveler.'
      });
    */
  } catch (error) {
    console.error('❌ Erreur lors de la désactivation des abonnements :', error);
  }
});

