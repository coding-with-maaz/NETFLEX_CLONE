# Comments System Documentation

**Created:** January 2025  
**Feature:** Comments System with Nested Replies and Email Notifications

---

## 📋 Overview

The Comments System allows users to comment on movies, TV shows, and episodes without authentication. It includes nested replies, email notifications, admin management, and email export functionality.

---

## 🎯 Features

### ✅ Core Features
- **No Authentication Required** - Users can comment by entering name, email, and comment
- **Nested Replies** - Support for threaded/reply comments (unlimited nesting)
- **Email Notifications** - Automatic email notifications when:
  - Someone replies to a user's comment
  - Admin replies to a user's comment
- **Comment Status Management** - Statuses: `pending`, `approved`, `rejected`, `spam`
- **Admin Management** - Full CRUD operations from admin panel
- **Email Collection** - Export all user emails as JSON

---

## 🗄️ Database Schema

### Comments Table

```sql
CREATE TABLE comments (
    id BIGINT PRIMARY KEY,
    commentable_type VARCHAR(255),  -- 'App\Models\Movie', 'App\Models\TVShow', 'App\Models\Episode'
    commentable_id BIGINT,          -- ID of the content
    parent_id BIGINT NULL,          -- For nested replies
    name VARCHAR(255),              -- Commenter name
    email VARCHAR(255),              -- Commenter email
    comment TEXT,                   -- Comment content
    status ENUM('pending', 'approved', 'rejected', 'spam') DEFAULT 'pending',
    admin_id BIGINT NULL,           -- Admin who replied/approved
    is_admin_reply BOOLEAN DEFAULT false,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    like_count INT DEFAULT 0,
    dislike_count INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_commentable (commentable_type, commentable_id),
    INDEX idx_parent (parent_id),
    INDEX idx_status (status),
    INDEX idx_email (email),
    INDEX idx_created (created_at),
    
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
);
```

---

## 🔌 API Endpoints

### Public API (No Authentication)

#### Get Comments
- **Endpoint:** `GET /api/v1/comments`
- **Parameters:**
  - `type` (required): `movie`, `tvshow`, or `episode`
  - `id` (required): Content ID
- **Response:**
```json
{
  "success": true,
  "data": {
    "comments": [
      {
        "id": 1,
        "name": "John Doe",
        "comment": "Great movie!",
        "status": "approved",
        "is_admin_reply": false,
        "like_count": 0,
        "dislike_count": 0,
        "created_at": "2025-01-11T10:00:00.000000Z",
        "replies": [
          {
            "id": 2,
            "name": "Jane Smith",
            "comment": "I agree!",
            "replies": []
          }
        ]
      }
    ],
    "total": 1
  }
}
```

#### Submit Comment
- **Endpoint:** `POST /api/v1/comments`
- **Parameters:**
  - `type` (required): `movie`, `tvshow`, or `episode`
  - `id` (required): Content ID
  - `name` (required): Commenter name (max 255 chars)
  - `email` (required): Valid email address
  - `comment` (required): Comment text (min 3, max 5000 chars)
  - `parent_id` (optional): Parent comment ID for replies
- **Response:**
```json
{
  "success": true,
  "message": "Comment submitted successfully. It will be visible after approval.",
  "data": {
    "comment": {
      "id": 1,
      "name": "John Doe",
      "comment": "Great movie!",
      "status": "pending",
      "created_at": "2025-01-11T10:00:00.000000Z"
    }
  }
}
```

### Admin API (Token Authentication Required)

#### List Comments
- **Endpoint:** `GET /api/v1/admin/comments`
- **Parameters:**
  - `status` (optional): Filter by status
  - `type` (optional): Filter by content type
  - `content_id` (optional): Filter by content ID
  - `email` (optional): Search by email
  - `admin_replies` (optional): Filter admin replies only
  - `search` (optional): Search in name, email, or comment
  - `sort_by` (optional): Sort field
  - `order` (optional): `asc` or `desc`
  - `page` (optional): Page number
  - `limit` (optional): Results per page
