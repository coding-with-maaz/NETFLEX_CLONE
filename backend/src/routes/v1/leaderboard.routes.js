const { Router } = require('express');
const { trackMovieView, trackTvShowView, getTrending, getTVShowsLeaderboard } = require('../../controllers/leaderboard.controller.js');

const router = Router();
router.get('/trending', getTrending);
// More specific routes must come before parameterized routes
router.get('/tvshows/leaderboard', getTVShowsLeaderboard);
router.post('/movies/:id/view', trackMovieView);
router.post('/tvshows/:id/view', trackTvShowView);
module.exports = router;


