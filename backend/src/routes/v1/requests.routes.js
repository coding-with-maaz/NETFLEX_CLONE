const { Router } = require('express');
const { listRequests, submitRequest } = require('../../controllers/requests.controller.js');

const router = Router();
router.get('/', listRequests);
router.post('/', submitRequest);
module.exports = router;


