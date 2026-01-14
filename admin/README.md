# Sheet Homes Admin Panel

## Setup Instructions

### 1. Database Configuration

Edit `admin/config.php` and update the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sheet_homes');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```

### 2. Admin Credentials

**IMPORTANT:** Change the default admin credentials in `admin/config.php`:

```php
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123'); // CHANGE THIS IMMEDIATELY!
```

### 3. Database Setup

The database and tables will be created automatically on first access. Make sure:
- MySQL/MariaDB is running
- The database user has CREATE DATABASE privileges (for first run)
- PHP PDO MySQL extension is installed

### 4. File Permissions

Ensure the upload directory is writable:

```bash
chmod 755 assets/img/properties/
chown www-data:www-data assets/img/properties/
```

### 5. Access the Admin Panel

Navigate to: `https://sheet.homes/admin/login.php`

Default credentials (CHANGE IMMEDIATELY):
- Username: `admin`
- Password: `admin123`

## Features

- ✅ Secure login system
- ✅ Add new properties with images
- ✅ Edit existing properties
- ✅ Delete properties
- ✅ Upload multiple slide images
- ✅ Mark properties as featured (for homepage)
- ✅ All properties automatically appear on the website

## File Structure

```
admin/
├── config.php          # Database and admin configuration
├── login.php            # Admin login page
├── index.php            # Properties dashboard
├── property-form.php    # Add/Edit property form
├── logout.php           # Logout handler
└── admin.css            # Admin panel styles

api/
└── properties.php       # API endpoint for frontend

assets/img/properties/   # Property images storage
```

## Security Notes

1. **Change default password immediately** after first login
2. Consider using environment variables for sensitive data
3. Ensure `admin/` directory is not publicly accessible (use .htaccess if needed)
4. Regularly backup the database
5. Keep PHP and MySQL updated

## Troubleshooting

### Database connection error
- Check database credentials in `config.php`
- Ensure MySQL service is running
- Verify database user has proper permissions

### Images not uploading
- Check file permissions on `assets/img/properties/`
- Verify upload_max_filesize in php.ini
- Check PHP error logs

### Properties not showing on website
- Check API endpoint: `/api/properties.php`
- Verify database has properties
- Check browser console for JavaScript errors

