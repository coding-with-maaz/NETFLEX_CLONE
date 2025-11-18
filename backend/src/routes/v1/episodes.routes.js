const { Router } = require('express');
const { getLatestEpisodes, getEpisodesByDate, getEpisodeEmbeds } = require('../../controllers/episodes.controller.js');

const router = Router();
router.get('/latest/all', getLatestEpisodes);
router.get('/today/all', getEpisodesByDate);
router.get('/:id/embeds', getEpisodeEmbeds);
// Alternate path used by Laravel frontend: /embeds/episodes/:id
router.get('/embeds/episodes/:id', getEpisodeEmbeds);
module.exports = router;


