require('./setupEnv.js');
const app = require('./app.js');
const pino = require('pino');

const logger = pino({ level: process.env.NODE_ENV === 'production' ? 'info' : 'debug' });
const port = Number(process.env.PORT || 8080);

app.listen(port, () => {
  logger.info({ port }, 'Nazaara Box public API server started');
});


