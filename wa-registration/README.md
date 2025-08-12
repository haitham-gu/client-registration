# 🏢 Client Registration System

A professional Arabic client registration system with admin dashboard, built with PHP, MySQL, and modern web technologies.

## ✨ Features

- **📝 Interactive Registration Form** - Arabic interface with dynamic city/wilaya selection
- **🛡️ Duplicate Detection** - Prevents duplicate registrations by phone number
- **👨‍💼 Admin Dashboard** - Secure login with session management
- **📊 Statistics & Analytics** - Real-time registration statistics
- **📤 Excel Export** - Export client data to CSV format
- **🎨 Modern UI** - Glassmorphism design with animations
- **📱 Responsive Design** - Works on desktop, tablet, and mobile
- **🔒 Security Features** - Rate limiting, password hashing, XSS protection

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/wa-registration.git
   cd wa-registration
   ```

2. **Set up the database**
   - Create a MySQL database
   - Import the database structure:
   ```bash
   mysql -u your_username -p your_database < database_structure.sql
   ```

3. **Configure database connection**
   - Copy `api/config.sample.php` to `api/config.php`
   - Update database credentials in `api/config.php`

4. **Set up admin credentials**
   - Edit `api/auth.php` and change the default password hash
   - Default login: `admin` / `password` (CHANGE THIS!)

5. **Configure web server**
   - Point your web server to the project root
   - Ensure `.htaccess` rules are enabled for Apache

## 📁 Project Structure

```
wa-registration/
├── public/                 # Frontend files
│   ├── index.html         # Landing page
│   ├── register.html      # Registration form
│   ├── admin.html         # Admin dashboard
│   ├── login.html         # Admin login
│   ├── style.css          # Main styles
│   ├── script.js          # Registration logic
│   └── admin.js           # Admin functionality
├── api/                   # Backend API
│   ├── config.sample.php  # Database config template
│   ├── auth.php          # Authentication system
│   ├── submit.php        # Registration handler
│   └── admin.php         # Admin API endpoints
├── data/                  # Static data files
│   ├── wilayas.json      # Algerian provinces
│   └── mapping.json      # City mappings
├── backup/               # Data backups (not in git)
└── .htaccess            # Security configurations
```

## 🔧 Configuration

### Database Setup
The system automatically creates the required table on first run. The `clients` table includes:
- Auto-incrementing ID
- Unique client codes (format: CL-YYMM-XXXXXX)
- Client information (name, phone, wilaya, city)
- Registration timestamps

### Security Features
- **Rate Limiting**: 5 login attempts per IP, 15-minute lockout
- **Session Management**: 1-hour timeout with secure cookies
- **Password Hashing**: bcrypt with cost factor 10
- **XSS Protection**: Input sanitization and CSP headers
- **SQL Injection Protection**: PDO prepared statements

## 🎯 Usage

### For Clients
1. Visit the registration page
2. Fill out the form with personal information
3. Select wilaya and city from searchable dropdowns
4. Submit to receive a unique client code

### For Administrators
1. Access the admin login page
2. Log in with admin credentials
3. View registration statistics and client list
4. Search and filter clients
5. Export data to CSV format

## 🔒 Security Considerations

Before deploying to production:

1. **Change default passwords** in `api/auth.php`
2. **Set up SSL certificate** for HTTPS
3. **Configure proper file permissions**
4. **Enable security headers** in `.htaccess`
5. **Set up regular database backups**
6. **Monitor access logs** for suspicious activity

## 🌐 Deployment

### Recommended Hosting Providers
- **SiteGround** - Excellent PHP/MySQL support
- **Bluehost** - Beginner-friendly with good performance
- **DigitalOcean** - VPS option for more control
- **Hostinger** - Budget-friendly option

### Deployment Checklist
- [ ] Change default admin password
- [ ] Update database configuration
- [ ] Enable SSL certificate
- [ ] Set up automated backups
- [ ] Configure security headers
- [ ] Test all functionality
- [ ] Monitor error logs

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

If you encounter any issues or have questions:

1. Check the [Issues](../../issues) page
2. Create a new issue with detailed information
3. Include error logs and steps to reproduce

## 🙏 Acknowledgments

- Built with modern web technologies
- Designed for Arabic-speaking users
- Focuses on security and user experience
- Optimized for performance and scalability

---

**⚠️ Important**: Always change default credentials before production deployment!