- **Response:**
```json
{
  "success": true,
  "data": {
    "comments": [...],
    "pagination": {...},
    "stats": {
      "total": 100,
      "pending": 10,
      "approved": 80,
      "rejected": 5,
      "spam": 5
    }
  }
}
```

#### Get Comment Details
- **Endpoint:** `GET /api/v1/admin/comments/{id}`
- **Response:** Detailed comment with all relationships

#### Update Comment Status
- **Endpoint:** `PATCH /api/v1/admin/comments/{id}`
- **Parameters:**
  - `status` (optional): New status
- **Response:**
```json
{
  "success": true,
  "message": "Comment updated successfully",
  "data": {
    "comment": {...}
  }
}
```

#### Reply as Admin
- **Endpoint:** `PATCH /api/v1/admin/comments/{id}`
- **Parameters:**
  - `comment` (required): Admin reply text
  - `admin_id` (optional): Admin ID (defaults to authenticated admin)
- **Response:**
```json
{
  "success": true,
  "message": "Admin reply added and notification sent",
  "data": {
    "comment": {...},
    "admin_reply": {...}
  }
}
```

#### Delete Comment
- **Endpoint:** `DELETE /api/v1/admin/comments/{id}`
- **Response:**
```json
{
  "success": true,
  "message": "Comment deleted successfully"
}
```

#### Bulk Update Comments
- **Endpoint:** `POST /api/v1/admin/comments/bulk-update`
- **Parameters:**
  - `comment_ids` (required): Array of comment IDs
  - `status` (required): New status
- **Response:**
```json
{
  "success": true,
  "message": "10 comment(s) updated successfully",
  "data": {
    "updated_count": 10
  }
}
```

#### Export Emails
- **Endpoint:** `GET /api/v1/admin/comments/export/emails`
- **Parameters:**
  - `status` (optional): Filter by status
- **Response:** JSON file download with array of email objects
```json
[
  {
    "email": "user@example.com",
    "name": "User Name"
  },
  ...
]
```

---

## 📧 Email Notifications

### Configuration

Update your `.env` file with the following mail settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=jobc453@gmail.com
MAIL_PASSWORD=nojoowpiacaoxxet
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="jobc453@gmail.com"
MAIL_FROM_NAME="Nazaara Box"
```

### Email Templates

#### Comment Reply Notification
- **Trigger:** When someone replies to a user's comment
- **Recipient:** Original commenter
- **Template:** `resources/views/emails/comment-reply.blade.php`
- **Subject:** "New Reply to Your Comment on {Content Title}"

#### Admin Reply Notification
- **Trigger:** When admin replies to a user's comment
- **Recipient:** Original commenter
- **Template:** `resources/views/emails/admin-reply.blade.php`
- **Subject:** "Admin Reply to Your Comment on {Content Title}"

---

## 🎨 Admin Panel

### Access
- **URL:** `/admin/comments`
- **Route:** `admin.comments`
- **View:** `admin/comments.blade.php`

### Features
- **Statistics Dashboard** - Total, pending, approved, rejected, spam counts
- **Filtering** - By status, content type, search
- **Bulk Actions** - Bulk approve, reject, mark as spam
- **Individual Actions** - Approve, reject, mark spam, reply, delete
- **Email Export** - Export all user emails as JSON
- **Nested View** - Visual indication of reply threads

---

## 🔗 Model Relationships

### Comment Model
```php
// Polymorphic relationship to content
$comment->commentable; // Movie, TVShow, or Episode

// Parent comment (for replies)
$comment->parent;

// Child replies
$comment->replies; // Approved replies only
$comment->allReplies; // All replies (for admin)

// Admin who replied
$comment->admin;
```

### Content Models (Movie, TVShow, Episode)
```php
// Get all comments
$movie->comments;

