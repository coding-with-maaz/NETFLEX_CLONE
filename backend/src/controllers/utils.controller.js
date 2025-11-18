const { db } = require('../db/knex.js');
const { ok, serverError } = require('../utils/responses.js');

async function getAllUtils(req, res) {
  try {
    const [genres, countries, categories, languages] = await Promise.all([
      db('genres').select('*'),
      db('countries').select('*'),
      db('categories').select('*'),
      db('languages').select('*')
    ]);
    const currentYear = new Date().getFullYear();
    const years = Array.from({ length: 50 }, (_, i) => currentYear - i);
    return ok(res, { genres, countries, categories, languages, years });
  } catch (e) {
    return serverError(res, 'Failed to load utilities');
  }
}

module.exports = { getAllUtils };


