import { ref, computed } from "vue";

// Global state
const user = ref(JSON.parse(localStorage.getItem("auth_user") || "null"));
const permissions = ref(JSON.parse(localStorage.getItem("auth_permissions") || "[]"));

export function useAuth() {
    const setUser = (userData, permissionsData) => {
        user.value = userData;
        permissions.value = permissionsData || [];

        if (userData) {
            localStorage.setItem("auth_user", JSON.stringify(userData));
        } else {
            localStorage.removeItem("auth_user");
        }

        if (permissionsData) {
            localStorage.setItem("auth_permissions", JSON.stringify(permissionsData));
        } else {
            localStorage.removeItem("auth_permissions");
        }
    };

    const logout = () => {
        user.value = null;
        permissions.value = [];
        localStorage.removeItem("auth_token");
        localStorage.removeItem("auth_user");
        localStorage.removeItem("auth_permissions");
        window.location.href = "/login";
    };

    return {
        user: computed(() => user.value),
        permissions: computed(() => permissions.value),
        setUser,
        logout,
    };
}
