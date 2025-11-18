## Nazaara Box – Public API Backend (Node.js) Documentation

This document defines a Node.js backend that provides only the public APIs used by the Nazaara Box Flutter app, while connecting to the exact same database used by the existing Laravel project. It mirrors the public endpoints and response contracts so the mobile/web frontend can work without code changes beyond the base URL. This Node service owns the public API base URL and replaces the Laravel public API endpoints.


### 1) Goals and Scope

- Provide public-only REST APIs for:
  - Utilities and Search
  - View tracking (leaderboard counters)
  - Content Requests (submit + list)
  - Embed Reports (submit + list)
  - Comments (submit + list, optional if used by app)
- Connect to the same relational database as Laravel with no schema changes.
- Maintain identical response shapes, pagination, and validation semantics.
- Exclude protected endpoints (e.g., movie/TV show detail, embeds/downloads lists behind API key).


### 2) Tech Stack (Recommended)

- Runtime: Node.js 18+
- Framework: Express 4+
- Language: TypeScript
- Database: Knex.js (query builder) or Prisma (ORM) — this doc shows Knex
- Validation: Zod or Joi
- Security / Ops: Helmet, express-rate-limit, CORS, pino (logging), dotenv


### 3) Project Structure (Example)

```
backend/
  src/
    server.ts
    app.ts
    routes/
      v1/
        index.ts
        utils.routes.ts
        search.routes.ts
        leaderboard.routes.ts
        requests.routes.ts
        reports.routes.ts
        comments.routes.ts
    controllers/
      utils.controller.ts
      search.controller.ts
      leaderboard.controller.ts
      requests.controller.ts
      reports.controller.ts
      comments.controller.ts
    services/
      search.service.ts
      requests.service.ts
      reports.service.ts
      comments.service.ts
    db/
      knex.ts
    middleware/
      errorHandler.ts
      rateLimit.ts
      cors.ts
    utils/
      pagination.ts
      responses.ts
      validation.ts
  package.json
  tsconfig.json
  .env.example
```


### 4) Environment Configuration

- Use the same DB as Laravel. Example for MySQL:
```
DB_CLIENT=mysql2
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=laravel_user
DB_PASS=laravel_pass
DB_NAME=laravel_db

PORT=8080
NODE_ENV=production
CORS_ORIGINS=https://nazaarabox.com,https://harpaljob.com
```

Knex bootstrap:
```
import knex from 'knex';

export const db = knex({
  client: process.env.DB_CLIENT,             // 'mysql2' | 'pg' | 'sqlite3'
  connection: {
    host: process.env.DB_HOST,
    port: Number(process.env.DB_PORT),
    user: process.env.DB_USER,
    password: process.env.DB_PASS,
    database: process.env.DB_NAME
  },
  useNullAsDefault: process.env.DB_CLIENT === 'sqlite3'
});
```


### 5) API Base and Response Contract

- Base path: `/api/v1` (Node backend)
- Success:
```
{
  "success": true,
  "message": "Optional message",
  "data": {},
  "pagination": { "...": "..." } // only when paginated
}
```
- Error:
```
{
  "success": false,
  "message": "What went wrong",
  "errors": { "field": ["Validation details..."] }
}
```
- Pagination keys: `current_page`, `last_page`, `per_page`, `total`, `from`, `to`. `per_page` capped at 100.


### 6) Database Model Assumptions (No Changes)

Core tables (must already exist per Laravel):
- Content: `movies`, `tv_shows`, `seasons`, `episodes`
- Metadata: `genres`, `categories`, `countries`, `languages`
- Relations: `movie_genre`, `tv_show_genre`
- Media: `movie_embeds`, `episode_embeds`, `movie_downloads`, `episode_downloads`
- Analytics: `views`, `content_requests`, `embed_reports`
- System (untouched by public APIs): `admins`, `api_keys`, `users`, `cache`, `sessions`, `jobs`

Behavioral invariants:
- Content Requests: deduplicate by `(LOWER(type), LOWER(title))`; increment `request_count` instead of inserting duplicates.
- Embed Reports: deduplicate by `(content_type, content_id, report_type, COALESCE(embed_id, 0))`; increment `report_count` instead of inserting duplicates. Validate target content exists.
- View Tracking: increment `view_count` on item and insert a record into `views` with polymorphic fields.

