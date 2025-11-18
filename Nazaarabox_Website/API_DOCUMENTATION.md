# API Documentation - Content Requests & Embed Reports

## Base URL
```
https://nazaarabox.com/api/v1
```

## Content Requests API

### 1. Submit Content Request
**Endpoint:** `POST /api/v1/requests`

**Description:** Submit a new content request (movie or TV show)

**Authentication:** Not required (Public endpoint)

**Request Body:**
```json
{
    "type": "movie",           // Required: "movie" or "tvshow"
    "title": "Movie Title",    // Required: string, max 255 characters
    "description": "...",      // Optional: string, max 1000 characters
    "tmdb_id": "12345",        // Optional: string, max 50 characters
    "year": "2024"             // Optional: string, max 10 characters
}
```

**Success Response (201 Created):**
```json
{
    "success": true,
    "message": "Content request submitted successfully",
    "data": {
        "request": {
            "id": 1,
            "type": "movie",
            "title": "Movie Title",
            "description": "...",
            "tmdb_id": "12345",
            "year": "2024",
            "status": "pending",
            "request_count": 1,
            "requested_at": "2024-11-08T14:43:24.000000Z"
        }
    }
}
```

**Duplicate Request Response (200 OK):**
```json
{
    "success": true,
    "message": "Request already exists. We have updated the request count.",
    "data": {
        "request": {...},
        "request_count": 2
    }
}
```

**Validation Error Response (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "type": ["The type field is required."],
        "title": ["The title field is required."]
    }
}
```

**Error Response (500 Internal Server Error):**
```json
{
    "success": false,
    "message": "An error occurred while submitting your request. Please try again later.",
    "error": "Error details (only in debug mode)"
}
```

---

### 2. Get Content Requests
**Endpoint:** `GET /api/v1/requests`

**Description:** Retrieve content requests with filtering, sorting, and pagination

**Authentication:** Not required (Public endpoint)

**Query Parameters:**
- `type` (optional): Filter by type - "movie" or "tvshow"
- `status` (optional): Filter by status - "pending", "approved", "rejected", "completed"
- `search` (optional): Search by title (partial match)
- `sort_by` (optional): Sort field - "requested_at", "request_count", "title", "status" (default: "requested_at")
- `sort_order` (optional): Sort direction - "asc" or "desc" (default: "desc")
- `per_page` (optional): Items per page - 1-100 (default: 20)

**Example Request:**
```
GET /api/v1/requests?status=pending&sort_by=request_count&sort_order=desc&per_page=10
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "requests": [
            {
                "id": 1,
                "type": "movie",
                "title": "Movie Title",
                "description": "...",
                "tmdb_id": "12345",
                "year": "2024",
                "status": "pending",
                "request_count": 5,
                "requested_at": "2024-11-08T14:43:24.000000Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 5,
            "per_page": 10,
            "total": 50,
            "from": 1,
            "to": 10
        }
    }
}
```

---

## Embed Reports API

### 1. Submit Embed Report
**Endpoint:** `POST /api/v1/reports/embed`

**Description:** Report a problem with an embed (movie or episode)

**Authentication:** Not required (Public endpoint)

**Request Body:**
```json
{
    "content_type": "movie",              // Required: "movie" or "episode"
    "content_id": 123,                    // Required: integer (movie ID or episode ID)
    "embed_id": 456,                      // Optional: integer (specific embed ID)
    "report_type": "not_working",         // Required: "not_working", "wrong_content", "poor_quality", "broken_link", "other"
    "description": "The video won't play" // Optional: string, max 1000 characters
}
```

**Success Response (201 Created):**
```json
{
    "success": true,
    "message": "Embed problem reported successfully",
    "data": {
        "report": {
            "id": 1,
            "content_type": "movie",
            "content_id": 123,
            "embed_id": 456,
            "report_type": "not_working",
            "description": "The video won't play",
            "status": "pending",
            "report_count": 1,
            "reported_at": "2024-11-08T14:43:24.000000Z"
        }
    }
}
```

**Content Not Found Response (404 Not Found):**
```json
{
    "success": false,
    "message": "Movie not found"
}
```
or
```json
{
    "success": false,
    "message": "Episode not found"
}
```

**Validation Error Response (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "content_type": ["The content type field is required."],
        "content_id": ["The content id field is required."],
        "report_type": ["The report type field is required."]
    }
}
```

---

### 2. Get Embed Reports
**Endpoint:** `GET /api/v1/reports/embed`

**Description:** Retrieve embed reports with filtering, sorting, and pagination

**Authentication:** Not required (Public endpoint)

**Query Parameters:**
- `content_type` (optional): Filter by content type - "movie" or "episode"
- `content_id` (optional): Filter by content ID (integer)
- `status` (optional): Filter by status - "pending", "approved", "rejected", "resolved"
- `report_type` (optional): Filter by report type - "not_working", "wrong_content", "poor_quality", "broken_link", "other"
- `sort_by` (optional): Sort field - "reported_at", "report_count", "report_type", "status" (default: "reported_at")
- `sort_order` (optional): Sort direction - "asc" or "desc" (default: "desc")
- `per_page` (optional): Items per page - 1-100 (default: 20)

**Example Request:**
```
GET /api/v1/reports/embed?content_type=movie&status=pending&per_page=20
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "reports": [
            {
                "id": 1,
                "content_type": "movie",
                "content_id": 123,
                "embed_id": 456,
                "report_type": "not_working",
                "description": "The video won't play",
                "status": "pending",
                "report_count": 3,
                "reported_at": "2024-11-08T14:43:24.000000Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 3,
            "per_page": 20,
            "total": 45,
            "from": 1,
            "to": 20
        }
    }
}
```

---

## Response Format

All API responses follow this structure:

**Success Response:**
```json
{
    "success": true,
    "message": "Success message",
    "data": {
        // Response data
    }
}
```

**Error Response:**
```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        // Validation errors (if applicable)
    },
    "error": "Detailed error (only in debug mode)"
}
```

## HTTP Status Codes

- `200 OK` - Request successful
- `201 Created` - Resource created successfully
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation error
- `500 Internal Server Error` - Server error

## Content-Type Header

All responses include:
```
Content-Type: application/json
```

## Notes

1. Both APIs are **public endpoints** - no authentication required
2. All responses are in JSON format
3. Pagination is limited to a maximum of 100 items per page
4. Duplicate requests/reports increment the count instead of creating new records
5. Error messages in production mode don't include detailed stack traces (for security)
6. All timestamps are in ISO 8601 format (UTC)

