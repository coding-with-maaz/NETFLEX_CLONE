const { db } = require('../db/knex.js');
const { ok, serverError } = require('../utils/responses.js');
const { paginate } = require('../utils/pagination.js');

async function globalSearch(req, res) {
  const { q = '', type = 'all', page = 1, limit = 20 } = req.query;
  try {
    if (type === 'movies') {
      const qb = db('movies as m').modify(qb => {
        if (q) qb.where('m.title', 'like', `%${q}%`);
      }).orderBy('m.created_at', 'desc');
      const { items, pagination } = await paginate(qb, page, limit);
      return ok(res, { movies: items, pagination });
    }
    if (type === 'tvshows') {
      const qb = db('tv_shows as t').modify(qb => {
        if (q) qb.where('t.name', 'like', `%${q}%`);
      }).orderBy('t.created_at', 'desc');
      const { items, pagination } = await paginate(qb, page, limit);
      return ok(res, { tvShows: items, pagination });
    }
    if (type === 'episodes') {
      const qb = db('episodes as e').modify(qb => {
        if (q) qb.where('e.name', 'like', `%${q}%`);
      }).orderBy('e.created_at', 'desc');
      const { items, pagination } = await paginate(qb, page, limit);
      return ok(res, { episodes: items, pagination });
    }
    // type=all: limited page per bucket
    const [movies, tvshows, episodes] = await Promise.all([
      db('movies').modify(qb => { if (q) qb.where('title', 'like', `%${q}%`); }).orderBy('created_at', 'desc').limit(Number(limit)),
      db('tv_shows').modify(qb => { if (q) qb.where('name', 'like', `%${q}%`); }).orderBy('created_at', 'desc').limit(Number(limit)),
      db('episodes').modify(qb => { if (q) qb.where('name', 'like', `%${q}%`); }).orderBy('created_at', 'desc').limit(Number(limit))
    ]);
    return ok(res, { movies, tvshows, episodes });
  } catch (e) {
    return serverError(res, 'Search failed');
  }
}

async function searchMovies(req, res) {
  const { q, genre, year, language, sort_by, order, page = 1, limit = 20 } = req.query;
  try {
    const qb = db('movies as m').select('m.*').modify(qb => {
      if (q) qb.where('m.title', 'like', `%${q}%`);
      if (genre) qb.whereExists(db('movie_genre').whereRaw('movie_genre.movie_id = m.id').andWhere('movie_genre.genre_id', Number(genre)));
      if (year) qb.whereRaw('YEAR(m.release_date) = ?', [Number(year)]);
      if (language) qb.where('m.dubbing_language_id', Number(language));
    });
    const sort = ['created_at', 'release_date', 'vote_average', 'view_count'].includes(String(sort_by)) ? sort_by : 'created_at';
    const dir = ['asc', 'desc'].includes(String(order)) ? order : 'desc';
    qb.orderBy(`m.${sort}`, dir);
    const { items, pagination } = await paginate(qb, page, limit);
    return ok(res, { movies: items, pagination });
  } catch (e) {
    return serverError(res, 'Movie search failed');
  }
}

async function searchTvShows(req, res) {
  const { q, genre, year, sort_by, order, page = 1, limit = 20 } = req.query;
  try {
    const qb = db('tv_shows as t').select('t.*').modify(qb => {
      if (q) qb.where('t.name', 'like', `%${q}%`);
      if (genre) qb.whereExists(db('tv_show_genre').whereRaw('tv_show_genre.tv_show_id = t.id').andWhere('tv_show_genre.genre_id', Number(genre)));
      if (year) qb.whereRaw('YEAR(t.first_air_date) = ?', [Number(year)]);
    });
    const sort = ['created_at', 'first_air_date', 'vote_average', 'view_count'].includes(String(sort_by)) ? sort_by : 'created_at';
    const dir = ['asc', 'desc'].includes(String(order)) ? order : 'desc';
    qb.orderBy(`t.${sort}`, dir);
    const { items, pagination } = await paginate(qb, page, limit);
    return ok(res, { tvshows: items, pagination });
  } catch (e) {
    return serverError(res, 'TV show search failed');
  }
}

async function searchEpisodes(req, res) {
  const { q, tvshow_id, season_id, genre, air_date_from, air_date_to, sort_by, order, page = 1, limit = 20 } = req.query;
  try {
    const qb = db('episodes as e').select('e.*')
      .leftJoin('seasons as s', 's.id', 'e.season_id')
      .leftJoin('tv_shows as t', 't.id', 's.tv_show_id')
      .modify(qb => {
        if (q) qb.where('e.name', 'like', `%${q}%`);
        if (season_id) qb.where('e.season_id', Number(season_id));
        if (tvshow_id) qb.where('s.tv_show_id', Number(tvshow_id));
        if (air_date_from) qb.where('e.air_date', '>=', air_date_from);
        if (air_date_to) qb.where('e.air_date', '<=', air_date_to);
        if (genre) qb.whereExists(
          db('tv_show_genre').whereRaw('tv_show_genre.tv_show_id = t.id').andWhere('tv_show_genre.genre_id', Number(genre))
        );
      });
    const sort = ['created_at', 'air_date', 'view_count'].includes(String(sort_by)) ? sort_by : 'created_at';
    const dir = ['asc', 'desc'].includes(String(order)) ? order : 'desc';
    qb.orderBy(`e.${sort}`, dir);
    const { items, pagination } = await paginate(qb, page, limit);
    return ok(res, { episodes: items, pagination });
  } catch (e) {
    return serverError(res, 'Episode search failed');
  }
}

module.exports = { globalSearch, searchMovies, searchTvShows, searchEpisodes };


