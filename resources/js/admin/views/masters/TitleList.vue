<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Titles</h1>
        <p class="text-sm text-slate-500 mt-1">
          Manage titles for patients and doctors.
        </p>
      </div>
      <button v-if="canAdd" @click="openAddDialog"
        class="flex items-center gap-2 px-4 py-2 bg-primary hover:opacity-90 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-primary/20 active:scale-95">
        <PlusIcon class="h-4 w-4" />
        Add Title
      </button>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-soft-xl overflow-x-auto custom-scrollbar">
      <table class="w-full">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/50">
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">
              S.No
            </th>
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">
              Title Name
            </th>
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">
              Status
            </th>
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">
              Created
            </th>
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase">
              Modified
            </th>
            <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase">
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
            <tr v-for="(title, index) in filteredTitles" :key="title.id"
              class="group hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4 text-sm font-medium text-slate-500 font-mono">
                {{ (pagination?.from || 1) + index }}
              </td>
              <td class="px-6 py-4 text-sm font-bold text-slate-700">
                {{ title.title_name }}
              </td>
              <td class="px-6 py-4">
                <button @click="toggleStatus(title)" :class="cn(
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase transition-all duration-200 active:scale-95',
                  title.status === 'active'
                    ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20'
                    : 'bg-slate-500/10 text-slate-500 border border-slate-500/20',
                )
                  ">
                  <CircleIcon class="h-2 w-2 mr-1.5 fill-current" v-if="title.status === 'active'" />
                  {{ title.status }}
                </button>
              </td>
              <td class="px-6 py-4 text-xs font-medium text-slate-500">
                <div class="flex flex-col">
                  <span class="text-xs text-slate-600">{{
                    formatDate(title.created_at)
                  }}</span>
                  <span v-if="title.added_by_user" class="text-[10px] text-slate-400">
                    by {{ title.added_by_user?.name || "Unknown" }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-xs font-medium text-slate-500">
                <div class="flex flex-col">
                  <span class="text-xs text-slate-600">{{
                    formatDate(title.updated_at)
                  }}</span>
                  <span v-if="title.modified_by_user" class="text-[10px] text-slate-400">
                    by {{ title.modified_by_user?.name || "Unknown" }}
                  </span>
                  <span v-else-if="title.added_by_user" class="text-[10px] text-slate-400">
                    by {{ title.added_by_user?.name || "Unknown" }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-right">
                <TableActions :item="title" :permissions="{ canView, canEdit, canDelete }" view-title="View"
                  edit-title="Edit" delete-title="Delete" @view="openViewDialog($event)" @edit="openEditDialog($event)"
                  @delete="deleteTitle($event)" />
              </td>
            </tr>
            <tr v-if="filteredTitles.length === 0">
              <td colspan="5" class="px-6 py-20 text-center">
                <p class="text-slate-400 italic">
                  No titles found matching your filter.
                </p>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <Pagination v-if="pagination" :pagination="pagination" @page-change="handlePageChange" />

    <!-- Dialog -->
    <MasterDataDialog :is-open="dialogOpen" :mode="dialogMode" title="Title" label="Title" :icon="CaseSensitiveIcon"
      :initial-data="selectedTitle" :loading="dialogLoading" :error="dialogError" @close="dialogOpen = false"
      @submit="handleDialogSubmit" />

    <!-- Confirmation Modal -->
    <ConfirmationModal :is-open="confirmOpen" title="Delete Title"
      :description="`Are you sure you want to delete '${selectedTitle?.title_name}'?`" confirm-label="Delete"
      variant="danger" :loading="dialogLoading" @close="confirmOpen = false" @confirm="handleDeleteConfirm" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import {
  Plus as PlusIcon,
  Circle as CircleIcon,
  UserCircle as TitleIcon,
  CaseSensitive as CaseSensitiveIcon,
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
const { canAdd, canView, canEdit, canDelete } = getModulePermissions("titles");

const titles = ref([]);
const pagination = ref(null);
const loading = ref(true);

const dialogOpen = ref(false);
const dialogMode = ref("add");
const selectedTitle = ref(null);
const dialogLoading = ref(false);
const dialogError = ref(null);
const confirmOpen = ref(false);

const filteredTitles = computed(() => {
  return titles.value;
});

const fetchTitles = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/masters/titles", {
      params: {
        page,
      },
    });
    if (response.data.success) {
      titles.value = response.data.data.data;
      pagination.value = response.data.data;
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const handlePageChange = (page) => {
  fetchTitles(page);
};

const openAddDialog = () => {
  dialogMode.value = "add";
  selectedTitle.value = null;
  dialogError.value = null;
  dialogOpen.value = true;
};

const openEditDialog = (title) => {
  dialogMode.value = "edit";
  selectedTitle.value = { id: title.id, name: title.title_name }; // Mapping title_name to name for dialog
  dialogError.value = null;
  dialogOpen.value = true;
};

const openViewDialog = (title) => {
  dialogMode.value = "view";
  selectedTitle.value = { id: title.id, name: title.title_name }; // Mapping title_name to name for dialog
  dialogError.value = null;
  dialogOpen.value = true;
};

const handleDialogSubmit = async (formData) => {
  dialogLoading.value = true;
  dialogError.value = null;
  try {
    let response;
    if (dialogMode.value === "add") {
      response = await axios.post("/api/v1/masters/titles", {
        title_name: formData.name,
      });
    } else {
      response = await axios.put(`/api/v1/masters/titles/${formData.id}`, {
        title_name: formData.name,
      });
    }

    if (response.data.success) {
      await fetchTitles();
      dialogOpen.value = false;
      addToast({
        title: "Success",
        description: `Title ${dialogMode.value === "add" ? "created" : "updated"} successfully.`,
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

const toggleStatus = async (title) => {
  const newStatus = title.status === "active" ? "inactive" : "active";
  try {
    const response = await axios.post(
      `/api/v1/masters/titles/${title.id}/status`,
      {
        status: newStatus,
      },
    );
    if (response.data.success) {
      title.status = newStatus;
      addToast({
        title: "Success",
        description: "Title status updated successfully.",
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

const deleteTitle = (title) => {
  selectedTitle.value = title;
  confirmOpen.value = true;
};

const handleDeleteConfirm = async () => {
  dialogLoading.value = true;
  try {
    const response = await axios.delete(
      `/api/v1/masters/titles/${selectedTitle.value.id}`,
    );
    if (response.data.success) {
      await fetchTitles();
      confirmOpen.value = false;
      addToast({
        title: "Success",
        description: "Title deleted successfully.",
        variant: "success",
      });
    }
  } catch (e) {
    console.error(e);
    addToast({
      title: "Error",
      description: "Failed to delete title.",
      variant: "error",
    });
  } finally {
    dialogLoading.value = false;
  }
};

onMounted(fetchTitles);
</script>
