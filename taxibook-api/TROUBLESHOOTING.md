# Troubleshooting Guide

## Common Issues & Solutions

### Email Issues

#### Confusing Email Template Form (Fixed v1.7.2)
**Problem**: Form required selecting trigger events for time-based emails

**Cause**: Form validation didn't match the simplified email system logic

**Solution**: 
- Redesigned form with conditional validation
- Triggers only required for immediate emails
- Trigger field hidden for scheduled emails
- Clear visual feedback with color-coded summary boxes

**How it works now**:
- Select email type first (Event-triggered vs Time-based)
- Form dynamically adjusts based on selection
- No more selecting irrelevant triggers for scheduled emails

#### Duplicate Cancellation Emails
**Problem**: Receiving two cancellation emails when cancelling a booking from admin panel

**Cause**: Both BookingObserver and EditBooking.php were firing BookingCancelled events

**Solution**: Fixed in v1.7.1
- The system now uses only the BookingObserver to fire events
- Manual event triggers have been removed from admin actions
- Ensures only one email per status change

**Prevention**:
- Always let observers handle event firing for status changes
- Don't manually fire events when updating model attributes that trigger observers

#### Emails Not Sending
**Problem**: Emails are not being sent despite being triggered

**Solutions**:
1. Check email settings in admin panel (Settings → Email Settings)
2. Verify SMTP credentials in `.env` file
3. Check email logs in admin panel (Email Queue)
4. Clear cache: `php artisan optimize:clear`
5. Check Laravel logs: `tail -f storage/logs/laravel.log`

### Installation Issues

#### Composer Dependencies Failed
**Error**: `Your requirements could not be resolved to an installable set of packages`

**Solution**:
```bash
# Clear composer cache
composer clear-cache

# Update composer
composer self-update

# Try installing with --ignore-platform-reqs
composer install --ignore-platform-reqs

# If specific package fails
composer require package/name --with-all-dependencies
```

#### NPM Installation Errors
**Error**: `npm ERR! code ERESOLVE`

**Solution**:
```bash
# Clear npm cache
npm cache clean --force

# Try with legacy peer deps
npm install --legacy-peer-deps

# Or use force
npm install --force
```

### Database Issues

#### Migration Failed
**Error**: `SQLSTATE[42S01]: Base table or view already exists`

**Solution**:
```bash
# Rollback migrations
php artisan migrate:rollback

# Fresh migration (CAUTION: deletes all data)
php artisan migrate:fresh

# Check migration status
php artisan migrate:status
```

#### Database Connection Refused
**Error**: `SQLSTATE[HY000] [2002] Connection refused`

**Solution**:
1. Check `.env` database credentials
2. Verify MySQL is running: `systemctl status mysql`
3. Check host (use `127.0.0.1` instead of `localhost` on some systems)
4. Verify port (default 3306)

### Stripe Issues

#### Keys Not Working
**Problem**: Payment fails with invalid key error

**Solution**:
1. Check stripe_mode in admin settings
2. Verify correct keys for mode (test vs live)
3. Clear config cache: `php artisan config:clear`
4. Check key format:
   - Test keys: `pk_test_...` and `sk_test_...`
   - Live keys: `pk_live_...` and `sk_live_...`

#### Webhook Not Receiving Events
**Problem**: Stripe webhooks not triggering

**Solution**:
1. Verify webhook endpoint: `https://yourdomain.com/api/stripe/webhook`
2. Check webhook secret in settings
3. Ensure CSRF is disabled for webhook route
4. Test with Stripe CLI:
```bash
stripe listen --forward-to localhost:8000/api/stripe/webhook
```

#### Payment Intent Failed
**Error**: `Payment intent confirmation failed`

**Solution**:
1. Check if amount is valid (minimum $0.50 USD)
2. Verify card test numbers in test mode
3. Check for 3D Secure requirements
4. Review Stripe dashboard for detailed errors

### Email Issues

#### Emails Not Sending
**Problem**: Emails queued but not sent

**Solution**:
1. Check SMTP settings in admin panel
2. Verify Gmail app password (not regular password)
3. Check queue worker is running:
```bash
php artisan queue:work
```
4. Review email logs in database
5. Test SMTP connection:
```php
php artisan tinker
Mail::raw('Test email', function($message) {
    $message->to('test@example.com')->subject('Test');
});
```

#### Gmail Authentication Failed
**Error**: `Failed to authenticate on SMTP server`

**Solution**:
1. Enable 2-factor authentication on Gmail
2. Generate app-specific password
3. Use app password in settings, not regular password
4. Enable "Less secure app access" (if not using app password)

#### Email Templates Not Rendering
**Problem**: Variables not being replaced

**Solution**:
1. Check variable syntax: `{{variable_name}}`
2. Verify variable exists in NotificationService
3. Clear view cache: `php artisan view:clear`
4. Check template type (HTML vs plain text)

### Frontend Issues

#### API CORS Errors
**Error**: `Access to XMLHttpRequest blocked by CORS policy`

**Solution**:
1. Check `FRONTEND_URL` in `.env`
2. Update `config/cors.php`:
```php
'allowed_origins' => [env('FRONTEND_URL')],
```
3. Clear config cache: `php artisan config:clear`

#### Mapbox Not Loading
**Problem**: Map doesn't appear

