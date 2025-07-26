const nodemailer = require('nodemailer');

// 🔧 Chargement des variables d’environnement (.env)
const {
  SMTP_HOST,
  SMTP_PORT,
  SMTP_USER,
  SMTP_PASS,
  EMAIL_FROM
} = process.env;

// ✅ Transporteur SMTP prêt si infos disponibles
const transporter = (SMTP_HOST && SMTP_USER && SMTP_PASS)
  ? nodemailer.createTransport({
      host: SMTP_HOST,
      port: SMTP_PORT || 587,
      secure: false, // true pour port 465
      auth: {
        user: SMTP_USER,
        pass: SMTP_PASS
      }
    })
  : null;

/**
 * Fonction d'envoi d'une notification
 * @param {Object} options - Paramètres de la notification
 * @param {'email'|'sms'} options.type - Type de notification
 * @param {string} options.to - Destinataire (email ou téléphone)
 * @param {string} options.subject - Sujet de l’email (si applicable)
 * @param {string} options.message - Contenu de l’email ou SMS
 */
const sendNotification = async ({ type, to, subject, message }) => {
  try {
    if (type === 'email') {
      if (transporter) {
        await transporter.sendMail({
          from: EMAIL_FROM || SMTP_USER,
          to,
          subject,
          text: message
        });
        console.log(`✅ Email envoyé à ${to}`);
      } else {
        console.log(`📧 [MODE TEST] Email à ${to}: ${subject} - ${message}`);
      }
    }

    if (type === 'sms') {
      // 📱 Tu pourras brancher Twilio ou OVH ici plus tard
      console.log(`📱 [SMS à implémenter] Message à ${to}: ${message}`);
    }

  } catch (err) {
    console.error('❌ Erreur lors de l’envoi de notification :', err);
  }
};

module.exports = { sendNotification };