Column mapping (reference):
- `movies`: id, title, slug, overview, poster_path, backdrop_path, release_date, vote_average, vote_count, view_count, category_id, dubbing_language_id, created_at
- `tv_shows`: id, name, slug, overview, first_air_date, number_of_seasons, number_of_episodes, vote_average, view_count, category_id, created_at
- `episodes`: id, season_id, episode_number, name, overview, air_date, still_path, view_count, created_at
- `content_requests`: id, type, title, email, description, tmdb_id, year, status, request_count, requested_at, ip_address, user_agent, created_at
- `embed_reports`: id, content_type, content_id, embed_id (nullable), report_type, description, email, status, report_count, reported_at, ip_address, user_agent, created_at
- `views`: id, viewable_type, viewable_id, viewed_at

Important: Use the exact `viewable_type` strings Laravel writes (e.g., `App\\Models\\Movie`, `App\\Models\\TVShow`) to avoid analytics inconsistency.


### 7) Public Endpoints

Base URL examples:
- Production (Node public API): `https://api.nazaarabox.com/api/v1`
- Local: `http://localhost:8080/api/v1`

Important: Do NOT use the Laravel public base URLs. All public API traffic must go to the Node backend base URL above.


#### 7.1 Utilities

GET `/utils/all`
- Returns active metadata and generated years array.
- Response data:
```
{
  "genres": [...],
  "countries": [...],
  "categories": [...],
  "languages": [...],
  "years": [2025, 2024, ...]
}
```


#### 7.2 Global Search

GET `/search`
- Query: `q`, `type=all|movies|tvshows|episodes`, `page`, `limit`
- Behavior: partial match on titles/names; when `type=all` include buckets `{ movies, tvshows, episodes }`.
- Field mapping:
  - Movies: `WHERE title ILIKE '%q%' OR original_title ILIKE '%q%'` (use `LIKE` on MySQL)
  - TV shows: `WHERE name ILIKE '%q%' OR original_name ILIKE '%q%'`
  - Episodes: `WHERE name ILIKE '%q%'`

GET `/movies/search`
- Query: `q`, `genre`, `year`, `language`, `sort_by`, `order`, `page`, `limit`
- Sorting defaults to newest/created if unspecified.
- Filters to columns:
  - `q` → `m.title`
  - `genre` → exists in `movie_genre (movie_id=m.id AND genre_id=:genre)`
  - `year` → extract year from `m.release_date` (`YEAR()` MySQL; `EXTRACT(YEAR FROM)` Postgres; `strftime('%Y',...)` SQLite)
  - `language` → `m.dubbing_language_id`
  - `sort_by` whitelist: `created_at|release_date|vote_average|view_count`
  - `order` whitelist: `asc|desc`

GET `/tvshows/search`
- Same shape as movies; filters reflect TV shows.
- Mapping hints:
  - `genre` → `tv_show_genre`
  - `year` → `EXTRACT(YEAR FROM first_air_date)` (dialect-specific)
  - `sort_by` whitelist: `created_at|first_air_date|vote_average|view_count`

GET `/episodes/search`
- Query: `q`, `tvshow_id`, `season_id`, `genre`, `air_date_from`, `air_date_to`, `sort_by`, `order`, `page`, `limit`
- Mapping hints:
  - Join `seasons` and optionally `tv_shows` when filtering by `tvshow_id`
  - `genre` can be applied via the parent TV show’s genres
  - Date filters against `episodes.air_date`


#### 7.3 View Tracking (Leaderboard)

POST `/leaderboard/movies/{id}/view`
- Increments `movies.view_count` and inserts into `views`:
  - `viewable_type='movie'`, `viewable_id={id}`, `viewed_at=NOW()`
- 404 if movie not found.
- Note: Prefer using Laravel’s morph type string (e.g., `App\\Models\\Movie`) when inserting into `views` to stay consistent with existing analytics.

POST `/leaderboard/tvshows/{id}/view`
- Same for `tv_shows` with `viewable_type='tv_show'` (or whatever Laravel uses).


