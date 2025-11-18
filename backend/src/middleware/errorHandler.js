const { serverError } = require('../utils/responses.js');

function notFoundHandler(req, res, next) {
  return res.status(404).json({ success: false, message: 'Not Found' });
}

function errorHandler(err, req, res, next) {
  if (res.headersSent) return next(err);
  const message = process.env.NODE_ENV === 'production' ? 'Internal Server Error' : err?.message || 'Error';
  return serverError(res, message);
}

module.exports = { notFoundHandler, errorHandler };


