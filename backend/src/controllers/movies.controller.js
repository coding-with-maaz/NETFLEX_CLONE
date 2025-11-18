const { db } = require('../db/knex.js');
const { ok, notFound, serverError } = require('../utils/responses.js');
const { paginate } = require('../utils/pagination.js');

async function listMovies(req, res) {
  const { q, genre, year, language, is_featured, sort_by, order, page = 1, limit = 20 } = req.query;
  try {
    const qb = db('movies as m').select('m.*').modify(async qb => {
      if (q) qb.where('m.title', 'like', `%${q}%`);
      if (genre) {
        const genreParam = String(genre);
        let genreId = Number(genreParam);
        if (!Number.isFinite(genreId)) {
          const g = await db('genres').whereRaw('LOWER(slug)=LOWER(?)', [genreParam]).first('id');
          genreId = g?.id || null;
        }
        if (genreId) {
          qb.whereExists(
            db('movie_genre')
              .whereRaw('movie_genre.movie_id = m.id')
              .andWhere('movie_genre.genre_id', genreId)
          );
        } else {
          // If genre not found, force no results
          qb.whereRaw('1=0');
        }
      }
      if (year) qb.whereRaw('YEAR(m.release_date) = ?', [Number(year)]);
      if (language) qb.where('m.dubbing_language_id', Number(language));
      if (typeof is_featured !== 'undefined') {
        const val = String(is_featured).toLowerCase();
        if (val === 'true' || val === '1') qb.where('m.is_featured', 1);
        if (val === 'false' || val === '0') qb.where('m.is_featured', 0);
      }
    });
    const sort = ['id', 'created_at', 'release_date', 'vote_average', 'view_count', 'popularity'].includes(String(sort_by)) ? sort_by : 'created_at';
    const dir = ['asc', 'desc'].includes(String(order)) ? order : 'desc';
    qb.orderBy(`m.${sort}`, dir);
    const { items, pagination } = await paginate(qb, page, limit);
    // Normalize booleans/numbers as needed for app expectations
    const movies = items.map(it => ({
      ...it,
      is_featured: it.is_featured ? true : false
    }));
    return ok(res, { movies, pagination });
  } catch (e) {
    return serverError(res, 'Failed to load movies');
  }
}

async function getMovieDetail(req, res) {
  const id = Number(req.params.id);
  try {
    const movie = await db('movies as m').where('m.id', id).first('m.*');
    if (!movie) return notFound(res, 'Movie not found');
    const [genres, category, embeds, downloads] = await Promise.all([
      db('genres as g')
        .join('movie_genre as mg', 'mg.genre_id', 'g.id')
        .where('mg.movie_id', id)
        .select('g.id', 'g.name', 'g.slug'),
      db('categories').where('id', movie.category_id).first('id', 'name', 'slug'),
      db('movie_embeds').where('movie_id', id).select('id', 'server_name', 'embed_url', 'priority'),
      db('movie_downloads').where('movie_id', id).select('id', 'quality', 'download_url', 'size')
    ]);
    return ok(res, {
      id: movie.id,
      title: movie.title,
      overview: movie.overview,
      poster_path: movie.poster_path,
      backdrop_path: movie.backdrop_path,
      release_date: movie.release_date,
      runtime: movie.runtime,
      vote_average: movie.vote_average,
      vote_count: movie.vote_count,
      view_count: movie.view_count,
      status: movie.status || 'active',
      is_featured: movie.is_featured ? true : false,
      category,
      genres,
      embeds,
      downloads,
      created_at: movie.created_at
    });
  } catch (e) {
    return serverError(res, 'Failed to load movie detail');
  }
}

async function getTrendingMovies(req, res) {
  const { period = 'week', limit = 20 } = req.query;
  try {
    const lim = Math.max(1, Math.min(100, Number(limit)));
    const rows = await db('movies')
      .orderBy([{ column: 'view_count', order: 'desc' }, { column: 'created_at', order: 'desc' }])
      .limit(lim);
    const movies = rows.map(it => ({ ...it, is_featured: it.is_featured ? true : false }));
    return ok(res, { movies, period });
  } catch (e) {
    return serverError(res, 'Failed to load trending movies');
  }
}

async function getTopRatedMovies(req, res) {
  const { limit = 20 } = req.query;
  try {
    const lim = Math.max(1, Math.min(100, Number(limit)));
    const rows = await db('movies')
      .orderBy([{ column: 'vote_average', order: 'desc' }, { column: 'vote_count', order: 'desc' }])
      .limit(lim);
    const movies = rows.map(it => ({ ...it, is_featured: it.is_featured ? true : false }));
    return ok(res, { movies });
  } catch (e) {
    return serverError(res, 'Failed to load top rated movies');
  }
}

// Movies by upload date (created_at) - used by TodayMoviesPage
async function getMoviesByDate(req, res) {
  const { date } = req.query;
  try {
    const qb = db('movies as m')
      .where('m.status', 'active')
      .orderBy('m.created_at', 'desc');

    if (date) {
      qb.whereRaw('DATE(m.created_at) = ?', [date]);
    } else {
      qb.whereRaw('DATE(m.created_at) = CURDATE()');
    }

    const rows = await qb.select('m.*');
    const movies = rows.map(it => ({
      ...it,
      is_featured: it.is_featured ? true : false
    }));
    return ok(res, { movies });
  } catch (e) {
    return serverError(res, 'Failed to load movies by date');
  }
}

async function getMovieEmbeds(req, res) {
  const movieId = Number(req.params.id);
  try {
    // Select all available columns (some may not exist in all databases)
    const embeds = await db('movie_embeds')
      .where('movie_id', movieId)
      .orderBy([{ column: 'priority', order: 'asc' }, { column: 'id', order: 'asc' }])
      .select('*');
    const normalized = embeds.map(e => ({
      id: e.id,
      server_name: e.server_name,
      embed_url: e.embed_url,
      priority: e.priority || 0,
      requires_ad: e.requires_ad ? true : false,
      is_active: e.is_active !== undefined ? (e.is_active ? true : false) : true
    }));
    return ok(res, { embeds: normalized });
  } catch (e) {
    return serverError(res, 'Failed to load movie embeds');
  }
}

module.exports = { listMovies, getMovieDetail, getTrendingMovies, getTopRatedMovies, getMoviesByDate, getMovieEmbeds };


