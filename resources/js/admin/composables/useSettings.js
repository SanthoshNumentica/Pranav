import { ref, computed } from "vue";
import axios from "axios";

const settings = ref({
    company_name: "Agoo Order",
    logo: null,
});

const loaded = ref(false);

export function useSettings() {
    const fetchSettings = async () => {
        if (loaded.value) return;

        try {
            const response = await axios.get("/api/v1/settings");
            if (response.data.success && response.data.data) {
                setSettings(response.data.data);
            }
        } catch (error) {
            console.error("Failed to fetch settings:", error);
            // Fallback for non-admins
            await fetchBranding();
        } finally {
            loaded.value = true;
        }
    };

    const fetchBranding = async () => {
        try {
            const response = await axios.get("/api/v1/settings/logo");
            if (response.data.success && response.data.data) {
                setSettings(response.data.data);
            }
        } catch (error) {
            console.error("Failed to fetch branding:", error);
        }
    };

    const setSettings = (data) => {
        if (!data) return;
        settings.value = {
            ...settings.value,
            ...data
        };
    };

    // Helper to get image source
    const getLogoSource = () => {
        const path = settings.value.logo;
        if (!path) return "";
        if (path.startsWith("data:image")) return path;
        return `/${path}`;
    };

    return {
        settings: computed(() => settings.value),
        fetchSettings,
        setSettings,
        getLogoSource
    };
}
