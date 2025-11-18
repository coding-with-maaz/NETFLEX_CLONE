# Content Requests and Embed Reports Features

## Overview

This document describes the new features added to the Nazaara Box platform:
1. **Content Requests System** - Allows users to request movies/series without authentication
2. **Embed Reports System** - Allows users to report problems with movie/episode embeds without authentication

## Features

### 1. Content Requests

Users can request movies or TV shows to be added to the platform. No authentication is required.

#### Public API Endpoints

**Create Request**
```
POST /api/v1/requests
Content-Type: application/json

{
    "type": "movie",  // or "tvshow"
    "title": "Movie Title",
    "description": "Optional description",
    "tmdb_id": "12345",  // Optional TMDB ID
    "year": "2024"  // Optional year
}
```

**Get Requests** (Public - for checking status)
```
GET /api/v1/requests?type=movie&status=pending&search=title
```

#### Request Statuses
- `pending` - New request, not yet reviewed
- `approved` - Request approved by admin
- `rejected` - Request rejected by admin
- `completed` - Request completed (content added)

#### Admin Management

**View All Requests**
```
GET /api/v1/admin/requests?type=movie&status=pending
```

**Update Request Status**
```
PATCH /api/v1/admin/requests/{id}
Content-Type: application/json
Authorization: Bearer {admin_token}

{
    "status": "approved",
    "admin_notes": "Optional admin notes"
}
```

**Bulk Update Requests**
```
POST /api/v1/admin/requests/bulk-update
Content-Type: application/json
Authorization: Bearer {admin_token}

{
    "ids": [1, 2, 3],
    "status": "approved",
    "admin_notes": "Optional notes"
}
```

**Admin Panel**: `/admin/requests`

### 2. Embed Reports

Users can report problems with movie or episode embeds. No authentication is required.

#### Public API Endpoints

**Report Embed Problem**
```
POST /api/v1/reports/embed
Content-Type: application/json

{
    "content_type": "movie",  // or "episode"
    "content_id": 123,
    "embed_id": 456,  // Optional - specific embed ID
    "report_type": "not_working",  // See report types below
    "description": "Optional description of the problem"
}
```

**Report Types**
- `not_working` - Embed is not working
- `wrong_content` - Wrong content is playing
- `poor_quality` - Poor video quality
- `broken_link` - Broken link
- `other` - Other issues

**Get Reports** (Public - for checking status)
```
GET /api/v1/reports/embed?content_type=movie&content_id=123&status=pending
```

#### Report Statuses
- `pending` - New report, not yet reviewed
- `reviewed` - Report reviewed by admin
- `fixed` - Problem has been fixed
- `dismissed` - Report dismissed by admin

#### Admin Management

**View All Reports**
```
GET /api/v1/admin/reports/embed?content_type=movie&status=pending&report_type=not_working
```

**Update Report Status**
```
PATCH /api/v1/admin/reports/embed/{id}
Content-Type: application/json
Authorization: Bearer {admin_token}

{
    "status": "fixed",
    "admin_notes": "Fixed the embed URL"
}
```

**Bulk Update Reports**
```
POST /api/v1/admin/reports/embed/bulk-update
Content-Type: application/json
Authorization: Bearer {admin_token}

{
    "ids": [1, 2, 3],
    "status": "fixed",
    "admin_notes": "All fixed"
}
```

**Admin Panel**: `/admin/reports`

## Database Schema

### content_requests Table
- `id` - Primary key
- `type` - 'movie' or 'tvshow'
- `title` - Request title
- `description` - Optional description
- `tmdb_id` - Optional TMDB ID
- `year` - Optional year
- `status` - Request status
- `admin_notes` - Admin notes
- `ip_address` - User IP address
- `user_agent` - User agent
- `request_count` - Number of times requested
- `requested_at` - Request timestamp
- `processed_at` - Processing timestamp
- `processed_by` - Admin ID who processed it
- `created_at`, `updated_at` - Timestamps

