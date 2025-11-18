const { Router } = require('express');
const { getComments, postComment } = require('../../controllers/comments.controller.js');

const router = Router();
router.get('/', getComments);
router.post('/', postComment);
module.exports = router;