**Solution**:
1. Verify Mapbox token in settings
2. Check browser console for errors
3. Ensure token has correct scopes
4. Check domain restrictions on Mapbox

#### React Build Errors
**Error**: Build fails with module errors

**Solution**:
```bash
# Clear node modules
rm -rf node_modules package-lock.json

# Reinstall
npm install

# Clear Vite cache
rm -rf node_modules/.vite

# Rebuild
npm run build
```

### Admin Panel Issues

#### Can't Access Admin Panel
**Problem**: 403 Forbidden or redirect loop

**Solution**:
1. Check admin user exists: `php artisan tinker`
```php
User::where('email', 'admin@example.com')->first();
```
2. Reset admin password:
```bash
php artisan tinker
$user = User::where('email', 'admin@example.com')->first();
$user->password = Hash::make('newpassword');
$user->save();
```
3. Clear all caches:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

#### Filament Assets Not Loading
**Problem**: Admin panel has no styling

**Solution**:
```bash
# Publish Filament assets
php artisan filament:assets

# Clear view cache
php artisan view:clear

# Check public/filament directory exists
ls -la public/filament
```

### Performance Issues

#### Slow Page Load
**Problem**: Pages taking too long to load

**Solution**:
1. Enable caching:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
2. Optimize autoloader:
```bash
composer install --optimize-autoloader --no-dev
```
3. Check database queries for N+1 problems
4. Enable query logging to identify slow queries

#### High Memory Usage
**Problem**: PHP memory limit exceeded

**Solution**:
1. Increase memory limit in `php.ini`:
```ini
memory_limit = 256M
```
2. Optimize queries to use less memory
3. Use pagination for large datasets
4. Clear unused variables in long-running scripts

### Deployment Issues

#### 500 Internal Server Error
**Problem**: White screen or 500 error

**Solution**:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify file permissions:
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```
3. Check `.env` file exists
4. Enable debug mode temporarily:
```env
APP_DEBUG=true
```
5. Check PHP error logs

#### Assets Not Loading (404)
**Problem**: CSS/JS files return 404

**Solution**:
1. Check `APP_URL` in `.env` matches actual URL
2. Run asset build:
```bash
npm run build
```
3. Verify public directory structure
4. Check `.htaccess` file exists in public/

#### Session Errors
**Problem**: "Session store not set on request"

**Solution**:
1. Check session driver in `.env`
2. For database sessions, run:
```bash
php artisan session:table
php artisan migrate
```
3. Verify storage/framework/sessions is writable
4. Clear session data:
```bash
php artisan cache:clear
```

### Queue Issues

#### Jobs Not Processing
**Problem**: Queued jobs stuck in pending

**Solution**:
1. Start queue worker:
```bash
php artisan queue:work
```
2. For shared hosting, add to cron:
```bash
* * * * * php artisan queue:work --stop-when-empty
```
3. Check failed jobs:
```bash
php artisan queue:failed
```
4. Retry failed jobs:
```bash
php artisan queue:retry all
```

### Booking Issues

#### Email Verification Code Not Working
**Problem**: Verification code always invalid

**Solution**:
1. Check code expiration time (10 minutes default)
2. Verify timezone settings in `config/app.php`
3. Check verification_attempts counter
4. Clear any caching that might affect sessions

#### Price Calculation Incorrect
**Problem**: Fare doesn't match expected amount

**Solution**:
1. Check vehicle pricing tiers in admin
2. Verify distance calculation from Mapbox
3. Review fare_breakdown in database
4. Check for airport fees or surcharges

## Debug Commands

### Useful Artisan Commands
```bash
# Clear all caches
php artisan optimize:clear

# View routes
php artisan route:list

# Test email configuration
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('test@test.com')->subject('Test'));

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();

# View config values
php artisan tinker
>>> config('services.stripe.secret');

# Create test booking
php artisan tinker
>>> Booking::factory()->create();
```

### Logging & Debugging

#### Enable Query Logging
```php
// In AppServiceProvider boot()
\DB::listen(function ($query) {
    \Log::info($query->sql, $query->bindings);
});
```

#### Custom Debug Output
```php
// Add to any controller/service
\Log::debug('Debug message', ['data' => $variable]);

// View in logs
tail -f storage/logs/laravel.log
```

#### Stripe Debug Mode
```php
// In StripeService
\Stripe\Stripe::setLogger(\Log::channel('stripe'));
```

## Error Codes Reference

| Code | Description | Solution |
|------|-------------|----------|
| 401 | Unauthorized | Check authentication |
| 403 | Forbidden | Verify permissions |
| 404 | Not Found | Check route/resource exists |
| 419 | CSRF Token Mismatch | Refresh token or exempt route |
| 422 | Validation Failed | Check request data |
| 429 | Too Many Requests | Implement rate limiting |
| 500 | Server Error | Check logs for details |
| 502 | Bad Gateway | Check PHP-FPM service |
| 503 | Service Unavailable | Maintenance mode or server issue |

## Getting Help

### Log Locations
- Laravel: `storage/logs/laravel.log`
- Apache: `/var/log/apache2/error.log`
- Nginx: `/var/log/nginx/error.log`
- PHP: `/var/log/php/error.log`
- MySQL: `/var/log/mysql/error.log`

### Support Channels
1. Check this documentation
2. Review error logs
3. Search GitHub issues
4. Contact developer support
5. Stripe support for payment issues
6. Hostinger support for hosting issues