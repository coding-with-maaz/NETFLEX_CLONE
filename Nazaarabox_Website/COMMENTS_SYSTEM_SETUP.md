# Comments System - Quick Setup Guide

## 🚀 Installation Steps

### 1. Run Database Migration
```bash
cd nazaarabox_production
php artisan migrate
```

This will create the `comments` table with all necessary fields for nested replies, status management, and email tracking.

### 2. Configure Mail Settings

Update your `.env` file with the following:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=jobc453@gmail.com
MAIL_PASSWORD=nojoowpiacaoxxet
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="jobc453@gmail.com"
MAIL_FROM_NAME="Nazaara Box"
```

**Important:** For Gmail, you may need to:
1. Enable "Less secure app access" OR
2. Use an App Password (recommended)

### 3. Test Email Configuration

```bash
php artisan tinker
```

Then run:
```php
Mail::raw('Test email from Nazaara Box', function($message) {
    $message->to('your-test-email@example.com')
            ->subject('Test Email');
});
```

### 4. Clear Cache (if needed)
```bash
php artisan config:clear
php artisan cache:clear
```

---

## 📝 API Usage

### Public Endpoints (No Authentication)

#### Get Comments
```bash
GET /api/v1/comments?type=movie&id=1
```

#### Submit Comment
```bash
POST /api/v1/comments
Content-Type: application/json

{
  "type": "movie",
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "comment": "Great movie!"
}
```

#### Reply to Comment
```bash
POST /api/v1/comments
Content-Type: application/json

{
  "type": "movie",
  "id": 1,
  "name": "Jane Smith",
  "email": "jane@example.com",
  "comment": "I agree!",
  "parent_id": 1
}
```

### Admin Endpoints (Token Required)

#### List Comments
```bash
GET /api/v1/admin/comments?status=pending&page=1&limit=20
Authorization: Bearer YOUR_ADMIN_TOKEN
```

#### Approve Comment
```bash
PATCH /api/v1/admin/comments/1
Authorization: Bearer YOUR_ADMIN_TOKEN
Content-Type: application/json

{
  "status": "approved"
}
```

#### Reply as Admin
```bash
PATCH /api/v1/admin/comments/1
Authorization: Bearer YOUR_ADMIN_TOKEN
Content-Type: application/json

{
  "comment": "Thank you for your feedback!"
}
```

#### Export Emails
```bash
GET /api/v1/admin/comments/export/emails
Authorization: Bearer YOUR_ADMIN_TOKEN
```

---

## 🎨 Admin Panel

Access the comments management page at:
- **URL:** `/admin/comments`
- **Features:**
  - View all comments with filters
  - Approve/reject/spam comments
  - Reply to comments as admin
  - Bulk actions
  - Export user emails as JSON

---

## ✅ Verification Checklist

- [ ] Migration run successfully
- [ ] Mail configuration updated in `.env`
- [ ] Email test successful
- [ ] Admin panel accessible at `/admin/comments`
- [ ] Public API endpoints working
- [ ] Admin API endpoints working
- [ ] Email notifications sending correctly

---

## 🔧 Troubleshooting

### Migration Fails
- Check database connection
- Ensure SQLite file exists or MySQL is running
- Check migration file syntax

### Emails Not Sending
1. Verify SMTP credentials in `.env`
2. Check Gmail app password settings
3. Review Laravel logs: `storage/logs/laravel.log`
4. Test with tinker command

### Comments Not Appearing
- Check comment status (must be `approved`)
- Verify content exists
- Check API response for errors

---

**Ready to use!** 🎉

