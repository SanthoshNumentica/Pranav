# System Architecture

This document explains the high-level architecture and the technical design patterns used in the Pranav Diagnostics Centre Admin Panel.

## High-Level Overview

The system follows a modern decoupled architecture where **Laravel** serves as a robust RESTful API (Backend) and **Vue 3** serves as a reactive Single Page Application (Frontend).

- **Frontend**: Vue 3 (Composition API), Tailwind CSS, Vite.
- **Backend**: Laravel 10+, MySQL, REST API.
- **Communication**: Axios handles HTTP requests between Vue and Laravel.
- **Authentication**: Token-based authentication using **Laravel Sanctum**.

## Backend Architecture: Service Layer Pattern

We adopt a "Thin Controller, Thick Service" approach to keep the codebase maintainable and testable.

### 1. Controllers (Http/Controllers/Admin)
Controllers are responsible for handling incoming requests, calling relevant services, and returning JSON responses. They do **not** contain business logic.

### 2. Service Layer (app/Services)
All business logic, complex queries, and data manipulation reside here. Services are injected into controllers via Dependency Injection.

### 3. Models (app/Models)
Eloquent models represent the database tables and define relationships (e.g., `CaseReport` has many `CaseReportItem`).

### 4. Form Requests (Http/Requests)
Dedicated classes handle request validation before it reaches the controller, keeping the controller clean.

## Frontend Architecture: Component-Based

The frontend is built using **Vue 3** with the **Composition API** for better logic reuse.

### 1. Views (resources/js/admin/views)
Each major section of the admin panel (Dashboard, Case Reports, Invoices) has a dedicated view component.

### 2. Reusable Components (resources/js/admin/components)
UI elements like Modals, Tables, Status Badges, and Pagination are extracted into reusable components.

### 3. API Service Layer (resources/js/admin/services)
JavaScript classes/modules encapsulating Axios calls to the backend endpoints.

## API Communication Flow

1. **User Action**: User clicks a button in the Vue UI.
2. **Frontend Call**: The Vue component calls a method in the JS `service` layer.
3. **HTTP Request**: Axios sends an asynchronous request (with Sanctum CSRF/Bearer token) to a Laravel route.
4. **Backend Processing**: 
   - `admin.php` route points to a `Controller`.
   - `Controller` validates via `FormRequest`.
   - `Controller` calls a `Service`.
   - `Service` interacts with `Model` (Database).
5. **Response**: Laravel returns a structured JSON response.
6. **UI Update**: Vue receives the data and reactively updates the DOM.

## Authentication & Security

### Laravel Sanctum
Authentication is handled via Sanctum. For SPA communication, it uses stateful cookies; for mobile or external APIs, it supports Bearer tokens.

### Role & Permission Management
We use the **Spatie Laravel-Permission** package.
- **Roles**: High-level groups (e.g., Super Admin, Branch Manager).
- **Permissions**: Granular actions (e.g., `view reports`, `edit invoices`).
- Permissions are checked on both the Backend (Middleware) and Frontend (UI visibility).

## Database Interaction

- **Eloquent ORM**: Used for all database operations.
- **Migrations**: Version control for the database schema.
- **Seeders**: Used for populating initial system configuration and roles.

## Folder Structure Summary

```text
app/
 ├── Http/Controllers/Admin/  # API entry points
 ├── Http/Requests/           # Validation logic
 ├── Services/                # Core business logic
 ├── Models/                  # Database entities
resources/js/admin/
 ├── views/                   # Page components
 ├── components/              # Shared UI components
 ├── services/                # Axios API wrappers
 ├── router/                  # URL mapping (Vue Router)
```
