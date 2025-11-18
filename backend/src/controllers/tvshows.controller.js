const { db } = require('../db/knex.js');
const { ok, notFound, serverError } = require('../utils/responses.js');
const { paginate } = require('../utils/pagination.js');

// Cache resolved column names to avoid repeated information_schema lookups
let seasonsTvShowFkColumn = null; // 'tv_show_id' or 'tvshow_id'
let seasonNumberColumn = null; // 'season_number' or 'number'

async function resolveSeasonColumns() {
  if (seasonsTvShowFkColumn && seasonNumberColumn) return { seasonsTvShowFkColumn, seasonNumberColumn };
  try {
    const dbName = db?.client?.config?.connection?.database;
    const rows = await db('information_schema.COLUMNS')
      .whereIn('COLUMN_NAME', ['tv_show_id', 'tvshow_id', 'season_number', 'number'])
      .andWhere('TABLE_NAME', 'seasons')
      .modify(qb => {
        if (dbName) qb.andWhere('TABLE_SCHEMA', dbName);
      })
      .select('COLUMN_NAME');
    const names = new Set(rows.map(r => r.COLUMN_NAME));
    seasonsTvShowFkColumn = names.has('tv_show_id') ? 'tv_show_id' : (names.has('tvshow_id') ? 'tvshow_id' : 'tv_show_id');
    seasonNumberColumn = names.has('season_number') ? 'season_number' : (names.has('number') ? 'number' : 'season_number');
  } catch (e) {
    // Fallbacks
    seasonsTvShowFkColumn = 'tv_show_id';
    seasonNumberColumn = 'season_number';
  }
  return { seasonsTvShowFkColumn, seasonNumberColumn };
}

async function listTvShows(req, res) {
  const { q, genre, category, year, status, is_featured, sort_by, order, page = 1, limit = 20 } = req.query;
  try {
    // Resolve category ID if category filter is provided (outside modify to handle async properly)
    let categoryId = null;
    if (category) {
      const categoryParam = String(category);
      console.log('[TVShows] Filtering by category:', categoryParam);
      categoryId = Number(categoryParam);
      if (!Number.isFinite(categoryId)) {
        // Try to find by slug first
        const c = await db('categories').whereRaw('LOWER(slug)=LOWER(?)', [categoryParam]).first('id');
        if (c) {
          categoryId = c.id;
          console.log('[TVShows] Found category by slug:', categoryId);
        } else {
          // Try to find by name
          const cByName = await db('categories').whereRaw('LOWER(name)=LOWER(?)', [categoryParam]).first('id');
          if (cByName) {
            categoryId = cByName.id;
            console.log('[TVShows] Found category by name:', categoryId);
          } else {
            console.log('[TVShows] Category not found:', categoryParam);
            categoryId = null;
          }
        }
      } else {
        console.log('[TVShows] Using category ID directly:', categoryId);
      }
    }

    // Resolve genre ID if genre filter is provided
    let genreId = null;
    if (genre) {
      const genreParam = String(genre);
      genreId = Number(genreParam);
      if (!Number.isFinite(genreId)) {
        const g = await db('genres').whereRaw('LOWER(slug)=LOWER(?)', [genreParam]).first('id');
        genreId = g?.id || null;
      }
    }

    const qb = db('tv_shows as t').select('t.*').modify(qb => {
      if (q) qb.where('t.name', 'like', `%${q}%`);
      if (genreId) {
        qb.whereExists(
          db('tv_show_genre')
            .whereRaw('tv_show_genre.tv_show_id = t.id')
            .andWhere('tv_show_genre.genre_id', genreId)
        );
      } else if (genre) {
        // Genre was provided but not found - return no results
        qb.whereRaw('1=0');
      }
      if (categoryId) {
        qb.where('t.category_id', categoryId);
        console.log('[TVShows] Applied category filter with ID:', categoryId);
      } else if (category) {
        // Category was provided but not found - return no results
        console.log('[TVShows] Category not found, returning no results');
        qb.whereRaw('1=0');
      }
      if (year) qb.whereRaw('YEAR(t.first_air_date) = ?', [Number(year)]);
      if (status) qb.where('t.status', String(status));
      if (typeof is_featured !== 'undefined') {
        const val = String(is_featured).toLowerCase();
        if (val === 'true' || val === '1') qb.where('t.is_featured', 1);
        if (val === 'false' || val === '0') qb.where('t.is_featured', 0);
      }
    });
    const sort = ['id', 'created_at', 'first_air_date', 'vote_average', 'view_count', 'popularity'].includes(String(sort_by)) ? sort_by : 'created_at';
    const dir = ['asc', 'desc'].includes(String(order)) ? order : 'desc';
    qb.orderBy(`t.${sort}`, dir);
    const { items, pagination } = await paginate(qb, page, limit);
    const tvShows = items.map(it => ({
      ...it,
      is_featured: it.is_featured ? true : false
    }));
    return ok(res, { tvShows, pagination });
  } catch (e) {
    return serverError(res, 'Failed to load TV shows');
  }
}

