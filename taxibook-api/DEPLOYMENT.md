# Deployment Guide

**Last Updated**: 2026-02-19

## Production Environment

### Hosting Details
- **Provider**: Hostinger (Shared Hosting)
- **Backend URL**: https://admin.luxridesuv.com
- **Frontend URL**: https://book.luxridesuv.com
- **SSL**: Enabled (Let's Encrypt)
- **PHP Version**: 8.2
- **MySQL Version**: 8.0
- **Server**: Apache/LiteSpeed
- **Backend Root**: `/home/u388638774/domains/luxridesuv.com/public_html/admin`
- **Frontend Root**: `/home/u388638774/domains/luxridesuv.com/public_html/book`

## Environment Configuration

### Production Environment Variables (.env.production)
```env
# Application
APP_NAME=LuxRide
APP_ENV=production
APP_KEY=base64:mYmhrPskhLQ2zV79UjunNUBoZ5QR8jd+zaXm344/Qwk=
APP_DEBUG=false
APP_URL=https://book.luxridesuv.com

# Database (Hostinger MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u388638774_luxride
DB_USERNAME=u388638774_luxride
DB_PASSWORD=WilliamGarzonLuxRide2024!

# Mail (Gmail SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=contact@luxridesuv.com
MAIL_PASSWORD="kpai uuih tqrh qeck"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=contact@luxridesuv.com
MAIL_FROM_NAME="LuxRide"

# Session & Cache (Optimized for shared hosting)
SESSION_DRIVER=database
CACHE_STORE=file
QUEUE_CONNECTION=database

# Frontend
FRONTEND_URL=https://book.luxridesuv.com
SANCTUM_STATEFUL_DOMAINS=book.luxridesuv.com

# Admin
ADMIN_EMAIL=admin@luxridesuv.com
ADMIN_PASSWORD=LuxRide2024SecureAdmin!
```

## Deployment Process

### Initial Setup

#### 1. Prepare Local Build
```bash
# Backend preparation
cd taxibook-api
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Frontend build
cd taxibook-frontend
npm install
npm run build
```

#### 2. Upload Files
```bash
# Via SSH (if available)
scp -r taxibook-api/* user@server:/path/to/public_html/

# Via FTP (alternative)
# Use FileZilla or similar FTP client
# Upload all files except:
# - .env (create manually on server)
# - node_modules/
# - .git/
# - storage/logs/*
# - storage/framework/cache/*
```

#### 3. Server Configuration

##### Backend (.htaccess for Laravel)
Create/update `.htaccess` in backend public directory:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Force HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
    
    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    
    # Redirect Trailing Slashes If Not A Folder
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]
    
    # Send Requests To Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Cache Control
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

##### Frontend (.htaccess for React)
Create `.htaccess` in frontend root directory (`/public_html/book/`):
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # If requesting index.html, serve it as-is
    RewriteRule ^index\.html$ - [L]
    
    # If file doesn't exist
    RewriteCond %{REQUEST_FILENAME} !-f
    # If directory doesn't exist
    RewriteCond %{REQUEST_FILENAME} !-d
    # Serve index.html for React Router
    RewriteRule . /index.html [L]
</IfModule>
```
This enables React Router to handle client-side routing for URLs like `/tip/:token`.

#### 4. Database Setup
```bash
# SSH to server
ssh u388638774@server.hostinger.com

# Navigate to project
cd domains/luxridesuv.com/public_html/book

# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --force

# Create admin user
php artisan create:admin
```

#### 5. Set Permissions
```bash
# Storage directories
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Ensure web server can write
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

#### 6. Configure Cron Jobs

Add to crontab (Hostinger Control Panel):
```bash
# Laravel scheduler (every minute)
* * * * * cd /home/u388638774/domains/luxridesuv.com/public_html/book && php artisan schedule:run >> /dev/null 2>&1

# Queue worker (every 5 minutes - shared hosting limitation)
*/5 * * * * cd /home/u388638774/domains/luxridesuv.com/public_html/book && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

### Update Deployment

#### 1. Maintenance Mode
```bash
php artisan down --message="System update in progress" --retry=60
```

#### 2. Pull Updates
```bash
git pull origin main

