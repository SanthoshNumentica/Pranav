---
description: How to run the Laravel + Vue project
---

1. **Install Backend Dependencies**
   Run the following command to install PHP dependencies:
   ```bash
   composer install
   ```

2. **Install Frontend Dependencies**
   Run the following command to install Node.js dependencies:
   ```bash
   npm install
   ```

3. **Setup Environment**
   - Ensure `.env` file exists. If not, copy `.env.example` to `.env`.
   - Generate application key if missing:
     ```bash
     php artisan key:generate
     ```
   - Configure your database settings in `.env`.

4. **Run Database Migrations**
   Set up the database tables:
   ```bash
   php artisan migrate
   ```

5. **Start the Application**
   You need to run two servers in separate terminals:

   **Terminal 1 (Backend):**
   ```bash
   php artisan serve
   ```

   **Terminal 2 (Frontend):**
   ```bash
   npm run dev
   ```

   Access the application at `http://localhost:8000`.