async function getTvShowDetail(req, res) {
  const id = Number(req.params.id);
  try {
    const tv = await db('tv_shows as t').where('t.id', id).first('t.*');
    if (!tv) return notFound(res, 'TV show not found');
    const [genres, category, seasons] = await Promise.all([
      db('genres as g')
        .join('tv_show_genre as tg', 'tg.genre_id', 'g.id')
        .where('tg.tv_show_id', id)
        .select('g.id', 'g.name', 'g.slug'),
      db('categories').where('id', tv.category_id).first('id', 'name', 'slug'),
      db('seasons as s').where('s.tv_show_id', id).orderBy('s.season_number', 'asc')
        .select('s.id', 's.season_number', 's.name', 's.episode_count')
    ]);
    const seasonsWithEpisodes = await Promise.all(seasons.map(async (s) => {
      const episodes = await db('episodes as e')
        .where('e.season_id', s.id)
        .orderBy('e.episode_number', 'asc')
        .select('e.id', 'e.episode_number', 'e.name', 'e.overview', 'e.still_path', 'e.air_date');
      return { ...s, episodes };
    }));
    return ok(res, {
      id: tv.id,
      name: tv.name,
      overview: tv.overview,
      poster_path: tv.poster_path,
      backdrop_path: tv.backdrop_path,
      first_air_date: tv.first_air_date,
      number_of_seasons: tv.number_of_seasons,
      number_of_episodes: tv.number_of_episodes,
      vote_average: tv.vote_average,
      view_count: tv.view_count,
      status: tv.status || 'active',
      is_featured: tv.is_featured ? true : false,
      category,
      genres,
      seasons: seasonsWithEpisodes,
      created_at: tv.created_at
    });
  } catch (e) {
    return serverError(res, 'Failed to load TV show detail');
  }
}

async function getTvShowSeasons(req, res) {
  const tvShowId = Number(req.params.id);
  try {
    const tv = await db('tv_shows').where('id', tvShowId).first('id');
    if (!tv) return notFound(res, 'TV show not found');
    const { seasonsTvShowFkColumn, seasonNumberColumn } = await resolveSeasonColumns();
    const seasons = await db('seasons as s')
      .where(`s.${seasonsTvShowFkColumn}`, tvShowId)
      .orderBy('s.season_number', 'asc')
      .select(
        's.id',
        db.raw(`s.${seasonNumberColumn} as season_number`),
        's.name',
        's.episode_count'
      );
    return ok(res, { seasons });
  } catch (e) {
    return serverError(res, 'Failed to load seasons');
  }
}

async function getSeasonEpisodes(req, res) {
  const tvShowId = Number(req.params.id);
  const seasonId = Number(req.params.seasonId);
  try {
    const { seasonsTvShowFkColumn } = await resolveSeasonColumns();
    const season = await db('seasons')
      .where('id', seasonId)
      .andWhere(seasonsTvShowFkColumn, tvShowId)
      .first('id');
    if (!season) return notFound(res, 'Season not found');
    const episodes = await db('episodes as e')
      .where('e.season_id', seasonId)
      .orderBy('e.episode_number', 'asc')
      .select(
        'e.id',
        'e.season_id',
        'e.episode_number',
        'e.name',
        'e.overview',
        'e.still_path',
        'e.air_date',
        'e.vote_average',
        'e.vote_count',
        'e.runtime',
        'e.view_count'
      );
    // Optionally include embeds/downloads to avoid extra calls
    const episodeIds = episodes.map(e => e.id);
    const [embeds, downloads] = await Promise.all([
      db('episode_embeds').whereIn('episode_id', episodeIds).select('id', 'episode_id', 'server_name', 'embed_url', 'priority', 'requires_ad', 'is_active'),
      db('episode_downloads').whereIn('episode_id', episodeIds).select('id', 'episode_id', 'server_name', 'download_url', 'quality', 'size', 'priority', 'is_active')
    ]);
    const episodeIdToEmbeds = new Map();
    const episodeIdToDownloads = new Map();
    embeds.forEach(em => {
      const arr = episodeIdToEmbeds.get(em.episode_id) || [];
      arr.push({
        id: em.id,
        server_name: em.server_name,
        embed_url: em.embed_url,
        priority: em.priority,
        requires_ad: em.requires_ad ? true : false,
        is_active: em.is_active ? true : false
      });
      episodeIdToEmbeds.set(em.episode_id, arr);
    });
    downloads.forEach(dl => {
      const arr = episodeIdToDownloads.get(dl.episode_id) || [];
      arr.push({
        id: dl.id,
        server_name: dl.server_name,
        download_url: dl.download_url,
        quality: dl.quality,
        size: dl.size,
        priority: dl.priority,
        is_active: dl.is_active ? true : false
      });
      episodeIdToDownloads.set(dl.episode_id, arr);
    });
    const result = episodes.map(e => ({
      ...e,
      embeds: episodeIdToEmbeds.get(e.id) || [],
      downloads: episodeIdToDownloads.get(e.id) || []
    }));
    return ok(res, { episodes: result });
  } catch (e) {
    return serverError(res, 'Failed to load episodes');
  }
}

module.exports = { listTvShows, getTvShowDetail, getTvShowSeasons, getSeasonEpisodes };

