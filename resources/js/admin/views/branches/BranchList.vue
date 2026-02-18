<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-500"
    >
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
          Branches
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Manage organization branches and locations.
        </p>
      </div>
      <div class="flex items-center gap-3">
        <button
          v-if="modulePermissions.canAdd"
          @click="openAddModal"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-700 text-white rounded-xl text-sm font-semibold transition-all shadow-md shadow-primary/10 active:scale-95"
        >
          <PlusIcon class="h-4 w-4" />
          Add Branch
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
          <!-- Search -->
          <div class="relative w-full md:w-72 group">
            <SearchIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search Name, Code..."
              class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              @input="debouncedFetch"
            />
          </div>

          <div class="relative w-full md:w-48">
            <Select v-model="filters.status" @update:modelValue="fetchBranches">
              <SelectTrigger class="w-full pl-10">
                <SelectValue placeholder="Status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="active">Active</SelectItem>
                <SelectItem value="inactive">Inactive</SelectItem>
                <SelectItem value="all">All Status</SelectItem>
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
      <BranchesTable
        :branches="branches"
        :loading="loading"
        :permissions="{ ...modulePermissions, canStatus: hasStatusPermission }"
        @edit="handleEdit"
        @view-info="handleView"
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
    <BranchFormDialog
      :is-open="isFormModalOpen"
      :branch="selectedBranch"
      @close="isFormModalOpen = false"
      @saved="fetchBranches"
    />

    <BranchInfoDialog
      :is-open="isInfoModalOpen"
      :branch="selectedBranch"
      :can-edit="modulePermissions.canEdit"
      @close="isInfoModalOpen = false"
      @edit="handleEditFromInfo"
    />

    <ConfirmationModal
      :is-open="isDeleteModalOpen"
      title="Delete Branch"
      :description="`Are you sure you want to delete branch ${branchToDelete?.name}?`"
      confirm-label="Delete Branch"
      variant="danger"
      :icon="Trash2Icon"
      :loading="isDeleting"
      @close="isDeleteModalOpen = false"
      @confirm="confirmDelete"
    />

    <ConfirmationModal
      :is-open="isStatusModalOpen"
      title="Update Status"
      :description="`Are you sure you want to change the status of ${branchToToggle?.name} to ${nextStatus}?`"
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
import { ref, onMounted, reactive, computed } from "vue";
import {
  Plus as PlusIcon,
  Search as SearchIcon,
  Filter as FilterIcon,
  Trash2 as Trash2Icon,
  AlertCircle as AlertCircleIcon,
} from "lucide-vue-next";
import axios from "axios";
import { debounce } from "lodash";
import BranchesTable from "../../components/branches/BranchesTable.vue";
import BranchFormDialog from "../../components/branches/BranchFormDialog.vue";
import BranchInfoDialog from "../../components/branches/BranchInfoDialog.vue";
import ConfirmationModal from "../../components/ui/ConfirmationModal.vue";
import Pagination from "../../components/ui/Pagination.vue";
import { usePermissions } from "../../composables/usePermissions";
import { useToast } from "../../composables/useToast";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../../components/ui/select";

const { addToast } = useToast();
const { getModulePermissions, hasPermission } = usePermissions();
const modulePermissions = getModulePermissions("branch");
const hasStatusPermission = computed(() => hasPermission("branch", "status"));

const branches = ref([]);
const pagination = ref(null);
const loading = ref(true);
const filters = reactive({
  search: "",
  status: "all",
});

const isFormModalOpen = ref(false);
const isInfoModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const isStatusModalOpen = ref(false);
const isStatusUpdating = ref(false);
const selectedBranch = ref(null);
const branchToDelete = ref(null);
const branchToToggle = ref(null);
const nextStatus = ref("");

const fetchBranches = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/branches", {
      params: { ...filters, page },
    });
    if (response.data.success) {
      branches.value = response.data.data.data;
      pagination.value = response.data.data;
    }
  } catch (error) {
    console.error("Failed to fetch branches", error);
  } finally {
    loading.value = false;
  }
};

const handlePageChange = (page) => {
  fetchBranches(page);
};

const debouncedFetch = debounce(fetchBranches, 300);

const openAddModal = () => {
  selectedBranch.value = null;
  isFormModalOpen.value = true;
};

const handleView = (branch) => {
  selectedBranch.value = branch;
  isInfoModalOpen.value = true;
};

const handleEdit = (branch) => {
  selectedBranch.value = branch;
  isFormModalOpen.value = true;
};

const handleEditFromInfo = (branch) => {
  isInfoModalOpen.value = false;
  selectedBranch.value = branch;
  isFormModalOpen.value = true;
};

const handleDelete = (branch) => {
  branchToDelete.value = branch;
  isDeleteModalOpen.value = true;
};

const confirmDelete = async () => {
  if (!branchToDelete.value) return;
  isDeleting.value = true;
  try {
    await axios.delete(`/api/v1/branches/${branchToDelete.value.id}`);
    fetchBranches();
    isDeleteModalOpen.value = false;
    addToast({
      title: "Success",
      description: "Branch deleted successfully.",
      variant: "success",
    });
  } catch (err) {
    console.error("Failed to delete branch", err);
    addToast({
      title: "Error",
      description: "Failed to delete branch.",
      variant: "error",
    });
  } finally {
    isDeleting.value = false;
  }
};

const handleToggleStatus = (branch) => {
  branchToToggle.value = branch;
  nextStatus.value = branch.status === "active" ? "inactive" : "active";
  isStatusModalOpen.value = true;
};

const confirmToggleStatus = async () => {
  if (!branchToToggle.value) return;
  isStatusUpdating.value = true;
  try {
    await axios.post(`/api/v1/branches/${branchToToggle.value.id}/status`, {
      status: nextStatus.value,
    });
    fetchBranches();
    isStatusModalOpen.value = false;
    addToast({
      title: "Success",
      description: "Branch status updated successfully.",
      variant: "success",
    });
  } catch (err) {
    console.error("Failed to toggle status", err);
    addToast({
      title: "Error",
      description: "Failed to update branch status.",
      variant: "error",
    });
  } finally {
    isStatusUpdating.value = false;
  }
};

onMounted(() => {
  fetchBranches(1);
});
</script>
