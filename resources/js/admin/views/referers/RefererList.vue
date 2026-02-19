<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-500"
    >
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
          Referers
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Manage referers and their details.
        </p>
      </div>
      <div class="flex items-center gap-3">
        <button
          v-if="modulePermissions.canAdd"
          @click="openAddModal"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-700 text-white rounded-xl text-sm font-semibold transition-all shadow-md shadow-primary/10 active:scale-95"
        >
          <PlusIcon class="h-4 w-4" />
          Add Referer
        </button>
      </div>
    </div>

    <!-- Filters & Search -->
    <div
      class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm animate-in fade-in duration-700 delay-100"
    >
      <div
        class="flex flex-col md:flex-row md:items-center justify-between gap-4"
      >
        <div class="flex flex-wrap items-center gap-3 flex-1">
          <!-- Search Inner -->
          <div class="relative w-full md:w-72 group">
            <SearchIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search Name, Mobile..."
              class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              @input="debouncedFetch"
            />
          </div>

          <div class="relative w-full md:w-48">
            <Select v-model="filters.status" @update:modelValue="fetchReferers">
              <SelectTrigger class="w-full pl-10">
                <SelectValue placeholder="Status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="active">Active Referers</SelectItem>
                <SelectItem value="inactive">Inactive Referers</SelectItem>
                <SelectItem value="all">All Referers</SelectItem>
              </SelectContent>
            </Select>
            <FilterIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none group-focus-within:text-primary transition-colors"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Main Table Container -->
    <div
      class="bg-white rounded-3xl border border-slate-200 shadow-soft-xl overflow-x-auto custom-scrollbar animate-in fade-in slide-in-from-bottom-4 duration-700 delay-200"
    >
      <ReferersTable
        :referers="referers"
        :loading="loading"
        :permissions="modulePermissions"
        @edit="handleEdit"
        @delete="handleDelete"
        @toggle-status="handleToggleStatus"
      />
      <!-- Pagination -->
      <Pagination
        v-if="pagination"
        :pagination="pagination"
        @page-change="handlePageChange"
      />
    </div>

    <!-- Modals -->
    <RefererFormDialog
      :is-open="isFormModalOpen"
      :referer="selectedReferer"
      @close="isFormModalOpen = false"
      @saved="fetchReferers"
    />

    <ConfirmationModal
      :is-open="isDeleteModalOpen"
      title="Delete Referer"
      :description="`Are you sure you want to delete referer ${refererToDelete?.name}? This action will move the record to trash.`"
      confirm-label="Delete Referer"
      variant="danger"
      :icon="Trash2Icon"
      :loading="isDeleting"
      @close="isDeleteModalOpen = false"
      @confirm="confirmDelete"
    />

    <ConfirmationModal
      :is-open="isStatusModalOpen"
      title="Update Status"
      :description="`Are you sure you want to change the status of ${refererToToggle?.name} to ${nextStatus}?`"
      confirm-label="Update Status"
      variant="warning"
      :icon="AlertCircleIcon"
      :loading="isStatusUpdating"
      @close="isStatusModalOpen = false"
      @confirm="confirmToggleStatus"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from "vue";
import {
  Plus as PlusIcon,
  Search as SearchIcon,
  Filter as FilterIcon,
  Trash2 as Trash2Icon,
  AlertCircle as AlertCircleIcon,
  Stethoscope as StethoscopeIcon, // Fallback icon if needed
} from "lucide-vue-next";
import axios from "axios";
import { debounce } from "lodash";
import ReferersTable from "../../components/referers/ReferersTable.vue";
import RefererFormDialog from "../../components/referers/RefererFormDialog.vue";
import ConfirmationModal from "../../components/ui/ConfirmationModal.vue";
import Pagination from "../../components/ui/Pagination.vue";
import { useToast } from "../../composables/useToast";
import { usePermissions } from "../../composables/usePermissions";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../../components/ui/select";

const { addToast } = useToast();
const { getModulePermissions } = usePermissions();
const modulePermissions = getModulePermissions("referers");

const referers = ref([]);
const pagination = ref(null);
const loading = ref(true);
const filters = reactive({
  search: "",
  status: "active",
});

const isFormModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const isStatusModalOpen = ref(false);
const isStatusUpdating = ref(false);
const selectedReferer = ref(null);
const refererToDelete = ref(null);
const refererToToggle = ref(null);
const nextStatus = ref("");

const fetchReferers = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/referers", {
      params: { ...filters, page },
    });
    if (response.data.success) {
      referers.value = response.data.data.data;
      pagination.value = response.data.data;
    }
  } catch (error) {
    console.error("Failed to fetch referers", error);
  } finally {
    loading.value = false;
  }
};

const handlePageChange = (page) => {
  fetchReferers(page);
};

const debouncedFetch = debounce(fetchReferers, 300);

const openAddModal = () => {
  selectedReferer.value = null;
  isFormModalOpen.value = true;
};

const handleEdit = (referer) => {
  selectedReferer.value = referer;
  isFormModalOpen.value = true;
};

const handleDelete = (referer) => {
  refererToDelete.value = referer;
  isDeleteModalOpen.value = true;
};

const confirmDelete = async () => {
  if (!refererToDelete.value) return;
  isDeleting.value = true;
  try {
    await axios.delete(`/api/v1/referers/${refererToDelete.value.id}`);
    fetchReferers();
    isDeleteModalOpen.value = false;
    addToast({
      title: "Success",
      description: "Referer moved to trash successfully.",
      variant: "success",
    });
  } catch (err) {
    console.error("Failed to delete referer", err);
    addToast({
      title: "Error",
      description: "Failed to delete referer.",
      variant: "error",
    });
  } finally {
    isDeleting.value = false;
  }
};

const handleToggleStatus = (referer) => {
  refererToToggle.value = referer;
  nextStatus.value = referer.status === "active" ? "inactive" : "active";
  isStatusModalOpen.value = true;
};

const confirmToggleStatus = async () => {
  if (!refererToToggle.value) return;
  isStatusUpdating.value = true;
  try {
    await axios.post(`/api/v1/referers/${refererToToggle.value.id}/status`, {
      status: nextStatus.value,
    });
    fetchReferers();
    isStatusModalOpen.value = false;
    addToast({
      title: "Success",
      description: "Referer status updated successfully.",
      variant: "success",
    });
  } catch (err) {
    console.error("Failed to toggle status", err);
    addToast({
      title: "Error",
      description: "Failed to update referer status.",
      variant: "error",
    });
  } finally {
    isStatusUpdating.value = false;
  }
};

onMounted(() => {
  fetchReferers(1);
});
</script>
