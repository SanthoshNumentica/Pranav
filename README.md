# Benny Cards Admin Panel

An Order Management Admin Panel built with **Laravel 10** and **Vue 3**.  
Designed to integrate with an existing database, following strict architectural patterns.

## Tech Stack

- **Backend**: Laravel 10, PHP 8.1+
- **Frontend**: Vue 3, Tailwind CSS, Vite
- **Database**: MySQL (Existing Schema)
- **Auth**: Laravel Sanctum (Token-based)
- **ACL**: Spatie Laravel-Permission

## Folder Structure

Following the `agoo_foods` project pattern:

```
app/
 ├── Http/
 │   ├── Controllers/
 │   │    └── Admin/          # Thin Controllers
 │   ├── Requests/            # Validation Logic
 │   ├── Resources/           # API Resources
 │   └── Middleware/
 ├── Services/                # Business Logic (Service Layer)
 ├── Models/                  # Eloquent Models
 ├── Enums/                   # Status Enums
 ├── Repositories/            # (Optional) Data Access
routes/
 ├── api.php                  # Public API
 └── admin.php                # Admin Panel Routes
resources/js/admin/
 ├── views/                   # Vue Pages
 ├── components/              # Reusable Components
 ├── services/                # API Wrappers
 ├── router/                  # Vue Router Config
 └── store/                   # Vuex/Pinia Store
```

## Setup Instructions

### 1. Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js & NPM
- MySQL

### 2. Installation

```bash
# Clone repository
git clone <repo-url>
cd benny_cards_admin

# Install PHP dependencies
composer install

# Install JS dependencies
npm install
```

### 3. Environment Configuration

Copy `.env.example` to `.env` and configure your **existing database credentials**.

```bash
cp .env.example .env
php artisan key:generate
```

> **Important**: Do NOT run `php artisan migrate:fresh` as it will wipe the existing database. Only run migrations if adding _new_ tables (e.g., for permissions), but ensure you don't conflict with core tables.

### 4. Running Locally

Start the backend server:

```bash
php artisan serve
```

Start the frontend development server:

```bash
npm run dev
```

## Architecture Usage

### Creating a New Module (e.g., Products)

1.  **Model**: Create `app/Models/Product.php` mapping to the table.
2.  **Service**: Create `app/Services/ProductService.php` for logic.
3.  **Controller**: Create `app/Http/Controllers/Admin/ProductController.php`. Inject Service.
4.  **Route**: Add to `routes/admin.php`.
5.  **Frontend**: Create view in `resources/js/admin/views/products/ProductList.vue`.

### API Response Standard

All API responses must follow this JSON format:

```json
{
    "success": true,
    "message": "Optional success message",
    "data": { ... } // Object or Array
}
```

## Security

- All Admin routes are protected by `auth:sanctum`.
- Use `FormRequests` for validation.
- Use `RoleMiddleware` for permission checks.
