async function paginate(queryBuilder, page = 1, perPage = 20) {
  const pageNum = Math.max(1, Number(page || 1));
  const limit = Math.min(100, Math.max(1, Number(perPage || 20)));
  const [{ count }] = await queryBuilder.clone().clearSelect().clearOrder().count({ count: '*' });
  const items = await queryBuilder.offset((pageNum - 1) * limit).limit(limit);
  const total = Number(count || 0);
  const last = Math.max(1, Math.ceil(total / limit));
  const from = items.length ? (pageNum - 1) * limit + 1 : 0;
  const to = (pageNum - 1) * limit + items.length;
  return { items, pagination: { current_page: pageNum, last_page: last, per_page: limit, total, from, to } };
}

module.exports = { paginate };