// Get approved comments only
$movie->comments()->where('status', 'approved')->get();
```

---

## 🚀 Setup Instructions

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Configure Mail Settings
Update `.env` file with your SMTP credentials (see Email Notifications section above).

### 3. Test Email Configuration
```bash
php artisan tinker
Mail::raw('Test email', function($message) {
    $message->to('your-email@example.com')
            ->subject('Test Email');
});
```

### 4. Access Admin Panel
Navigate to `/admin/comments` to manage comments.

---

## 📝 Usage Examples

### Submit a Comment (Public API)
```javascript
fetch('/api/v1/comments', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        type: 'movie',
        id: 1,
        name: 'John Doe',
        email: 'john@example.com',
        comment: 'This is a great movie!'
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

### Reply to a Comment
```javascript
fetch('/api/v1/comments', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        type: 'movie',
        id: 1,
        name: 'Jane Smith',
        email: 'jane@example.com',
        comment: 'I agree!',
        parent_id: 1  // Reply to comment ID 1
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

### Get Comments for Content
```javascript
fetch('/api/v1/comments?type=movie&id=1')
.then(response => response.json())
.then(data => {
    console.log('Comments:', data.data.comments);
    // Comments include nested replies
});
```

### Admin: Approve Comment
```javascript
fetch('/api/v1/admin/comments/1', {
    method: 'PATCH',
    headers: {
        'Authorization': 'Bearer YOUR_ADMIN_TOKEN',
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        status: 'approved'
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

### Admin: Reply to Comment
```javascript
fetch('/api/v1/admin/comments/1', {
    method: 'PATCH',
    headers: {
        'Authorization': 'Bearer YOUR_ADMIN_TOKEN',
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        comment: 'Thank you for your feedback!'
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

### Admin: Export Emails
```javascript
fetch('/api/v1/admin/comments/export/emails', {
    headers: {
        'Authorization': 'Bearer YOUR_ADMIN_TOKEN',
    }
})
.then(response => response.blob())
.then(blob => {
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'user_emails.json';
    a.click();
});
```

---

## 🔒 Security Features

1. **Input Validation** - All inputs are validated
2. **Email Validation** - Email format validation
3. **Content Length Limits** - Comment min 3, max 5000 characters
4. **IP Tracking** - IP addresses stored for moderation
5. **Status Management** - All comments start as pending
6. **Admin Authentication** - Admin endpoints require token
7. **Email Privacy** - Emails not exposed in public API

---

## 📊 Comment Status Flow

```
User Submits Comment
        ↓
    [pending]
        ↓
Admin Reviews
        ↓
    ┌───┴───┐
    ↓       ↓
[approved] [rejected]
    ↓       ↓
Visible   Hidden
```

**Additional Status:**
- `spam` - Marked as spam by admin

---

## 🎯 Best Practices

1. **Moderation** - Review all pending comments before approval
2. **Email Notifications** - Monitor email delivery for failed sends
3. **Spam Prevention** - Use spam status for unwanted comments
4. **Reply Management** - Respond to user comments promptly
5. **Email Export** - Export emails regularly for backup
6. **Content Moderation** - Monitor comment content for inappropriate material

---

## 🐛 Troubleshooting

### Email Not Sending
1. Check `.env` mail configuration
2. Verify SMTP credentials
3. Check Laravel logs: `storage/logs/laravel.log`
4. Test email configuration using tinker

### Comments Not Appearing
1. Check comment status (must be `approved`)
2. Verify content exists and is active
3. Check API response for errors

### Nested Replies Not Loading
1. Verify parent comment exists
2. Check parent comment status
3. Ensure replies are approved

---

## 📈 Future Enhancements

- [ ] Comment likes/dislikes functionality
- [ ] Comment editing by users
- [ ] Comment reporting system
- [ ] Spam detection automation
- [ ] Comment moderation queue
- [ ] User comment history
- [ ] Comment notifications preferences

---

**Last Updated:** January 2025  
**Version:** 1.0.0

