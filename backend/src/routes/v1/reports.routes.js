const { Router } = require('express');
const { listEmbedReports, submitEmbedReport } = require('../../controllers/reports.controller.js');

const router = Router();
router.get('/embed', listEmbedReports);
router.post('/embed', submitEmbedReport);
module.exports = router;


