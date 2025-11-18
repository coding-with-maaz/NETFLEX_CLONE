const { Router } = require('express');
const { globalSearch, searchMovies, searchTvShows, searchEpisodes } = require('../../controllers/search.controller.js');

const router = Router();
router.get('/', globalSearch);
router.get('/movies', searchMovies);
router.get('/tvshows', searchTvShows);
router.get('/episodes', searchEpisodes);
module.exports = router;