#### 7.4 Content Requests

POST `/requests`
- Body:
```
{
  "type": "movie",           // required: "movie" | "tvshow"
  "title": "My Title",       // required, max 255
  "email": "user@ex.com",    // optional
  "description": "...",      // optional, max 1000
  "tmdb_id": "12345",        // optional, max 50
  "year": "2024"             // optional, max 10
}
```
- If duplicate (by type + title, case-insensitive): return 200 with updated `request_count`.
- Else create with `status='pending'`, `requested_at=NOW()`, capture `ip_address` and `user_agent`, return 201.

GET `/requests`
- Query: `type`, `status=pending|approved|rejected|completed`, `search`, `sort_by=requested_at|request_count|title|status`, `sort_order=asc|desc`, `page`, `per_page<=100`
- Returns:
```
{
  "requests": [...],
  "pagination": {...}
}
```


#### 7.5 Embed Reports

POST `/reports/embed`
- Body:
```
{
  "content_type": "movie",        // required: "movie" | "episode"
  "content_id": 42,               // required, must exist in target table
  "embed_id": 10,                 // optional
  "report_type": "not_working",   // required: "not_working" | "wrong_content" | "poor_quality" | "broken_link" | "other"
  "description": "..." ,          // optional, max 1000
  "email": "user@ex.com"          // optional
}
```
- Validate referenced content exists; if duplicate key exists, increment `report_count` and return 200; else create with `status='pending'`, `reported_at=NOW()`, capture `ip_address` and `user_agent`, return 201.

GET `/reports/embed`
- Query: `content_type`, `content_id`, `status=pending|approved|rejected|resolved`, `report_type`, `sort_by=reported_at|report_count|report_type|status`, `sort_order`, `page`, `per_page<=100`
- Returns:
```
{
  "reports": [...],
  "pagination": {...}
}
```


#### 7.6 Comments (optional, if app uses)

GET `/comments?type=movie|tvshow|episode&id={id}`
- Returns approved comments and nested replies (if modeled that way).

POST `/comments`
- Body:
```
{
  "type": "episode",
  "id": 15,
  "name": "Viewer",
  "email": "viewer@example.com",
  "comment": "Loved this episode!",
  "parent_id": 3
}
```
- Create with `status='pending'` for moderation; return success message.


### 8) Validation Rules

Requests POST:
- `type`: required enum `movie|tvshow`
- `title`: required string ≤ 255
- `description`: optional string ≤ 1000
- `tmdb_id`: optional string ≤ 50
- `year`: optional string ≤ 10

Reports POST:
- `content_type`: required enum `movie|episode`
- `content_id`: required integer; must exist
- `report_type`: required enum as listed
- `description`: optional string ≤ 1000

Comments POST:
- `type`: required enum `movie|tvshow|episode`
- `id`: required integer; must exist in target type
- `name`: required string ≤ 255
- `email`: required email format
- `comment`: required string ≤ 1000
- `parent_id`: optional integer; if provided, must belong to same thread

Validation response (422):
```
{
  "success": false,
  "message": "Validation failed",
  "errors": { "field": ["reason"] }
}
```


### 9) Pagination and Sorting

- `per_page` default 20, max 100.
- Response includes `pagination` with `current_page`, `last_page`, `per_page`, `total`, `from`, `to`.
- Sorting parameters as defined per endpoint; sanitize to a whitelist to avoid SQL injection.


### 10) Security, CORS, and Rate Limiting

- Public endpoints are anonymous; enforce:
  - CORS: restrict to known origins (Flutter web/site domains).
  - Rate limiting (suggested defaults):
    - GET: 60 req/min/IP
    - POST: 20 req/min/IP
    - Bursts: 10 req/10s window
  - Helmet security headers.
  - Never return stack traces in production.
  - Sanitize inputs; always parameterize queries via Knex.
- Capture `ip_address` and `user_agent` on POST `/requests` and `/reports/embed` as analytics and abuse signals.

Suggested CORS:
```
Origin allowlist:
- https://nazaarabox.com
- https://harpaljob.com
- https://www.nazaarabox.com
- Android/iOS app origins (native apps are not subject to CORS)
```