# Or upload changed files via FTP
```

#### 3. Update Dependencies
```bash
composer install --optimize-autoloader --no-dev
npm install && npm run build
```

#### 4. Database Updates
```bash
php artisan migrate --force
```

#### 5. Clear Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Or clear all at once
php artisan optimize:clear

# Rebuild caches for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Important Notes**:
- Always run `php artisan config:clear` after changing `.env` values in production!
- Run `php artisan view:clear` after updating email or PDF templates
- Use `php artisan optimize:clear` to clear all caches when troubleshooting

#### 6. Exit Maintenance Mode
```bash
php artisan up
```

## Configuration Management

### Stripe Configuration
Configure in Filament Admin Panel (`/admin/settings`):
1. **Test Mode**:
   - Test Publishable Key: `pk_test_...`
   - Test Secret Key: `sk_test_...`
2. **Live Mode**:
   - Live Publishable Key: `pk_live_...`
   - Live Secret Key: `sk_live_...`
3. **Webhook Secret**: `whsec_...`

### Google Maps Configuration
Configure in Admin Panel:
- API Key: `AIza...`
- Traffic Model: best_guess (default)

### Email Settings
Configure in Admin Panel:
- SMTP Host: `smtp.gmail.com`
- SMTP Port: `587`
- SMTP Username: `contact@luxridesuv.com`
- SMTP Password: App-specific password
- Encryption: `TLS`

## Frontend Deployment

### Build Configuration
```javascript
// vite.config.js
export default {
  base: '/',
  build: {
    outDir: '../public/app',
    manifest: true,
    rollupOptions: {
      input: 'src/main.jsx'
    }
  }
}
```

### Environment Variables
```javascript
// .env.production
VITE_API_URL=https://book.luxridesuv.com/api
VITE_APP_URL=https://book.luxridesuv.com
VITE_STRIPE_PUBLIC_KEY=pk_live_...
VITE_GOOGLE_MAPS_API_KEY=AIza...
```

### Deploy Frontend
```bash
# Build for production
npm run build

# Upload dist folder contents to:
# /public_html/book/public/app/
```

## Monitoring & Logs

### Application Logs
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Apache error logs (Hostinger)
tail -f /home/u388638774/logs/error.log
```

### Performance Monitoring
- Use Laravel Telescope (development)
- Google Analytics (production)
- Stripe Dashboard for payments
- Hostinger metrics panel

## Backup Procedures

### Database Backup
```bash
# Manual backup
mysqldump -u u388638774_luxride -p u388638774_luxride > backup_$(date +%Y%m%d).sql

# Automated (add to cron)
0 2 * * * mysqldump -u u388638774_luxride -p'password' u388638774_luxride > /home/u388638774/backups/db_$(date +\%Y\%m\%d).sql
```

### File Backup
```bash
# Backup important directories
tar -czf backup_$(date +%Y%m%d).tar.gz \
  storage/app \
  .env \
  database/seeders \
  config
```

## SSL Certificate

### Auto-renewal (Let's Encrypt)
Hostinger handles SSL auto-renewal. Manual check:
```bash
# Check expiry
echo | openssl s_client -servername book.luxridesuv.com -connect book.luxridesuv.com:443 2>/dev/null | openssl x509 -noout -dates
```

## Security Checklist

### Pre-deployment
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Remove default Laravel routes
- [ ] Update admin credentials
- [ ] Configure CORS properly
- [ ] Set secure session cookies
- [ ] Enable HTTPS redirect

### Post-deployment
- [ ] Test payment processing
- [ ] Verify email sending
- [ ] Check error logging
- [ ] Test booking flow
- [ ] Verify admin panel access
- [ ] Check API endpoints
- [ ] Monitor performance

## Rollback Procedure

### Quick Rollback
```bash
# Restore previous version
git checkout previous-tag

# Restore database
mysql -u u388638774_luxride -p u388638774_luxride < backup.sql

# Clear caches
php artisan cache:clear
php artisan config:clear
```

## Template Updates

### After Updating Email or PDF Templates
```bash
# Clear view cache to see changes
php artisan view:clear

# Or clear everything
php artisan optimize:clear
```

### PDF Template Locations
- Receipt: `/resources/views/pdf/receipt.blade.php`
- Booking Details: `/resources/views/pdf/booking-details.blade.php`
- Email templates: `/resources/views/emails/`

## Common Issues

### 500 Errors
- Check `.env` file exists
- Verify permissions on storage/
- Check PHP version compatibility
- Review error logs

### Database Connection
- Verify credentials in `.env`
- Check MySQL service status
- Ensure database exists

### Email Not Sending
- Verify SMTP credentials
- Check Gmail app password
- Review email logs in database

### PDF Not Attaching to Emails
- Check email template has PDF toggles enabled in admin panel
- Verify PDF generation permissions in `storage/app/temp/`
- Clear view cache: `php artisan view:clear`
- Check logs for PDF generation errors

### Stripe Issues
- Verify webhook endpoint
- Check API keys in settings
- Ensure HTTPS is working

## Performance Optimization

### Shared Hosting Limitations
- Use file cache instead of Redis
- Implement queue with cron workaround
- Optimize database queries
- Enable gzip compression
- Use CDN for assets (future)

### Laravel Optimizations
```bash
# Production optimizations
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Testing Email with MailHog (Local)
```bash
# Start MailHog
mailhog

# Access web interface
# http://localhost:8025

# Configure .env
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

MailHog captures all emails including PDF attachments for testing.

## Contact Support

### Hostinger Support
- Control Panel: https://hpanel.hostinger.com
- Support Ticket: Via control panel
- Live Chat: 24/7 available

### Application Support
- Developer: admin@luxridesuv.com
- Documentation: This file
- Repository: [Private GitHub repo]