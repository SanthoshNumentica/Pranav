# Deployment Guide (Laravel + Vue/Vite)

This guide outlines the steps to deploy your application to a live server.

## 1. Files to Upload

You need to upload almost all files in your project directory to the live server, **EXCEPT** for the following:

### Do NOT Upload:
- `node_modules/` (These are development dependencies)
- `.git/` (If you are using version control)
- `.env` (You will create a specific `.env` file on the production server)
- `tests/`
- Any local development database files (e.g., `database/database.sqlite`)

### Make sure you DO Upload:
- `public/build/` (This contains your compiled Vite assets, which you just successfully built)
- `vendor/` (Alternatively, you can skip uploading this and run
 `/opt/ecp-php83/bin/php $(which composer) install --optimize-autoloader --no-dev` directly on the live server)

## 2. Server Setup

1. **Upload Files:** Upload the relevant files to your server using FTP, SFTP, or a CI/CD pipeline.
2. **Document Root:** Ensure your web server (Apache/Nginx) document root is pointing to the `public/` directory of your Laravel project, NOT the root project folder.
    - Correct: `/var/www/your-project/public` or `/home/user/public_html/your-project/public`
    - Incorrect: `/var/www/your-project`

## 3. Environment Configuration

1. Copy `.env.example` to `.env` on your live server (or create a new `.env` file).
2. Update the `.env` variables for your production environment. Crucially:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-live-domain.com

   # Update Database Credentials
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_live_db_name
   DB_USERNAME=your_live_db_user
   DB_PASSWORD=your_live_db_password
   ```
3. Generate an application key on the server (if it's a fresh install):
   ```bash
   /opt/ecp-php83/bin/php artisan key:generate
   ```

## 4. Set Permissions

Laravel needs write permissions to specific directories to function correctly (for logs, cache, sessions, etc.). 

Run the following commands on your server (adjust the user group if necessary, commonly `www-data` on Ubuntu/Debian or `apache` on CentOS/RedHat):
```bash
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache
```

## 5. Database Setup

1. Create a fresh database on your live server.
2. Run your migrations to set up the database structure:
   ```bash
   /opt/ecp-php83/bin/php artisan migrate --force
   ```
   *(Note: The `--force` flag is required when running migrations in a production environment)*

## 6. Optimize and Cache

To maximize performance in production, cache your configuration, routes, and views:
```bash
/opt/ecp-php83/bin/php artisan config:cache
/opt/ecp-php83/bin/php artisan route:cache
/opt/ecp-php83/bin/php artisan view:cache
/opt/ecp-php83/bin/php artisan event:cache
```

## 7. Storage Link

If your application allows users to upload files that need to be publicly accessible (like profile pictures or documents), create a symbolic link from `public/storage` to `storage/app/public`:
```bash
/opt/ecp-php83/bin/php artisan storage:link
```

## Summary Checklist

- [ ] Upload project files (excluding `node_modules`, local `.env`, etc.)
- [ ] Point web server Document Root to the `public/` directory
- [ ] Create and configure the `.env` file (`APP_ENV=production`, `APP_DEBUG=false`)
- [ ] Set write permissions on `storage/` and `bootstrap/cache/`
- [ ] Run `/opt/ecp-php83/bin/php $(which composer) install --optimize-autoloader --no-dev` (if you didn't upload the `vendor` folder)
- [ ] Run migrations: `/opt/ecp-php83/bin/php artisan migrate --force`
- [ ] Cache config, routes, and views for performance
- [ ] Link storage (if applicable): `/opt/ecp-php83/bin/php artisan storage:link`
