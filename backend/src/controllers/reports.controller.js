const { db } = require('../db/knex.js');
const { ok, created, validationError, notFound, serverError } = require('../utils/responses.js');
const { paginate } = require('../utils/pagination.js');

const CONTENT_TYPES = new Set(['movie', 'episode']);
const REPORT_TYPES = new Set(['not_working', 'wrong_content', 'poor_quality', 'broken_link', 'other']);
const STATUS = new Set(['pending', 'approved', 'rejected', 'resolved', 'reviewed', 'fixed', 'dismissed']);

async function submitEmbedReport(req, res) {
  const { content_type, content_id, embed_id, report_type, description, email } = req.body || {};
  const errors = {};
  const ct = String(content_type || '').toLowerCase();
  if (!CONTENT_TYPES.has(ct)) errors.content_type = ['The content type field is required.'];
  const cid = Number(content_id);
  if (!cid) errors.content_id = ['The content id field is required.'];
  if (!REPORT_TYPES.has(String(report_type))) errors.report_type = ['The report type field is required.'];
  if (description && String(description).length > 1000) errors.description = ['Max 1000 chars.'];
  if (Object.keys(errors).length) return validationError(res, 'Validation failed', errors);
  try {
    const table = ct === 'movie' ? 'movies' : 'episodes';
    const exists = await db(table).where({ id: cid }).first();
    if (!exists) return notFound(res, ct === 'movie' ? 'Movie not found' : 'Episode not found');
    const key = { content_type: ct, content_id: cid, report_type: String(report_type), embed_id: embed_id || null };
    const existing = await db('embed_reports').where(key).first();
    if (existing) {
      const [row] = await db('embed_reports').where({ id: existing.id }).increment('report_count', 1).returning('*');
      return ok(res, { report: row || existing, report_count: (existing.report_count || 0) + 1 }, 'Report already exists. We have updated the report count.');
    }
    const payload = {
      ...key,
      description: description || null,
      email: email || null,
      status: 'pending',
      report_count: 1,
      reported_at: db.fn.now(),
      ip_address: req.ip,
      user_agent: String(req.headers['user-agent'] || '').slice(0, 255)
    };
    const [createdRow] = await db('embed_reports').insert(payload).returning('*');
    return created(res, { report: createdRow || payload }, 'Embed problem reported successfully');
  } catch (e) {
    return serverError(res, 'Failed to submit embed report');
  }
}

async function listEmbedReports(req, res) {
  const { content_type, content_id, status, report_type, sort_by = 'reported_at', sort_order = 'desc', page = 1, per_page = 20 } = req.query;
  try {
    const qb = db('embed_reports as r').select('r.*').modify(qb => {
      if (content_type && CONTENT_TYPES.has(String(content_type).toLowerCase())) qb.where('r.content_type', String(content_type).toLowerCase());
      if (content_id) qb.where('r.content_id', Number(content_id));
      if (status && STATUS.has(String(status).toLowerCase())) qb.where('r.status', String(status).toLowerCase());
      if (report_type && REPORT_TYPES.has(String(report_type))) qb.where('r.report_type', String(report_type));
    });
    const sortWhitelist = new Set(['reported_at', 'report_count', 'report_type', 'status']);
    const sort = sortWhitelist.has(String(sort_by)) ? String(sort_by) : 'reported_at';
    const dir = ['asc', 'desc'].includes(String(sort_order)) ? String(sort_order) : 'desc';
    qb.orderBy(`r.${sort}`, dir);
    const { items, pagination } = await paginate(qb, page, per_page);
    return ok(res, { reports: items, pagination });
  } catch (e) {
    return serverError(res, 'Failed to load embed reports');
  }
}

module.exports = { submitEmbedReport, listEmbedReports };


