# SRP Production Deployment Checklist

## Pre-Deployment Security Fixes Applied

### 1. Critical Security Issues Fixed ✅
- [x] Removed IP spoofing vulnerability in `SrpClient.php`
- [x] Changed VPN check from fail-open to fail-close for better security
- [x] Disabled debug mode in production (`LandingController.php`)
- [x] Optimized database connection to avoid unnecessary ping queries
- [x] Enhanced session directory security with permission checks

### 2. Code Quality Improvements ✅
- [x] Added proper type declarations
- [x] Improved error handling
- [x] Enhanced input validation
- [x] Consistent code style across all files

## Production Deployment Steps

### 1. Environment Configuration
```bash
# Generate new API keys (32 characters)
openssl rand -hex 32

# Update .env file with production values
cp srp/.env.example srp/.env
```

Required .env changes:
- [ ] `SRP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_NAME="Smart Redirect Platform"`
- [ ] `SRP_API_KEY=<new_32_char_key>`
- [ ] `API_KEY_INTERNAL=<new_32_char_key>`
- [ ] `API_KEY_EXTERNAL=<new_32_char_key>`
- [ ] `DECISION_API_KEY=<new_32_char_key>`
- [ ] `POSTBACK_SECRET=<new_secret_string>`
- [ ] `SRP_ADMIN_USER=<new_admin_username>`
- [ ] `SRP_ADMIN_PASSWORD_HASH=<bcrypt_hash>`
- [ ] Database credentials
- [ ] Domain URLs (brand and tracking)
- [ ] `HEALTH_CHECK_TOKEN=<new_token>`

### 2. Generate Admin Password Hash
```php
<?php
// Generate bcrypt hash for admin password
$password = 'your_secure_password_here';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
echo "Password Hash: " . $hash . "\n";
```

### 3. Database Setup
```bash
# Import schema
mysql -u username -p database_name < database/schema.sql

# Verify tables created
mysql -u username -p database_name -e "SHOW TABLES;"
```

### 4. File Upload Structure
```
/home/username/
├── public_html/              # Brand domain files
├── public_html_tracking/     # Tracking domain files
├── srp/                      # Application (outside webroot)
└── storage/                  # Logs and cache
```

### 5. Update Bootstrap Paths
Replace relative paths with absolute paths in all PHP entry files:
```php
// From:
require_once __DIR__ . '/../srp/src/bootstrap.php';

// To:
require_once '/home/username/srp/src/bootstrap.php';
```

Files to update:
- [ ] All files in `public_html/*.php`
- [ ] All files in `public_html_tracking/*.php`

### 6. Set Proper Permissions
```bash
# Secure .env file
chmod 600 /home/username/srp/.env

# Set directory permissions
chmod 755 /home/username/public_html
chmod 755 /home/username/public_html_tracking
chmod 700 /home/username/srp
chmod 700 /home/username/storage/logs
```

### 7. Configure Web Server

#### Apache .htaccess (already included)
- Security headers configured
- Cross-domain blocking implemented
- CORS rules for API endpoints

#### SSL/HTTPS
- [ ] Enable AutoSSL via cPanel
- [ ] Verify all domains have SSL certificates
- [ ] Test HTTPS redirect

### 8. Test All Endpoints
```bash
# Run endpoint tests
php test-all-endpoints.php production

# Test Decision API with new client
php srp-decision-simple.php

# Test health check endpoint
curl -H "X-Health-Token: your_health_token" https://api.qvtrk.com/health

# Test all domains
curl -I https://trackng.app
curl -I https://panel.trackng.app
curl -I https://qvtrk.com
curl -I https://api.qvtrk.com
```

### 9. Security Verification
- [ ] Verify CSRF protection on all forms
- [ ] Check CSP headers are working
- [ ] Confirm session security settings
- [ ] Test rate limiting on API endpoints
- [ ] Verify cross-domain blocking

### 10. Monitoring Setup
- [ ] Set up log rotation for `storage/logs/`
- [ ] Configure error monitoring
- [ ] Set up uptime monitoring for both domains
- [ ] Enable MySQL event scheduler for auto-cleanup

### 11. Backup Strategy
```bash
# Database backup script
#!/bin/bash
BACKUP_DIR="/home/username/backups"
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u user -p'password' database > "$BACKUP_DIR/srp_$DATE.sql"
gzip "$BACKUP_DIR/srp_$DATE.sql"
find "$BACKUP_DIR" -name "srp_*.sql.gz" -mtime +30 -delete
```

### 12. API Client Deployment
For external hosts using the Decision API:
- [ ] Distribute `srp-decision-client.php` or `srp-decision-simple.php`
- [ ] Provide API key securely (not via email)
- [ ] Test integration with sample request
- [ ] Monitor initial traffic for issues
- [ ] Check rate limiting is working

### 13. Post-Deployment Verification
- [ ] All endpoints responding correctly
- [ ] Admin login working
- [ ] Decision API returning correct responses
- [ ] API clients connecting successfully
- [ ] Postback system functioning
- [ ] No error messages exposed to users
- [ ] Performance acceptable (< 200ms response times)
- [ ] Bot blocking working (check logs)
- [ ] Health check endpoint accessible

## Important Security Notes

1. **Never commit .env file to version control**
2. **Change default admin credentials immediately**
3. **Monitor logs regularly for suspicious activity**
4. **Keep PHP and MySQL versions updated**
5. **Regular security audits recommended**

## Rollback Plan

If issues occur:
1. Restore previous code version
2. Restore database from backup
3. Clear all caches
4. Check error logs for issues
5. Test basic functionality before going live

## Support Contacts

- Hosting Support: [Your hosting provider]
- System Admin: [Contact info]
- Developer: [Contact info]

---

Last Updated: November 2025
Version: 2.2.0

## What's New in This Version

- Enhanced API security with multiple keys
- PHP client libraries for easy integration
- Improved .htaccess security rules
- Bot blocking and rate limiting
- Health check monitoring endpoint
- Extended environment configuration
- Better PHP.ini production settings