const express = require('express');
const helmet = require('helmet');
const { corsMiddleware } = require('./middleware/cors.js');
const { errorHandler, notFoundHandler } = require('./middleware/errorHandler.js');
const v1Routes = require('./routes/v1/index.js');

const app = express();

app.set('trust proxy', 1);
app.use(helmet());
app.use(express.json({ limit: '1mb' }));
app.use(express.urlencoded({ extended: true }));
app.use(corsMiddleware);

app.use('/api/v1', v1Routes);

app.use(notFoundHandler);
app.use(errorHandler);

module.exports = app;


