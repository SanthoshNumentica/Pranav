import { createRouter, createWebHistory } from 'vue-router';
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
                component: () => import('../views/case-reports/CaseReportList.vue')
            },
            {
                path: 'case-reports/new',
                name: 'CaseReportCreate',
                component: () => import('../views/case-reports/CaseReportCreate.vue')
            },
            {
                path: 'case-reports/:id/edit',
                name: 'CaseReportEdit',
                component: () => import('../views/case-reports/CaseReportEdit.vue')
            },
            {
                path: 'patients',
                name: 'Patients',
                component: () => import('../views/patients/PatientList.vue')
            },
            {
                path: 'doctors',
                name: 'Doctors',
                component: () => import('../views/doctors/DoctorList.vue')
            },
            {
                path: 'masters/scan-types',
                name: 'ScanTypes',
                component: () => import('../views/masters/ScanTypeList.vue')
            },
            {
                path: 'masters/genders',
                name: 'Genders',
                component: () => import('../views/masters/GenderList.vue')
            },
            {
                path: 'masters/blood-groups',
                name: 'BloodGroups',
                component: () => import('../views/masters/BloodGroupList.vue')
            },
            {
                path: 'masters/titles',
                name: 'Titles',
                component: () => import('../views/masters/TitleList.vue')
            },
            {
                path: 'reports/patients',
                name: 'PatientReports',
                component: () => import('../views/reports/PatientReport.vue')
            },
            {
                path: 'reports/payments',
                name: 'PaymentReports',
                component: PlaceholderView
            },
            {
                path: 'reports/invoices',
                name: 'InvoiceReports',
                component: PlaceholderView
            },
            {
                path: 'reports/orders',
                name: 'OrderReports',
                component: PlaceholderView
            }
        ]
    },
    {
        path: '/view-report/:token',
        name: 'PublicReportView',
        component: () => import('../views/case-reports/PublicReportView.vue')
    },
    {
        path: '/case-reports/view-dicom',
        name: 'DicomView',
        component: () => import('../views/case-reports/DicomView.vue')
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

    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!token) {
            next({ name: 'Login' });
        } else {
            next();
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

export default router;
