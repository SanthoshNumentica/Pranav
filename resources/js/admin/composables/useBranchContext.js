import { ref, computed, watch } from "vue";

// Global state shared across all instances of the composable
const selectedBranchId = ref(localStorage.getItem("active_branch_id") || "all");

export function useBranchContext() {
    const setBranchId = (id) => {
        selectedBranchId.value = id?.toString() || "all";
        localStorage.setItem("active_branch_id", selectedBranchId.value);
    };

    const isAllBranches = computed(() => selectedBranchId.value === "all");

    return {
        selectedBranchId: computed(() => selectedBranchId.value),
        setBranchId,
        isAllBranches,
    };
}
