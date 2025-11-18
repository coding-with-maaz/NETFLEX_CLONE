const { db } = require('../db/knex.js');
const { ok, created, validationError, notFound, serverError } = require('../utils/responses.js');
const { sendMail } = require('../utils/mailer.js');

const TYPES = new Set(['movie', 'tvshow', 'episode']);

function mapTypeToModel(type) {
  if (type === 'movie') return { table: 'movies', className: 'App\\Models\\Movie' };
  if (type === 'tvshow') return { table: 'tv_shows', className: 'App\\Models\\TVShow' };
  return { table: 'episodes', className: 'App\\Models\\Episode' };
}

async function getComments(req, res) {
  const type = String(req.query.type || '').toLowerCase();
  const id = Number(req.query.id);
  if (!TYPES.has(type) || !id) {
    return validationError(res, 'Validation failed', { type: ['type must be movie|tvshow|episode'], id: ['id required'] });
  }
  try {
    const { className } = mapTypeToModel(type);
    // Return all comments regardless of status (pending, approved, etc.)
    const rows = await db('comments')
      .where({ commentable_type: className, commentable_id: id })
      .orderBy('created_at', 'asc');
    // Optionally nest replies if using parent_id
    const byId = new Map();
    rows.forEach(r => { byId.set(r.id, { ...r, replies: [] }); });
    const roots = [];
    rows.forEach(r => {
      const node = byId.get(r.id);
      if (r.parent_id && byId.get(r.parent_id)) {
        byId.get(r.parent_id).replies.push(node);
      } else {
        roots.push(node);
      }
    });
    return ok(res, { comments: roots });
  } catch (e) {
    return serverError(res, 'Failed to load comments');
  }
}

async function postComment(req, res) {
  const { type, id, name, email, comment, parent_id } = req.body || {};
  const errors = {};
  const t = String(type || '').toLowerCase();
  if (!TYPES.has(t)) errors.type = ['type must be movie|tvshow|episode'];
  if (!Number(id)) errors.id = ['id required'];
  if (!name || String(name).length > 255) errors.name = ['name required, <= 255 chars'];
  if (!email) errors.email = ['email required'];
  if (!comment || String(comment).length > 1000) errors.comment = ['comment required, <= 1000 chars'];
  if (Object.keys(errors).length) return validationError(res, 'Validation failed', errors);
  try {
    const { table, className } = mapTypeToModel(t);
    const exists = await db(table).where({ id: Number(id) }).first();
    if (!exists) return notFound(res, 'Content not found');
    const payload = {
      commentable_type: className,
      commentable_id: Number(id),
      parent_id: parent_id || null,
      name,
      email,
      comment,
      status: 'pending',
      is_admin_reply: 0,
      like_count: 0,
      dislike_count: 0,
      created_at: db.fn.now(),
      updated_at: db.fn.now()
    };
    const [row] = await db('comments').insert(payload).returning('*');
    // Send acknowledgement (non-blocking)
    const subject = 'Comment received';
    const html = `<p>Thanks ${name}, your comment is submitted and pending approval.</p>`;
    sendMail(email, subject, html);
    return created(res, { comment: row || payload }, 'Comment submitted and pending approval');
  } catch (e) {
    return serverError(res, 'Failed to submit comment');
  }
}

module.exports = { getComments, postComment };


