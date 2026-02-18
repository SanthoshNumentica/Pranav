<template>
  <div class="space-y-6">
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
          Scan Types
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Manage available scan types and their specific procedures.
        </p>
      </div>
      <button
        v-if="canAdd"
        @click="openAddDialog"
        class="flex items-center gap-2 px-4 py-2 bg-primary hover:opacity-90 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-primary/20 active:scale-95"
      >
        <PlusIcon class="h-4 w-4" />
        Add Scan Type
      </button>
    </div>

    <div
      class="bg-white rounded-3xl border border-slate-200 shadow-soft-xl overflow-x-auto custom-scrollbar"
    >
      <table class="w-full">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/50">
            <th
              class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase"
            >
              S.No
            </th>
            <th
              class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase"
            >
              Scan Type
            </th>
            <th
              class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase font-mono"
            >
              Included Scans
            </th>
            <th
              class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase"
            >
              Status
            </th>
            <th
              class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase"
            >
              Created
            </th>
            <th
              class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase"
            >
              Modified
            </th>
            <th
              class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase"
            >
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <!-- Skeleton Loading State -->
          <template v-if="loading">
            <tr v-for="i in 5" :key="i" class="animate-pulse">
              <td class="px-6 py-4">
                <div class="h-4 bg-slate-50 rounded-md w-8"></div>
              </td>
              <td class="px-6 py-4">
                <div class="h-4 bg-slate-50 rounded-md w-32"></div>
              </td>
              <td class="px-6 py-4">
                <div class="h-4 bg-slate-50 rounded-md w-32"></div>
              </td>
              <td class="px-6 py-4">
                <div class="h-4 bg-slate-50 rounded-md w-64"></div>
              </td>
              <td class="px-6 py-4">
                <div class="h-6 bg-slate-50 rounded-full w-20"></div>
              </td>
              <td class="px-6 py-4">
                <div class="h-4 bg-slate-50 rounded-md w-24"></div>
              </td>
              <td class="px-6 py-4">
                <div class="h-8 bg-slate-50 rounded-lg w-20 ml-auto"></div>
              </td>
            </tr>
          </template>

          <template v-else>
            <tr
              v-for="(type, index) in filteredScanTypes"
              :key="type.id"
              class="group hover:bg-slate-50/50 transition-colors"
            >
              <td
                class="px-6 py-4 text-sm font-medium text-slate-500 font-mono"
              >
                {{ index + 1 }}
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-slate-900">{{
                    type.name
                  }}</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="scan in type.scans"
                    :key="scan.id"
                    class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-medium border border-slate-200/50"
                  >
                    {{ scan.name }}
                  </span>
                  <span
                    v-if="!type.scans?.length"
                    class="text-xs text-slate-400 italic font-mono"
                    >No sub-scans</span
                  >
                </div>
              </td>
              <td class="px-6 py-4">
                <button
                  @click="toggleStatus(type)"
                  :class="
                    cn(
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase transition-all duration-200 active:scale-95',
                      type.status === 'active'
                        ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20'
                        : 'bg-slate-500/10 text-slate-500 border border-slate-500/20',
                    )
                  "
                >
                  <CircleIcon
                    class="h-2 w-2 mr-1.5 fill-current"
                    v-if="type.status === 'active'"
                  />
                  {{ type.status }}
                </button>
              </td>
              <td class="px-6 py-4 text-xs font-medium text-slate-500">
                <div class="flex flex-col">
                  <span class="text-xs text-slate-600">{{
                    formatDate(type.created_at)
                  }}</span>
                  <span
                    v-if="type.added_by_user"
                    class="text-[10px] text-slate-400"
                  >
                    by {{ type.added_by_user?.name || "Unknown" }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-xs font-medium text-slate-500">
                <div class="flex flex-col">
                  <span class="text-xs text-slate-600">{{
                    formatDate(type.updated_at)
                  }}</span>
                  <span
                    v-if="type.modified_by_user"
                    class="text-[10px] text-slate-400"
                  >
                    by {{ type.modified_by_user?.name || "Unknown" }}
                  </span>
                  <span
                    v-else-if="type.added_by_user"
                    class="text-[10px] text-slate-400"
                  >
                    by {{ type.added_by_user?.name || "Unknown" }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-right">
                <TableActions
                  :item="type"
                  :permissions="{ canView, canEdit, canDelete }"
                  view-title="View"
                  edit-title="Edit"
                  delete-title="Delete"
                  @view="openViewDialog($event)"
                  @edit="openEditDialog($event)"
                  @delete="deleteScanType($event)"
                />
              </td>
            </tr>
            <tr v-if="filteredScanTypes.length === 0">
              <td colspan="5" class="px-6 py-20 text-center">
                <p class="text-slate-400 italic">
                  No scan types found matching your filter.
                </p>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <Pagination
      v-if="pagination"
      :pagination="pagination"
      @page-change="handlePageChange"
    />

    <!-- Dialog -->
    <ScanTypeDialog
      :is-open="dialogOpen"
      :mode="dialogMode"
      :initial-data="selectedType"
      :loading="dialogLoading"
      :error="dialogError"
      @close="dialogOpen = false"
      @submit="handleDialogSubmit"
    />

    <!-- Confirmation Modal -->
    <ConfirmationModal
      :is-open="confirmOpen"
      title="Delete Scan Type"
      :description="`Are you sure you want to delete '${selectedType?.name}'? This action cannot be undone.`"
      confirm-label="Delete"
      variant="danger"
      :loading="dialogLoading"
      @close="confirmOpen = false"
      @confirm="handleDeleteConfirm"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import {
  Plus as PlusIcon,
  Circle as CircleIcon,
  Activity as ScanIcon,
} from "lucide-vue-next";
import { formatDate } from "../../utils/format";
import ScanTypeDialog from "../../components/master-data/ScanTypeDialog.vue";
import TableActions from "../../components/ui/TableActions.vue";
import ConfirmationModal from "../../components/ui/ConfirmationModal.vue";
import Pagination from "../../components/ui/Pagination.vue";
import { useToast } from "../../composables/useToast";
import { usePermissions } from "../../composables/usePermissions";

// Utility for classes
function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}

const { addToast } = useToast();
const { getModulePermissions } = usePermissions();
const { canAdd, canView, canEdit, canDelete } =
  getModulePermissions("scan-types");

const scanTypes = ref([]);
const pagination = ref(null);
const loading = ref(true);

const dialogOpen = ref(false);
const dialogMode = ref("add");
const selectedType = ref(null);
const dialogLoading = ref(false);
const dialogError = ref(null);
const confirmOpen = ref(false);

const filteredScanTypes = computed(() => {
  return scanTypes.value; // Filtering is now done on backend side or we should handle it carefully with pagination.
  // For backend pagination + status filter, we should rely on API.
  // But since the current controller implementation handles status filtering, we just display what returned.
});

const fetchScanTypes = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/masters/scan-types", {
      params: {
        page,
      },
    });
    if (response.data.success) {
      scanTypes.value = response.data.data.data;
      pagination.value = response.data.data;
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const handlePageChange = (page) => {
  fetchScanTypes(page);
};

const openAddDialog = () => {
  dialogMode.value = "add";
  selectedType.value = null;
  dialogError.value = null;
  dialogOpen.value = true;
};

const openEditDialog = (type) => {
  dialogMode.value = "edit";
  selectedType.value = type;
  dialogError.value = null;
  dialogOpen.value = true;
};

const openViewDialog = (type) => {
  dialogMode.value = "view";
  selectedType.value = type;
  dialogError.value = null;
  dialogOpen.value = true;
};

const handleDialogSubmit = async (formData) => {
  dialogLoading.value = true;
  dialogError.value = null;
  try {
    let response;
    if (dialogMode.value === "add") {
      const data = {
        name: formData.name,
        scans: formData.scans.map((s) => ({
          name: s.name,
          amount: s.amount,
        })),
      };
      response = await axios.post("/api/v1/masters/scan-types", data);
    } else {
      response = await axios.put(
        `/api/v1/masters/scan-types/${formData.id}`,
        formData,
      );
    }

    if (response.data.success) {
      await fetchScanTypes();
      dialogOpen.value = false;
      addToast({
        title: "Success",
        description: `Scan type ${dialogMode.value === "add" ? "created" : "updated"} successfully.`,
        variant: "success",
      });
    }
  } catch (e) {
    console.error(e);
    dialogError.value = e.response?.data?.message || "Something went wrong";
    addToast({
      title: "Error",
      description: dialogError.value,
      variant: "error",
    });
  } finally {
    dialogLoading.value = false;
  }
};

const toggleStatus = async (type) => {
  const newStatus = type.status === "active" ? "inactive" : "active";
  try {
    const response = await axios.post(
      `/api/v1/masters/scan-types/${type.id}/status`,
      {
        status: newStatus,
      },
    );
    if (response.data.success) {
      type.status = newStatus;
      addToast({
        title: "Success",
        description: "Scan type status updated successfully.",
        variant: "success",
      });
    }
  } catch (e) {
    console.error(e);
    addToast({
      title: "Error",
      description: "Failed to update status.",
      variant: "error",
    });
  }
};

const deleteScanType = (type) => {
  selectedType.value = type;
  confirmOpen.value = true;
};

const handleDeleteConfirm = async () => {
  dialogLoading.value = true;
  try {
    const response = await axios.delete(
      `/api/v1/masters/scan-types/${selectedType.value.id}`,
    );
    if (response.data.success) {
      await fetchScanTypes();
      confirmOpen.value = false;
      addToast({
        title: "Success",
        description: "Scan type deleted successfully.",
        variant: "success",
      });
    }
  } catch (e) {
    console.error(e);
    addToast({
      title: "Error",
      description: "Failed to delete scan type.",
      variant: "error",
    });
  } finally {
    dialogLoading.value = false;
  }
};

onMounted(fetchScanTypes);
</script>
