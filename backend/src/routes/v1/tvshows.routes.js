const { Router } = require('express');
const { listTvShows, getTvShowDetail, getTvShowSeasons, getSeasonEpisodes } = require('../../controllers/tvshows.controller.js');

const router = Router();
router.get('/', listTvShows);
router.get('/:id', getTvShowDetail);
router.get('/:id/seasons', getTvShowSeasons);
router.get('/:id/seasons/:seasonId/episodes', getSeasonEpisodes);
module.exports = router;


