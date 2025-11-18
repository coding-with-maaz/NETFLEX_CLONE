const { db } = require('../db/knex.js');
const { ok, created, validationError, serverError } = require('../utils/responses.js');
const { paginate } = require('../utils/pagination.js');
const { sendMail } = require('../utils/mailer.js');

const REQUEST_TYPES = new Set(['movie', 'tvshow']);
const STATUS = new Set(['pending', 'approved', 'rejected', 'completed']);

async function submitRequest(req, res) {
  const { type, title, email, description, tmdb_id, year } = req.body || {};
  const errors = {};
  if (!REQUEST_TYPES.has(String(type || '').toLowerCase())) errors.type = ['The type field is required and must be movie or tvshow.'];
  if (!title || String(title).length > 255) errors.title = ['The title field is required and must be <= 255 chars.'];
  if (description && String(description).length > 1000) errors.description = ['Max 1000 chars.'];
  if (tmdb_id && String(tmdb_id).length > 50) errors.tmdb_id = ['Max 50 chars.'];
  if (year && String(year).length > 10) errors.year = ['Max 10 chars.'];
  if (Object.keys(errors).length) return validationError(res, 'Validation failed', errors);
  try {
    const typeLc = String(type).toLowerCase();
    const titleLc = String(title).toLowerCase();
    const existing = await db('content_requests')
      .whereRaw('LOWER(type)=? AND LOWER(title)=?', [typeLc, titleLc])
      .first();
    if (existing) {
      const [row] = await db('content_requests')
        .where({ id: existing.id })
        .increment('request_count', 1)
        .returning('*');
      return ok(res, { request: row || existing, request_count: (existing.request_count || 0) + 1 }, 'Request already exists. We have updated the request count.');
    }
    const payload = {
      type: typeLc,
      title,
      email: email || null,
      description: description || null,
      tmdb_id: tmdb_id || null,
      year: year || null,
      status: 'pending',
      request_count: 1,
      requested_at: db.fn.now(),
      ip_address: req.ip,
      user_agent: String(req.headers['user-agent'] || '').slice(0, 255)
    };
    const [createdRow] = await db('content_requests').insert(payload).returning('*');
    // Try to send an acknowledgement email (non-blocking)
    if (email) {
      const subject = 'Content request received';
      const html = `<p>Thank you for your request: <strong>${title}</strong> (${typeLc}). We will review it shortly.</p>`;
      sendMail(email, subject, html);
    }
    return created(res, { request: createdRow || payload }, 'Content request submitted successfully');
  } catch (e) {
    return serverError(res, 'An error occurred while submitting your request. Please try again later.');
  }
}

async function listRequests(req, res) {
  const { type, status, search, sort_by = 'requested_at', sort_order = 'desc', page = 1, per_page = 20 } = req.query;
  try {
    const qb = db('content_requests as r').select('r.*').modify(qb => {
      if (type && REQUEST_TYPES.has(String(type).toLowerCase())) qb.where('r.type', String(type).toLowerCase());
      if (status && STATUS.has(String(status).toLowerCase())) qb.where('r.status', String(status).toLowerCase());
      if (search) qb.where('r.title', 'like', `%${search}%`);
    });
    const sortWhitelist = new Set(['requested_at', 'request_count', 'title', 'status']);
    const sort = sortWhitelist.has(String(sort_by)) ? String(sort_by) : 'requested_at';
    const dir = ['asc', 'desc'].includes(String(sort_order)) ? String(sort_order) : 'desc';
    qb.orderBy(`r.${sort}`, dir);
    const { items, pagination } = await paginate(qb, page, per_page);
    return ok(res, { requests: items, pagination });
  } catch (e) {
    return serverError(res, 'Failed to load content requests');
  }
}

module.exports = { submitRequest, listRequests };


