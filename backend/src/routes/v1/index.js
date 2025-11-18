const { Router } = require('express');
const utilsRoutes = require('./utils.routes.js');
const searchRoutes = require('./search.routes.js');
const leaderboardRoutes = require('./leaderboard.routes.js');
const requestsRoutes = require('./requests.routes.js');
const reportsRoutes = require('./reports.routes.js');
const commentsRoutes = require('./comments.routes.js');
const moviesRoutes = require('./movies.routes.js');
const tvshowsRoutes = require('./tvshows.routes.js');
const episodesRoutes = require('./episodes.routes.js');
const { getEpisodeEmbeds } = require('../../controllers/episodes.controller.js');
const { getMovieEmbeds } = require('../../controllers/movies.controller.js');

const router = Router();

router.use('/utils', utilsRoutes);
router.use('/search', searchRoutes);
router.use('/leaderboard', leaderboardRoutes);
router.use('/requests', requestsRoutes);
router.use('/reports', reportsRoutes);
router.use('/comments', commentsRoutes);
router.use('/movies', moviesRoutes);
router.use('/tvshows', tvshowsRoutes);
router.use('/episodes', episodesRoutes);
// Alternate embeds paths used by Laravel page
router.get('/embeds/episodes/:id', getEpisodeEmbeds);
router.get('/embeds/movies/:id', getMovieEmbeds);

module.exports = router;


