const { db } = require('../db/knex.js');
const { ok, serverError } = require('../utils/responses.js');

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

async function getLatestEpisodes(req, res) {
  const { limit = 20 } = req.query;
  try {
    const lim = Math.max(1, Math.min(100, Number(limit)));
    const { seasonsTvShowFkColumn, seasonNumberColumn } = await resolveSeasonColumns();
    
    console.log(`[getLatestEpisodes] Using column: ${seasonsTvShowFkColumn} for TV show FK`);
    
    const episodes = await db('episodes as e')
      .leftJoin('seasons as s', 's.id', 'e.season_id')
      .leftJoin('tv_shows as t', function() {
        this.on(db.raw(`t.id = s.${seasonsTvShowFkColumn}`));
      })
      .orderBy([{ column: 'e.id', order: 'desc' }])
      .limit(lim)
      .select(
        'e.id',
        'e.season_id',
        db.raw(`s.${seasonNumberColumn} as season_number`),
        'e.episode_number',
        'e.name',
        'e.overview',
        'e.still_path',
        'e.air_date',
        'e.created_at',
        'e.vote_average',
        'e.vote_count',
        'e.runtime',
        'e.view_count',
        db.raw(`s.${seasonsTvShowFkColumn} as tv_show_id`),
        't.id as tv_show_id_direct',
        't.name as tv_show_name',
        't.slug as tv_show_slug',
        't.poster_path as tv_show_poster_path',
        't.backdrop_path as tv_show_backdrop_path'
      );
    
    // Debug: log first episode raw data
    if (episodes.length > 0) {
      const firstEp = episodes[0];
      console.log(`[getLatestEpisodes] First episode raw data:`, {
        id: firstEp.id,
        season_id: firstEp.season_id,
        tv_show_id: firstEp.tv_show_id,
        tv_show_id_direct: firstEp.tv_show_id_direct,
        tv_show_name: firstEp.tv_show_name
      });
    }
    
    // Format episodes with nested TV show info
    const formattedEpisodes = episodes.map(ep => {
      const tvShowId = ep.tv_show_id || ep.tv_show_id_direct;
      
      const episode = {
        id: ep.id,
        season_id: ep.season_id,
        season_number: ep.season_number || 0,
        episode_number: ep.episode_number,
        name: ep.name,
        overview: ep.overview,
        still_path: ep.still_path,
        air_date: ep.air_date,
        created_at: ep.created_at,
        vote_average: ep.vote_average,
        vote_count: ep.vote_count,
        runtime: ep.runtime,
        view_count: ep.view_count,
        tv_show_id: tvShowId
      };
      
      // Add TV show info if available
      if (tvShowId) {
        episode.tv_show = {
          id: tvShowId,
          name: ep.tv_show_name || '',
          slug: ep.tv_show_slug || null,
          poster_path: ep.tv_show_poster_path || null,
          backdrop_path: ep.tv_show_backdrop_path || null
        };
      } else {
        // Debug: log when TV show info is missing with full episode data
        console.log(`[getLatestEpisodes] Episode ${ep.id} missing TV show info. Full episode data:`, {
          id: ep.id,
          name: ep.name,
          season_id: ep.season_id,
          season_number: ep.season_number,
          tv_show_id: ep.tv_show_id,
          tv_show_id_direct: ep.tv_show_id_direct,
          tv_show_name: ep.tv_show_name,
          tv_show_slug: ep.tv_show_slug
        });
      }
      
      return episode;
    });
    
    return ok(res, { episodes: formattedEpisodes });
  } catch (e) {
    console.error('Error in getLatestEpisodes:', e);
    return serverError(res, 'Failed to load latest episodes');
  }
}

async function getEpisodesByDate(req, res) {
  const { date } = req.query;
  try {
    const { seasonsTvShowFkColumn, seasonNumberColumn } = await resolveSeasonColumns();
    
    const qb = db('episodes as e')
      .leftJoin('seasons as s', 's.id', 'e.season_id')
      .leftJoin('tv_shows as t', function() {
        this.on(db.raw(`t.id = s.${seasonsTvShowFkColumn}`));
      })
      .select(
        'e.id',
        'e.season_id',
        db.raw(`s.${seasonNumberColumn} as season_number`),
        'e.episode_number',
        'e.name',
        'e.overview',
        'e.still_path',
        'e.air_date',
        'e.created_at',
        'e.vote_average',
        'e.vote_count',
        'e.runtime',
        'e.view_count',
        db.raw(`s.${seasonsTvShowFkColumn} as tv_show_id`),
        't.id as tv_show_id_direct',
        't.name as tv_show_name',
        't.slug as tv_show_slug',
        't.poster_path as tv_show_poster_path',
        't.backdrop_path as tv_show_backdrop_path'
      )
      .orderBy('e.created_at', 'desc');

    if (date) {
      qb.whereRaw('DATE(e.created_at) = ?', [date]);
    } else {
      qb.whereRaw('DATE(e.created_at) = CURDATE()');
    }

    const episodes = await qb.limit(100);
    
    // Format episodes with nested TV show info
    const formattedEpisodes = episodes.map(ep => {
      const tvShowId = ep.tv_show_id || ep.tv_show_id_direct;
      
      const episode = {
        id: ep.id,
        season_id: ep.season_id,
        season_number: ep.season_number || 0,
        episode_number: ep.episode_number,
        name: ep.name,
        overview: ep.overview,
        still_path: ep.still_path,
        air_date: ep.air_date,
        created_at: ep.created_at,
        vote_average: ep.vote_average,
        vote_count: ep.vote_count,
        runtime: ep.runtime,
        view_count: ep.view_count,
        tv_show_id: tvShowId
      };
      
      // Add TV show info if available
      if (tvShowId) {
        episode.tv_show = {
          id: tvShowId,
          name: ep.tv_show_name || '',
          slug: ep.tv_show_slug || null,
          poster_path: ep.tv_show_poster_path || null,
          backdrop_path: ep.tv_show_backdrop_path || null
        };
      } else {
        // Debug: log when TV show info is missing
        console.log(`[getEpisodesByDate] Episode ${ep.id} missing TV show info. season_id: ${ep.season_id}, tv_show_id: ${ep.tv_show_id}, tv_show_id_direct: ${ep.tv_show_id_direct}`);
      }
      
      return episode;
    });
    
    return ok(res, { episodes: formattedEpisodes });
  } catch (e) {
    console.error('Error in getEpisodesByDate:', e);
    return serverError(res, 'Failed to load episodes by date');
  }
}

async function getEpisodeEmbeds(req, res) {
  const episodeId = Number(req.params.id);
  try {
    const embeds = await db('episode_embeds')
      .where('episode_id', episodeId)
      .orderBy([{ column: 'priority', order: 'asc' }, { column: 'id', order: 'asc' }])
      .select('id', 'server_name', 'embed_url', 'priority', 'requires_ad', 'is_active');
    const normalized = embeds.map(e => ({
      id: e.id,
      server_name: e.server_name,
      embed_url: e.embed_url,
      priority: e.priority,
      requires_ad: e.requires_ad ? true : false,
      is_active: e.is_active ? true : false
    }));
    return ok(res, { embeds: normalized });
  } catch (e) {
    return serverError(res, 'Failed to load episode embeds');
  }
}

module.exports = { getLatestEpisodes, getEpisodesByDate, getEpisodeEmbeds };