### 11) Error Handling

- 404 when content not found (e.g., view tracking target, report target).
- 422 for validation failures.
- 500 for unexpected errors; log details server-side (pino), return generic message to clients.

Error codes summary:
```
200 OK           - Success (including dedupe updates)
201 Created      - Resource created (first-time request/report)
404 Not Found    - Target content not found
422 Unprocessable- Validation failed
429 Too Many     - Rate limit exceeded
500 Server Error - Unexpected failure
```


### 12) Example cURL Requests

Submit a content request:
```
curl -X POST https://api.example.com/api/v1/requests \
  -H "Content-Type: application/json" \
  -d '{
    "type":"movie",
    "title":"My Requested Title",
    "email":"user@example.com",
    "description":"Optional",
    "tmdb_id":"12345",
    "year":"2024"
  }'
```

Report an embed:
```
curl -X POST https://api.example.com/api/v1/reports/embed \
  -H "Content-Type: application/json" \
  -d '{
    "content_type":"movie",
    "content_id":42,
    "report_type":"not_working",
    "embed_id":10,
    "description":"Player stuck"
  }'
```

Track a movie view:
```
curl -X POST https://api.example.com/api/v1/leaderboard/movies/101/view
```

Search movies:
```
curl "https://api.example.com/api/v1/movies/search?q=avengers&page=1&limit=20"
```


### 13) Flutter Integration Notes (Parity)

- Keep the same request/response shapes as Laravel.
- For public endpoints the app already uses:
  - `GET /utils/all`
  - `GET /search`, `/movies/search`, `/tvshows/search`, `/episodes/search`
  - `POST /leaderboard/movies/{id}/view`, `POST /leaderboard/tvshows/{id}/view`
  - `POST /requests`, `GET /requests`
  - `POST /reports/embed`, `GET /reports/embed`
- Only the base URL needs to change if migrating traffic to Node.

Compatibility checklist (map to Flutter ApiService methods):
- Movies: getMovies/searchMovies → `/movies/search` (query params mapped)
- TVShows: getTVShows/searchTVShows → `/tvshows/search`
- Episodes: getLatestEpisodes/getEpisodesByDate/searchEpisodes → `/episodes/search`
- Utilities: getUtilityData → `/utils/all`
- Leaderboard: trackMovieView/trackTVShowView → `/leaderboard/.../view`
- Requests: getContentRequests/submitContentRequest → `/requests`
- Reports: submitEmbedReport/get reports → `/reports/embed`


### 14) Deployment and Coexistence

- Point Node to the same DB via env vars.
- Recommended ingress:
  - Keep Laravel for web pages and protected APIs.
  - Route `/api/v1/*` public endpoints to Node (or use a subdomain like `api.nazaarabox.com`).
  - Ensure HTTPS and HTTP/2 at the edge (Nginx/Cloudflare).
- Observability: structured logs (pino), request IDs, metrics endpoint (optional), uptime checks.


### 15) Versioning and Change Control

- Prefix routes with `/api/v1`.
- Non-breaking changes: add fields only.
- Breaking changes: bump to `/api/v2` and dual-run during migration.

OpenAPI (seed):
```
openapi: 3.0.3
info:
  title: Nazaara Box Public API
  version: 1.0.0
servers:
  - url: https://api.nazaarabox.com/api/v1
paths:
  /utils/all:
    get:
      summary: Get utility metadata
      responses:
        '200':
          description: OK
  /movies/search:
    get:
      summary: Search movies
      parameters:
        - in: query
          name: q
          schema: { type: string }
        - in: query
          name: genre
          schema: { type: integer }
        - in: query
          name: year
          schema: { type: integer }
        - in: query
          name: language
          schema: { type: integer }
        - in: query
          name: page
          schema: { type: integer, default: 1 }
        - in: query
          name: limit
          schema: { type: integer, default: 20, maximum: 100 }
      responses:
        '200': { description: OK }
  /leaderboard/movies/{id}/view:
    post:
      summary: Track movie view
      parameters:
        - in: path
          name: id
          required: true
          schema: { type: integer }
      responses:
        '200': { description: OK }
        '404': { description: Not found }
  /requests:
    get: { summary: List content requests, responses: { '200': { description: OK } } }
    post:
      summary: Submit content request
      requestBody:
        required: true
      responses:
        '201': { description: Created }
        '200': { description: Dedupe updated }
        '422': { description: Validation failed }
```


