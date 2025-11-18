const knex = require('knex');

const client = process.env.DB_CLIENT;
if (!client) {
  throw new Error('DB_CLIENT is required (e.g., mysql2, pg, sqlite3)');
}

const db = knex({
  client,
  connection: client === 'sqlite3'
    ? { filename: process.env.DB_NAME || './data.sqlite' }
    : {
        host: process.env.DB_HOST,
        port: Number(process.env.DB_PORT),
        user: process.env.DB_USER,
        password: process.env.DB_PASS,
        database: process.env.DB_NAME
      },
  useNullAsDefault: client === 'sqlite3',
  pool: { min: 2, max: 10 }
});

module.exports = { db };


