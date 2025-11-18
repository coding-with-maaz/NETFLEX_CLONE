const cors = require('cors');

function parseOrigins(raw) {
  if (!raw) return [];
  return raw.split(',').map(s => s.trim()).filter(Boolean);
}

const allowlist = new Set(parseOrigins(process.env.CORS_ORIGINS));

const corsMiddleware = cors({
  origin: function(origin, callback) {
    if (!origin) return callback(null, true);
    if (allowlist.size === 0) return callback(null, true);
    if (allowlist.has(origin)) return callback(null, true);
    return callback(new Error('Not allowed by CORS'));
  },
  credentials: true
});

module.exports = { corsMiddleware };


