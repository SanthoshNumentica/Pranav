import { ref } from 'vue';

const toasts = ref([]);

export function useToast() {
    const addToast = ({ title, description, variant = 'success', duration = 3000 }) => {
        const id = Date.now() + Math.random();
        toasts.value.push({ id, title, description, variant });

        if (duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, duration);
        }
    };

    const removeToast = (id) => {
        const index = toasts.value.findIndex((t) => t.id === id);
        if (index !== -1) {
            toasts.value.splice(index, 1);
        }
    };

    return {
        toasts,
        addToast,
        removeToast,
    };
}
