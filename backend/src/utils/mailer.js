const nodemailer = require('nodemailer');

const smtpHost = process.env.SMTP_HOST;
const smtpPort = Number(process.env.SMTP_PORT || 587);
const smtpSecure = String(process.env.SMTP_SECURE || 'false') === 'true';
const smtpUser = process.env.SMTP_USER;
const smtpPass = process.env.SMTP_PASS;
const smtpFrom = process.env.SMTP_FROM || 'NAZAARABOX <no-reply@localhost>';

let transporter = null;
if (smtpHost && smtpUser && smtpPass) {
  transporter = nodemailer.createTransport({
    host: smtpHost,
    port: smtpPort,
    secure: smtpSecure,
    auth: { user: smtpUser, pass: smtpPass }
  });
}

async function sendMail(to, subject, html) {
  if (!transporter || !to) return false;
  try {
    await transporter.sendMail({ from: smtpFrom, to, subject, html });
    return true;
  } catch {
    return false;
  }
}

module.exports = { sendMail };


