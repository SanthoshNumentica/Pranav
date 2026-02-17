import { createRouter, createWebHistory } from 'vue-router';
import { usePermissions } from '../composables/usePermissions';
import Login from '../views/auth/Login.vue';
import Dashboard from '../views/dashboard/Dashboard.vue';
import Profile from '../views/profile/Profile.vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import PlaceholderView from '../views/PlaceholderView.vue';

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/',
        component: AdminLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'Dashboard',
                component: Dashboard
            },
            {
                path: 'profile',
                name: 'Profile',
                component: Profile
            },
            {
                path: 'case-reports',
                name: 'CaseReports',
                component: () => import('../views/case-reports/CaseReportList.vue'),
                meta: { module: 'case-reports' }
            },
            {
                path: 'case-reports/new',
                name: 'CaseReportCreate',
                component: () => import('../views/case-reports/CaseReportCreate.vue'),
                meta: { module: 'case-reports' }
            },
            {
                path: 'case-reports/:id/edit',
                name: 'CaseReportEdit',
                component: () => import('../views/case-reports/CaseReportEdit.vue'),
                meta: { module: 'case-reports' }
            },
            {
                path: 'patients',
                name: 'Patients',
                component: () => import('../views/patients/PatientList.vue'),
                meta: { module: 'patients' }
            },
            {
                path: 'doctors',
                name: 'Doctors',
                component: () => import('../views/doctors/DoctorList.vue'),
                meta: { module: 'doctors' }
            },
            {
                path: 'users',
                name: 'Users',
                component: () => import('../views/users/UserList.vue'),
                meta: { module: 'user' }
            },
            {
                path: 'roles',
                name: 'Roles',
                component: () => import('../views/roles/RoleList.vue'),
                meta: { module: 'role' }
            },
            {
                path: 'masters/scan-types',
                name: 'ScanTypes',
                component: () => import('../views/masters/ScanTypeList.vue'),
                meta: { module: 'scan-types' }
            },
            {
                path: 'masters/genders',
                name: 'Genders',
                component: () => import('../views/masters/GenderList.vue'),
                meta: { module: 'genders' }
            },
            {
                path: 'masters/blood-groups',
                name: 'BloodGroups',
                component: () => import('../views/masters/BloodGroupList.vue'),
                meta: { module: 'blood-groups' }
            },
            {
                path: 'masters/titles',
                name: 'Titles',
                component: () => import('../views/masters/TitleList.vue'),
                meta: { module: 'titles' }
            },
            {
                path: 'reports/patients',
                name: 'PatientReports',
                component: () => import('../views/reports/PatientReport.vue'),
                meta: { module: 'reports' }
            },
            {
                path: 'reports/payments',
                name: 'PaymentReports',
                component: PlaceholderView,
                meta: { module: 'reports' }
            },
            {
                path: 'reports/invoices',
                name: 'InvoiceReports',
                component: PlaceholderView,
                meta: { module: 'reports' }
            },
            {
                path: 'reports/orders',
                name: 'OrderReports',
                component: PlaceholderView,
                meta: { module: 'reports' }
            }
        ]
    },
    {
        path: '/view-report/:token',
        name: 'PublicReportView',
        component: () => import('../views/dicom/PublicReportView.vue')
    },
    {
        path: '/case-reports/view-dicom',
        name: 'DicomView',
        component: () => import('../views/dicom/DicomView.vue')
    },
    {
        // Default redirect
        path: '/:pathMatch(.*)*',
        redirect: '/'
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Navigation Guard
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('auth_token');

    // Import usePermissions here to avoid circular dependency issues if any,
    // though typically okay to import at top. But safe practice inside guard if needed.
    // However, for Composition API composables, we can import them at top level.
    // We will use strict import at top level, but here we can define logic.
    const { hasPermission, isSuperAdmin } = usePermissions();

    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!token) {
            next({ name: 'Login' });
        } else {
            // Check for module permission
            if (to.meta.module) {
                // If user is super admin or has permission to view/list the module
                // We use 'list' as the default action for viewing a module's page
                if (isSuperAdmin.value || hasPermission(to.meta.module, 'list')) {
                    next();
                } else {
                    // Redirect to dashboard or show 403
                    console.warn(`Access denied for module: ${to.meta.module}`);
                    next({ name: 'Dashboard' }); // Or a customized 403 page
                }
            } else {
                next();
            }
        }
    } else if (to.matched.some(record => record.meta.guest)) {
        if (token) {
            next({ name: 'Dashboard' });
        } else {
            next();
        }
    } else {
        next();
    }
});

// Handle dynamic import failures (e.g. after a new build)
router.onError((error) => {
    const message = error.message || error.toString();
    if (
        message.includes('Failed to fetch dynamically imported module') ||
        message.includes('error loading dynamically imported module') ||
        /loading chunk \d+ failed./i.test(message) ||
        (error.name === 'TypeError' && message.includes('import'))
    ) {
        window.location.reload();
    }
});

export default router;
