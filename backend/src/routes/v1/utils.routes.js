const { Router } = require('express');
const { getAllUtils } = require('../../controllers/utils.controller.js');

const router = Router();
router.get('/all', getAllUtils);
module.exports = router;


