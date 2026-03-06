# Installation Guide

This guide provides step-by-step instructions to set up the Pranav Diagnostics Centre Admin Panel on your local development environment.

## Prerequisites

Ensure you have the following installed on your system:

- **PHP**: 8.1 or higher
- **Composer**: Latest version
- **Node.js**: 16.x or higher
- **NPM**: 8.x or higher
- **MySQL**: 5.7 or higher
- **Web Server**: Apache or Nginx (or use `php artisan serve`)

## Step 1: Clone the Repository

Clone the project from your repository provider:

```bash
git clone <repository-url>
cd <project-folder>
```

## Step 2: Install PHP Dependencies

Use Composer to install the required Laravel packages:

```bash
composer install
```

## Step 3: Install Frontend Dependencies

Install the Node.js packages required for the Vue 3 frontend:

```bash
npm install
```

## Step 4: Environment Configuration

Create a local environment file by copying the example file:

```bash
cp .env.example .env
```

Open `.env` in your code editor and configure the following:

- **APP_URL**: `http://localhost:8000`
- **DB_CONNECTION**: `mysql`
- **DB_HOST**: `127.0.0.1`
- **DB_PORT**: `3306`
- **DB_DATABASE**: `your_database_name`
- **DB_USERNAME**: `your_username`
- **DB_PASSWORD**: `your_password`

## Step 5: Generate Application Key

Generate the unique application key for encryption:

```bash
php artisan key:generate
```

## Step 6: Database Setup & Migrations

> [!WARNING]
> If you are working with an existing database, **DO NOT** run `php artisan migrate:fresh`. This will delete all existing data.

Run the migrations to create the necessary tables (including Spatie permissions if not already present):

```bash
php artisan migrate
```

If you need to seed initial data (roles, permissions, or admin user), run:

```bash
php artisan db:seed
```

## Step 7: Linking Storage

Create a symbolic link from `public/storage` to `storage/app/public` to make uploaded files accessible:

```bash
php artisan storage:link
```

## Step 8: Running the Application

To run the project locally, you need to start both the Laravel backend and the Vite development server.

### Start Backend Server:
```bash
php artisan serve
```
The backend will be available at `http://localhost:8000`.

### Start Frontend Dev Server:
```bash
npm run dev
```
The frontend will be served and automatically proxy requests to the backend.

## Production Build

To build the frontend assets for production:

```bash
npm run build
```

## Common Installation Issues

### 1. Permission Denied on Storage/Cache
If you encounter permission errors, ensure the `storage` and `bootstrap/cache` directories are writable:
```bash
chmod -R 775 storage bootstrap/cache
```

### 2. Missing `.env` variables
Ensure all required keys from `.env.example` are present in your `.env` file, especially the database and Sanctum configuration.

### 3. Node/NPM Version Mismatch
If `npm install` fails, verify your Node.js version (`node -v`). This project is optimized for Node 16+.
