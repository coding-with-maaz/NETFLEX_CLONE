const ok = (res, data, message) => {
  return res.json({ success: true, message, data });
};

const created = (res, data, message) => {
  return res.status(201).json({ success: true, message, data });
};

const validationError = (res, message, errors) => {
  return res.status(422).json({ success: false, message, errors });
};

const notFound = (res, message) => {
  return res.status(404).json({ success: false, message });
};

const serverError = (res, message) => {
  return res.status(500).json({ success: false, message });
};

module.exports = { ok, created, validationError, notFound, serverError };


