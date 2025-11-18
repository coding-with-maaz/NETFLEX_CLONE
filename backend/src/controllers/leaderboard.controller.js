const { db } = require('../db/knex.js');
const { ok, notFound, serverError } = require('../utils/responses.js');

async function trackMovieView(req, res) {
  const id = Number(req.params.id);
  try {
    const exists = await db('movies').where({ id }).first();
    if (!exists) return notFound(res, 'Movie not found');
    await db.transaction(async trx => {
      await trx('movies').where({ id }).increment('view_count', 1);
      await trx('views').insert({
        viewable_type: 'App\\Models\\Movie',
        viewable_id: id,
        viewed_at: trx.fn.now()
      });
    });
    return ok(res, {});
  } catch (e) {
    return serverError(res, 'Failed to track movie view');
  }
}

async function trackTvShowView(req, res) {
  const id = Number(req.params.id);
  try {
    const exists = await db('tv_shows').where({ id }).first();
    if (!exists) return notFound(res, 'TV show not found');
    await db.transaction(async trx => {
      await trx('tv_shows').where({ id }).increment('view_count', 1);
      await trx('views').insert({
        viewable_type: 'App\\Models\\TVShow',
        viewable_id: id,
        viewed_at: trx.fn.now()
      });
    });
    return ok(res, {});
  } catch (e) {
    return serverError(res, 'Failed to track TV show view');
  }
}

async function getTrending(req, res) {
  const { period = 'week', limit = 20 } = req.query;
  try {
    // Simple heuristic: order by view_count desc, fallback to recent created_at
    const lim = Math.max(1, Math.min(100, Number(limit)));
    const [moviesRows, tvShowsRows] = await Promise.all([
      db('movies').orderBy([{ column: 'view_count', order: 'desc' }, { column: 'created_at', order: 'desc' }]).limit(lim),
      db('tv_shows').orderBy([{ column: 'view_count', order: 'desc' }, { column: 'created_at', order: 'desc' }]).limit(lim)
    ]);
    const movies = moviesRows.map(it => ({ ...it, is_featured: it.is_featured ? true : false }));
    const tvShows = tvShowsRows.map(it => ({ ...it, is_featured: it.is_featured ? true : false }));
    return ok(res, { movies, tvShows, period });
  } catch (e) {
    return serverError(res, 'Failed to load trending');
  }
}

async function getTVShowsLeaderboard(req, res) {
  const { period = 'week', limit = 20 } = req.query;
  try {
    const lim = Math.max(1, Math.min(100, Number(limit)));
    let qb = db('tv_shows as t').select('t.*');
    
    // Filter by period based on views table
    if (period === 'today') {
      qb = qb.whereRaw('DATE(t.created_at) = CURDATE()');
    } else if (period === 'week') {
      qb = qb.whereRaw('t.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
    } else if (period === 'month') {
      qb = qb.whereRaw('t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
    }
    // 'overall' doesn't need date filtering
    
    const tvShowsRows = await qb
      .orderBy([{ column: 't.view_count', order: 'desc' }, { column: 't.created_at', order: 'desc' }])
      .limit(lim);
    
    const tvShows = tvShowsRows.map(it => ({
      ...it,
      is_featured: it.is_featured ? true : false
    }));
    
    return ok(res, { tvShows, period });
  } catch (e) {
    return serverError(res, 'Failed to load TV shows leaderboard');
  }
}

module.exports = { trackMovieView, trackTvShowView, getTrending, getTVShowsLeaderboard };


