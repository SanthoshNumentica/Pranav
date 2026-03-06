# Deployment Guide

This document provides instructions for deploying the Pranav Diagnostics Centre Admin Panel to a production server (Ubuntu/Nginx/Apache).

## Server Requirements

- **PHP**: 8.1+ (with extensions: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`)
- **Web Server**: Nginx or Apache
- **Database**: MySQL 5.7+
- **Composer**: Latest version
- **Node.js & NPM**: For building assets

## Step 1: Prepare the Server

Ensure your server is updated and has the necessary PHP extensions installed.

```bash
sudo apt update
sudo apt install php8.1-fpm php8.1-mysql php8.1-common php8.1-xml php8.1-bcmath php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd
```

## Step 2: Upload the Project

Clone the repository into your web directory (e.g., `/var/www/html/pranav`):

```bash
cd /var/www/html
git clone <repository-url> pranav
cd pranav
```

## Step 3: Install Backend Dependencies

Install Composer dependencies for production (excluding dev-only packages):

```bash
composer install --optimize-autoloader --no-dev
```

## Step 4: Build Frontend Assets

Building assets locally and uploading them is often easier, but if building on the server:

```bash
npm install
npm run build
```

The compiled files will be located in the `public/build` directory.

## Step 5: Environment Configuration

Copy the `.env.example` file and configure it for production:

```bash
cp .env.example .env
nano .env
```

Key changes for production:
- **APP_ENV**: `production`
- **APP_DEBUG**: `false`
- **APP_URL**: `https://your-domain.com`
- **DB_DATABASE**, **DB_USERNAME**, **DB_PASSWORD**: Production MySQL credentials.

## Step 6: Database Setup

Run migrations to set up the production database:

```bash
php artisan migrate --force
```

## Step 7: Final Optimization Commands

Run these Laravel commands to optimize performance on production:

```bash
php artisan config:cache     # Cache the .env and config files
php artisan route:cache      # Cache all routes for faster loading
php artisan view:cache       # Compile all blade views
php artisan storage:link     # Create the public storage link
```

## Step 8: Web Server Configuration (Nginx Example)

Create a configuration file for your site: `/etc/nginx/sites-available/pranav`.

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/pranav/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Enable the site and restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/pranav /etc/nginx/sites-enabled/
sudo nginx -t
sudo system service nginx restart
```

## Security Best Practices

1. **HTTPS**: Use Let's Encrypt (Certbot) to enable SSL for your domain.
2. **File Permissions**: Ensure `storage` and `bootstrap/cache` are writable only by the web server user (`www-data`).
3. **App Secret**: Ensure `APP_KEY` is generated and never shared.
4. **Debug Mode**: Never run with `APP_DEBUG=true` on production.

## Troubleshooting

- Check Laravel logs in `storage/logs/laravel.log`.
- Check Nginx error logs in `/var/log/nginx/error.log`.
- Ensure `php-fpm` is running.