### embed_reports Table
- `id` - Primary key
- `content_type` - 'movie' or 'episode'
- `content_id` - Movie or Episode ID
- `embed_id` - Optional specific embed ID
- `report_type` - Type of report
- `description` - Problem description
- `status` - Report status
- `admin_notes` - Admin notes
- `ip_address` - User IP address
- `user_agent` - User agent
- `report_count` - Number of times reported
- `reported_at` - Report timestamp
- `processed_at` - Processing timestamp
- `processed_by` - Admin ID who processed it
- `created_at`, `updated_at` - Timestamps

## Installation

### 1. Run Migrations

```bash
php artisan migrate
```

This will create the `content_requests` and `embed_reports` tables.

### 2. Access Admin Panels

- Content Requests: `http://your-domain.com/admin/requests`
- Embed Reports: `http://your-domain.com/admin/reports`

## Usage Examples

### User Requesting a Movie

```javascript
// Frontend JavaScript example
async function requestMovie() {
    const response = await fetch('https://nazaarabox.com/api/v1/requests', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            type: 'movie',
            title: 'The Matrix',
            description: 'Please add this classic movie',
            year: '1999',
            tmdb_id: '603'
        })
    });
    
    const data = await response.json();
    if (data.success) {
        console.log('Request submitted successfully!');
    }
}
```

### User Reporting an Embed Problem

```javascript
// Frontend JavaScript example
async function reportEmbedProblem() {
    const response = await fetch('https://nazaarabox.com/api/v1/reports/embed', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            content_type: 'movie',
            content_id: 123,
            embed_id: 456,  // Optional
            report_type: 'not_working',
            description: 'The embed is not loading properly'
        })
    });
    
    const data = await response.json();
    if (data.success) {
        console.log('Report submitted successfully!');
    }
}
```

### Admin Managing Requests

```javascript
// Admin panel JavaScript example
async function approveRequest(requestId) {
    const token = localStorage.getItem('adminAccessToken');
    const response = await fetch(`https://nazaarabox.com/api/v1/admin/requests/${requestId}`, {
        method: 'PATCH',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            status: 'approved',
            admin_notes: 'Will be added soon'
        })
    });
    
    const data = await response.json();
    if (data.success) {
        console.log('Request approved!');
    }
}
```

## Features

### Content Requests
- ✅ Public submission (no authentication required)
- ✅ Duplicate request detection (increments request count)
- ✅ Admin dashboard with filtering and search
- ✅ Bulk status updates
- ✅ Admin notes
- ✅ Request statistics

### Embed Reports
- ✅ Public submission (no authentication required)
- ✅ Multiple report types
- ✅ Duplicate report detection (increments report count)
- ✅ Admin dashboard with filtering and search
- ✅ Bulk status updates
- ✅ Admin notes
- ✅ Report statistics
- ✅ Link to content from report

## Admin Panel Features

### Content Requests Page
- View all requests with filters
- Filter by type (movie/tvshow) and status
- Search by title
- Update request status
- Add admin notes
- Bulk update requests
- View request statistics

### Embed Reports Page
- View all reports with filters
- Filter by content type, status, and report type
- Search reports
- Update report status
- Add admin notes
- Bulk update reports
- View report statistics
- Link to content from report

## Security Features

- IP address tracking for requests and reports
- User agent tracking
- Duplicate detection to prevent spam
- Request/report count tracking
- Admin authentication required for management endpoints
- Public endpoints don't require authentication (as requested)

## Future Enhancements

- Email notifications for admins when new requests/reports are submitted
- Auto-approval for requests with high request counts
- Integration with TMDB API to automatically fetch movie details
- User notifications when their request is approved/completed
- Rate limiting to prevent abuse
- Captcha for public endpoints

## Notes

- The `processed_by` field is currently nullable and can be enhanced to properly track which admin processed the request/report
- Token-based authentication is used for admin endpoints
- Public endpoints are accessible without authentication as requested
- All timestamps are tracked for auditing purposes

