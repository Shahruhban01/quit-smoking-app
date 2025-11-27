# Detailed Setup Guide

## System Requirements

- **OS**: Linux (Ubuntu/Debian), macOS, or Windows
- **Web Server**: Apache 2.4+ with mod_rewrite enabled OR Nginx 1.18+
- **PHP**: 8.0 or higher with extensions:
  - pdo_mysql
  - mbstring
  - json
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Optional**: Composer for dependency management

## Step-by-Step Installation

### 1. Install Dependencies

**Ubuntu/Debian:**
```
sudo apt update
sudo apt install apache2 php8.1 php8.1-mysql php8.1-mbstring php8.1-json mariadb-server
```

**macOS (with Homebrew):**
```
brew install php@8.1 mysql
```

**Windows:**
- Install XAMPP or WAMP

### 2. Clone Repository
```
cd /var/www/html  # or /opt/lampp/htdocs for XAMPP
git clone https://github.com/yourusername/quit-smoking-app.git
cd quit-smoking-app
```

### 3. Database Setup
```
# Start MySQL
sudo systemctl start mysql

# Login
mysql -u root -p

# Create database
CREATE DATABASE quit_smoking_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user (optional, for production)
CREATE USER 'quit_app'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON quit_smoking_app.* TO 'quit_app'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import schema
mysql -u root -p quit_smoking_app < database/schema.sql
```

### 4. Configure Application
```
# Copy and edit config
cp backend/config/db.php.example backend/config/db.php
nano backend/config/db.php

# Update credentials:
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'quit_app');
define('DB_PASS', 'secure_password');
```

### 5. Set Permissions
```
chmod 755 backend/sessions
chmod 644 backend/config/db.php
chmod 644 backend/api/*.php
```

### 6. Install Composer Dependencies (Optional)
```
cd backend
composer install
```

### 7. Configure Web Server

**Apache (.htaccess):**
```
# backend/.htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ api/$1.php [L,QSA]
```

**Nginx:**
```
server {
    listen 80;
    server_name quit.local;
    root /var/www/quit-smoking-app/public;
    
    index index.html;
    
    location /backend/api/ {
        try_files $uri $uri.php $uri/ =404;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### 8. Test Installation
```
# Test database connection
php backend/test-connection.php

# Access in browser
http://localhost/quit-smoking-app/public/
```

## Production Deployment

### Security Checklist
- [ ] Change JWT_SECRET to random string
- [ ] Set `display_errors = 0` in php.ini
- [ ] Use HTTPS (SSL certificate)
- [ ] Restrict CORS to your domain
- [ ] Use environment variables for credentials
- [ ] Enable firewall
- [ ] Regular backups
- [ ] Update dependencies regularly

### Performance Optimization
- Enable PHP OPcache
- Use CDN for static assets
- Minify CSS/JS
- Enable gzip compression
- Add database indexes

## Troubleshooting

See [README.md#troubleshooting](../README.md#troubleshooting)