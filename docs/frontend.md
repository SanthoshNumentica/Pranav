# Frontend Documentation

This document describes the technical implementation of the Vue 3 frontend for the Pranav Diagnostics Centre Admin Panel.

## Vue 3 Project Structure

The frontend is built using **Vue 3** and located in `resources/js/admin/`.

- `resources/js/admin/views/`: Main page components.
- `resources/js/admin/components/`: Shared UI components.
- `resources/js/admin/services/`: API wrapper modules using Axios.
- `resources/js/admin/router/`: Vue Router configuration.
- `resources/js/admin/store/`: State management (if applicable).
- `resources/js/admin/composables/`: Reusable logic (hooks).

## Component Architecture

Each section is designed as a standalone view that uses shared components for layout and common UI elements.

### Reusable Components
- **Pagination**: Handles consistent table navigation.
- **Modal**: Base component for all popups.
- **StatusBadge**: Displays status (e.g., "Paid", "Pending") with consistent coloring.
- **SummaryCard**: Displays key metrics on dashboards and reports.

## Page Views

- **Dashboard**: High-level overview with key metrics and recent cases.
- **Case Reports**: Module for registering and managing scan cases.
- **Invoices**: Module for generating and managing billing.
- **Reports**: Specialized pages for detailed scan and invoice analysis.

## API Service Layer

We use a dedicated service layer to handle all HTTP communication.
- **Location**: `resources/js/admin/services/api.js`
- **Description**: Each JavaScript module corresponds to a backend controller (e.g., `patientService.js`).
- **Axios**: Configured with interceptors to handle authentication tokens and base URLs.

## Vue Router Configuration

- **Location**: `resources/js/admin/router/index.js`
- **Features**:
  - Lazy loading of components.
  - Navigation guards to redirect unauthenticated users to the login page.
  - Role-based route protection.

## UI & Styling (Tailwind CSS)

- The project uses **Tailwind CSS** for all styling.
- Custom colors and theme configurations are located in `tailwind.config.js`.
- Responsive design is implemented to ensure usability on various screen sizes.

## Form Handling & Validation

- Forms are built using native Vue `v-model`.
- Manual validation or libraries like `VeeValidate` may be used.
- Error messages from the Laravel backend are caught and displayed to the user.

## Dashboard & Admin Panel Structure

The main layout is defined in a wrapper component that includes:
1. **Sidebar**: Navigation links based on user permissions.
2. **Topbar**: User profile, notifications, and branch selection.
3. **Main Content**: The container for routing views.

## Data Fetching Strategy

- Data is usually fetched in the `onMounted` lifecycle hook of a view.
- `Loading` states are used to show skeletons or spinners during API calls.
- `Debouncing` is applied to search inputs to minimize API requests.