### 16) Implementation Hints (Selected Snippets)

Pagination helper:
```
export async function paginate<T>(query: any, page = 1, perPage = 20) {
  const [{ count }] = await query.clone().clearSelect().clearOrder().count({ count: '*' });
  const items = await query.offset((page - 1) * perPage).limit(perPage);
  return {
    items,
    pagination: {
      current_page: page,
      per_page: perPage,
      total: Number(count),
      last_page: Math.max(1, Math.ceil(Number(count) / perPage)),
      from: items.length ? (page - 1) * perPage + 1 : 0,
      to: (page - 1) * perPage + items.length
    }
  };
}
```

Standard responses:
```
export const ok = (res, data, message?) => res.json({ success: true, message, data });
export const created = (res, data, message?) => res.status(201).json({ success: true, message, data });
export const badRequest = (res, message, errors?) => res.status(422).json({ success: false, message, errors });
export const notFound = (res, message) => res.status(404).json({ success: false, message });
```

View tracking (transactional sketch):
```
router.post('/leaderboard/movies/:id/view', async (req, res) => {
  const id = Number(req.params.id);
  try {
    await db.transaction(async trx => {
      const updated = await trx('movies').where({ id }).increment('view_count', 1);
      if (!updated) throw new Error('not_found');
      await trx('views').insert({ viewable_type: 'movie', viewable_id: id, viewed_at: trx.fn.now() });
    });
    return ok(res, {});
  } catch (e) {
    return notFound(res, 'Movie not found');
  }
});
```

Content request (dedupe sketch):
```
const existing = await db('content_requests')
  .whereRaw('LOWER(type)=LOWER(?) AND LOWER(title)=LOWER(?)', [type, title])
  .first();
if (existing) {
  const [row] = await db('content_requests')
    .where({ id: existing.id })
    .increment('request_count', 1)
    .returning('*');
  return ok(res, { request: row, request_count: (existing.request_count || 0) + 1 },
    'Request already exists. We have updated the request count.');
}
const [createdRow] = await db('content_requests').insert({
  type, title, description, tmdb_id, year, status: 'pending', requested_at: db.fn.now(),
  ip_address: req.ip, user_agent: String(req.headers['user-agent'] || '').slice(0, 255)
}).returning('*');
return created(res, { request: createdRow }, 'Content request submitted successfully');
```

Embed report (existence + dedupe sketch):
```
const table = content_type === 'movie' ? 'movies' : 'episodes';
const target = await db(table).where({ id: content_id }).first();
if (!target) return notFound(res, content_type === 'movie' ? 'Movie not found' : 'Episode not found');

const key = { content_type, content_id, report_type, embed_id: embed_id || null };
const existing = await db('embed_reports').where(key).first();
if (existing) {
  const [row] = await db('embed_reports').where({ id: existing.id }).increment('report_count', 1).returning('*');
  return ok(res, { report: row, report_count: (existing.report_count || 0) + 1 },
    'Report already exists. We have updated the report count.');
}
const [createdRow] = await db('embed_reports').insert({
  ...key, description, status: 'pending', reported_at: db.fn.now(),
  ip_address: req.ip, user_agent: String(req.headers['user-agent'] || '').slice(0, 255)
}).returning('*');
return created(res, { report: createdRow }, 'Embed problem reported successfully');
```


### 17) Testing Checklist

- Unit tests for validation rules and services.
- Integration tests for each endpoint (happy path + edge cases).
- Load tests (optional) to size rate limits.
- Contract tests to ensure response wrappers/pagination keys remain consistent.


### 18) Rollout Plan

1) Stand up Node service pointed to staging DB (copy of prod).
2) Verify endpoints parity with current Laravel public APIs.
3) Smoke test Flutter app against Node base URL.
4) Deploy Node behind a new subdomain; warm up caches.
5) Gradually shift traffic (via DNS or gateway routing).
6) Monitor logs/metrics; finalize cutover.


— End of document —


