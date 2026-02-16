<template>
  <div class="space-y-6">
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
          Genders
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Manage gender master data for patients and doctors.
        </p>
      </div>
      <button
        v-if="canAdd"
        @click="openAddDialog()"
        class="flex items-center gap-2 px-4 py-2 bg-primary hover:opacity-90 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-primary/20 active:scale-95"
      >
        <PlusIcon class="h-4 w-4" />
        Add Gender
      </button>
    </div>

    <!-- Filters -->
    <div class="flex items-center gap-2 p-1 bg-slate-100 w-fit rounded-xl">
      <button
        v-for="status in ['all', 'active', 'inactive']"
        :key="status"
        @click="filterStatus = status"
        :class="
          cn(
            'px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg transition-all',
            filterStatus === status
              ? 'bg-white text-primary shadow-sm'
              : 'text-slate-500 hover:text-slate-700',
          )
        "
      >
        {{ status }}
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
              Gender Name
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
                <div class="h-4 bg-slate-50 rounded-md w-24"></div>
              </td>
              <td class="px-6 py-4">
                <div class="h-6 bg-slate-50 rounded-full w-20"></div>
              </td>
              <td class="px-6 py-4">
                <div class="h-4 bg-slate-50 rounded-md w-32"></div>
              </td>
              <td class="px-6 py-4">
                <div class="h-4 bg-slate-50 rounded-md w-32"></div>
              </td>
              <td class="px-6 py-4">
                <div class="h-8 bg-slate-50 rounded-lg w-20 ml-auto"></div>
              </td>
            </tr>
          </template>

          <template v-else>
            <tr
              v-for="(g, index) in filteredGenders"
              :key="g.id"
              class="group hover:bg-slate-50/50 transition-colors"
            >
              <td
                class="px-6 py-4 text-sm font-medium text-slate-500 font-mono"
              >
                {{ index + 1 }}
              </td>
              <td class="px-6 py-4 text-sm font-bold text-slate-700">
                {{ g.gender_name }}
              </td>
              <td class="px-6 py-4">
                <button
                  @click="toggleStatus(g)"
                  :class="
                    cn(
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase transition-all duration-200 active:scale-95',
                      g.status === 'active'
                        ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20'
                        : 'bg-slate-500/10 text-slate-500 border border-slate-500/20',
                    )
                  "
                >
                  <CircleIcon
                    class="h-2 w-2 mr-1.5 fill-current"
                    v-if="g.status === 'active'"
                  />
                  {{ g.status }}
                </button>
              </td>
              <td class="px-6 py-4 text-xs font-medium text-slate-500">
                <div class="flex flex-col">
                  <span class="text-xs text-slate-600">{{
                    formatDate(g.created_at)
                  }}</span>
                  <span
                    v-if="g.added_by_user"
                    class="text-[10px] text-slate-400"
                  >
                    by {{ g.added_by_user?.name || "Unknown" }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-xs font-medium text-slate-500">
                <div class="flex flex-col">
                  <span class="text-xs text-slate-600">{{
                    formatDate(g.updated_at)
                  }}</span>
                  <span
                    v-if="g.modified_by_user"
                    class="text-[10px] text-slate-400"
                  >
                    by {{ g.modified_by_user?.name || "Unknown" }}
                  </span>
                  <span
                    v-else-if="g.added_by_user"
                    class="text-[10px] text-slate-400"
                  >
                    by {{ g.added_by_user?.name || "Unknown" }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-right">
                <TableActions
                  :item="g"
                  :permissions="{ canView, canEdit, canDelete }"
                  view-title="View"
                  edit-title="Edit"
                  delete-title="Delete"
                  @view="openViewDialog($event)"
                  @edit="openEditDialog($event)"
                  @delete="deleteGender($event)"
                />
              </td>
            </tr>
            <tr v-if="filteredGenders.length === 0">
              <td colspan="5" class="px-6 py-20 text-center">
                <p class="text-slate-400 italic">
                  No genders found matching your filter.
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
    <MasterDataDialog
      :is-open="dialogOpen"
      :mode="dialogMode"
      title="Gender"
      label="Gender"
      :icon="GenderIcon"
      :initial-data="selectedGender"
      :loading="dialogLoading"
      :error="dialogError"
      @close="dialogOpen = false"
      @submit="handleDialogSubmit"
    />

    <!-- Confirmation Modal -->
    <ConfirmationModal
      :is-open="confirmOpen"
      title="Delete Gender"
      :description="`Are you sure you want to delete '${selectedGender?.gender_name}'?`"
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
  Users2 as GenderIcon,
} from "lucide-vue-next";
import { formatDate } from "../../utils/format";
import MasterDataDialog from "../../components/master-data/MasterDataDialog.vue";
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
const { canAdd, canView, canEdit, canDelete } = getModulePermissions("genders");

const genders = ref([]);
const pagination = ref(null);
const loading = ref(true);
const filterStatus = ref("all");

const dialogOpen = ref(false);
const dialogMode = ref("add");
const selectedGender = ref(null);
const dialogLoading = ref(false);
const dialogError = ref(null);
const confirmOpen = ref(false);

const filteredGenders = computed(() => {
  return genders.value;
});

const fetchGenders = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/masters/genders", {
      params: {
        page,
        status: filterStatus.value,
      },
    });
    if (response.data.success) {
      genders.value = response.data.data.data;
      pagination.value = response.data.data;
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const handlePageChange = (page) => {
  fetchGenders(page);
};

watch(filterStatus, () => {
  fetchGenders(1);
});

const openAddDialog = () => {
  dialogMode.value = "add";
  selectedGender.value = null;
  dialogError.value = null;
  dialogOpen.value = true;
};

const openEditDialog = (gender) => {
  dialogMode.value = "edit";
  selectedGender.value = gender;
  dialogError.value = null;
  dialogOpen.value = true;
};

const openViewDialog = (gender) => {
  dialogMode.value = "view";
  selectedGender.value = gender;
  dialogError.value = null;
  dialogOpen.value = true;
};

const handleDialogSubmit = async (formData) => {
  dialogLoading.value = true;
  dialogError.value = null;
  try {
    let response;
    if (dialogMode.value === "add") {
      response = await axios.post("/api/v1/masters/genders", {
        gender_name: formData.name,
      });
    } else {
      response = await axios.put(`/api/v1/masters/genders/${formData.id}`, {
        gender_name: formData.name,
      });
    }

    if (response.data.success) {
      await fetchGenders();
      dialogOpen.value = false;
      addToast({
        title: "Success",
        description: `Gender ${dialogMode.value === "add" ? "created" : "updated"} successfully.`,
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

const toggleStatus = async (gender) => {
  const newStatus = gender.status === "active" ? "inactive" : "active";
  try {
    const response = await axios.post(
      `/api/v1/masters/genders/${gender.id}/status`,
      {
        status: newStatus,
      },
    );
    if (response.data.success) {
      gender.status = newStatus;
      addToast({
        title: "Success",
        description: "Gender status updated successfully.",
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

const deleteGender = (gender) => {
  selectedGender.value = gender;
  confirmOpen.value = true;
};

const handleDeleteConfirm = async () => {
  dialogLoading.value = true;
  try {
    const response = await axios.delete(
      `/api/v1/masters/genders/${selectedGender.value.id}`,
    );
    if (response.data.success) {
      await fetchGenders();
      confirmOpen.value = false;
      addToast({
        title: "Success",
        description: "Gender deleted successfully.",
        variant: "success",
      });
    }
  } catch (e) {
    console.error(e);
    addToast({
      title: "Error",
      description: "Failed to delete gender.",
      variant: "error",
    });
  } finally {
    dialogLoading.value = false;
  }
};

onMounted(fetchGenders);
</script>
