# Backend Documentation

This document describes the technical implementation of the Laravel backend for the Pranav Diagnostics Centre Admin Panel.

## Laravel Project Structure

We follow the standard Laravel folder structure but place all Admin-related logic in its own subdirectories.

- `app/Http/Controllers/Admin/`: All API controllers for the admin panel.
- `app/Http/Requests/`: Request classes for validation.
- `app/Http/Resources/`: Classes for transforming models into JSON responses.
- `app/Services/`: The core business logic layer.
- `app/Models/`: Eloquent models defining database tables and relationships.
- `app/Exports/`: Specialized classes for Excel/CSV data exports.

## Service Layer Pattern

The service layer is the brain of the application. Controllers simply trigger services and return results.

### Example: ReportService
- **Purpose**: Handles all complex queries for various reports (Case Analysis, Invoice Analysis, etc.).
- **Location**: `app/Services/ReportService.php`
- **Responsibilities**:
  - Filter application logic.
  - Calculation of summary statistics.
  - Data aggregation for charts and exports.

## API Resources

We use Laravel API Resources to ensure consistent response structures across all endpoints.
- **Example**: `PatientResource` transforms the `Patient` model into a JSON object with specific fields.

## Middleware & Security

- `auth:sanctum`: Ensures that only authenticated users can access the protected routes.
- `Spatie Permission Middleware`: Checks if the user has the required permission for a specific route (e.g., `role:manager`).

## Routing Structure

Routes for the admin panel are centralized in `routes/admin.php`.

- **Prefix**: `/api/admin/v1/`
- **Route Groups**: Organized by module (Patients, Referers, Users, etc.).
- **Standards**: RESTful naming conventions are strictly followed.

## Database Migrations

All changes to the database schema are version-controlled via migrations.
- **Location**: `database/migrations/`
- **Tables**: Includes `case_reports`, `invoices`, `patients`, `referers`, `scan_types`, and the standard Spatie permission tables.

## Business Logic Flow

1. **Request Validation**: Incoming data is validated using a `FormRequest`.
2. **Service Interaction**: The `Controller` calls a method in the appropriate `Service`.
3. **Model Interaction**: The `Service` uses `Eloquent` to interact with the database.
4. **Data Transformation**: The `Controller` uses an `API Resource` to format the result.
5. **JSON Response**: A standard structure is returned to the frontend.

## Authentication (Laravel Sanctum)

Sanctum provides an easy-to-use token authentication system for SPAs.
- When a user logs in, a `token` is generated.
- The token is sent in the `Authorization` header for all subsequent requests.

## Role-Based Access Control (Spatie)

The system uses standard roles and permissions:
- **Roles**: Super Admin, Admin, Manager, Staff.
- **Permissions**: `view cases`, `edit cases`, `delete cases`, `view billing`.
- Roles are assigned to users, and permissions are attached to roles.
