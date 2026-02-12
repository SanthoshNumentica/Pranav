import { computed, reactive } from "vue";
import { useAuth } from "./useAuth";

export function usePermissions() {
    const { user, permissions: authPermissions } = useAuth();

    const permissions = computed(() => {
        // Prefer the explicitly managed permissions ref, with fallbacks to user object properties
        return authPermissions.value && authPermissions.value.length > 0
            ? authPermissions.value
            : (user.value?.permission_names || user.value?.permissions || []);
    });
    const userRole = computed(() => user.value?.role?.name?.toLowerCase() || user.value?.role?.toLowerCase() || "");
    const isSuperAdmin = computed(() => userRole.value === "super-admin" || userRole.value === "super admin");

    const hasPermission = (module, action = 'list') => {
        if (isSuperAdmin.value) return true;
        const permissionName = `${module.toLowerCase()}-${action.toLowerCase()}`;
        return permissions.value.includes(permissionName);
    };

    const getModulePermissions = (module) => {
        return reactive({
            canView: computed(() =>
                hasPermission(module, 'view') ||
                hasPermission(module, 'create') ||
                hasPermission(module, 'edit') ||
                hasPermission(module, 'delete')
            ),
            canAdd: computed(() => hasPermission(module, 'create')),
            canEdit: computed(() => hasPermission(module, 'edit')),
            canDelete: computed(() => hasPermission(module, 'delete')),
        });
    };

    return {
        isSuperAdmin,
        permissions,
        hasPermission,
        getModulePermissions,
    };
}
