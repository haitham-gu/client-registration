# Production Deployment Package

## What to Upload to Your Hosting Provider

### Core Files (Upload to public_html or www folder):
```
public/
├── index.html          # Landing page
├── register.html       # Registration form
├── login.html         # Admin login
├── admin.html         # Admin dashboard
├── script.js          # Registration logic
├── admin.js           # Dashboard logic
├── style.css          # Main styles
├── app.js             # Additional scripts
└── assets/
    ├── css/
    │   └── styles.css
    ├── js/
    │   └── app.js
    └── data/
        ├── wilayas.json
        └── mapping.json

api/
├── config.php         # Database configuration (UPDATE CREDENTIALS!)
├── submit.php         # Registration endpoint
├── admin.php          # Admin API
└── auth.php           # Authentication (UPDATED WITH STRONG PASSWORDS ✓)

.htaccess              # Security rules (CREATED ✓)
```

### Files to Keep Local (DO NOT UPLOAD):
```
deploy.bat             # This deployment script
backup/               # Local backups
config.production.php # Template only
DEPLOYMENT_GUIDE.md   # Documentation
```

## Hosting Provider Setup Steps

### Step 1: Choose Your Hosting Provider
Recommended options:
- **SiteGround** (Premium, excellent support)
- **Bluehost** (Beginner-friendly)
- **Hostinger** (Budget-friendly)
- **DigitalOcean** (Advanced users)

### Step 2: Database Setup
1. Log into your hosting control panel (cPanel/Plesk)
2. Create a new MySQL database
3. Create a database user with full permissions
4. Note down: database name, username, password, host

### Step 3: Update Configuration
Edit `api/config.php` with your hosting details:
```php
define('DB_HOST', 'localhost');        // Usually localhost
define('DB_USER', 'your_db_username'); // From hosting panel
define('DB_PASS', 'your_db_password'); // From hosting panel  
define('DB_NAME', 'your_db_name');     // From hosting panel
```

### Step 4: File Upload
1. Connect via FTP/SFTP or use hosting file manager
2. Upload all files from the "Core Files" list above
3. Set folder permissions:
   - Folders: 755
   - Files: 644
   - api/ folder: 755

### Step 5: Database Import
1. Open phpMyAdmin in your hosting panel
2. Select your database
3. Import the `backup/wa_registration_export.sql` file
4. Verify tables are created successfully

### Step 6: SSL Certificate
1. Enable SSL in your hosting control panel (usually free)
2. Update `.htaccess` to force HTTPS:
   - Uncomment the HTTPS redirect lines
3. Test with https://yourdomain.com

### Step 7: Final Testing
1. Visit your registration form
2. Submit a test registration
3. Log into admin panel with:
   - Username: `admin` Password: `Admin@2025!`
   - Username: `manager` Password: `Manager@2025!`
4. Verify export functionality works

## Security Checklist ✓
- [x] Strong admin passwords implemented
- [x] .htaccess security rules in place
- [x] Sensitive files protected
- [x] Input validation and SQL injection protection
- [x] XSS protection headers
- [ ] SSL certificate (enable after upload)
- [ ] Regular backups scheduled

## Your New Admin Credentials:
- **Username:** admin | **Password:** Admin@2025!
- **Username:** manager | **Password:** Manager@2025!

**🚀 You're ready for production deployment!**

Run the `deploy.bat` script to export your current database, then follow the hosting setup steps above.
