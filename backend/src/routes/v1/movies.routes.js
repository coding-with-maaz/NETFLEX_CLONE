const { Router } = require('express');
const { listMovies, getMovieDetail, getTrendingMovies, getTopRatedMovies, getMoviesByDate, getMovieEmbeds } = require('../../controllers/movies.controller.js');

const router = Router();
router.get('/', listMovies);
router.get('/trending', getTrendingMovies);
router.get('/top-rated', getTopRatedMovies);
// Movies by upload/air date (used by TodayMoviesPage)
router.get('/today/all', getMoviesByDate);
// More specific routes must come before /:id
router.get('/:id/embeds', getMovieEmbeds);
router.get('/:id', getMovieDetail);
module.exports = router;


