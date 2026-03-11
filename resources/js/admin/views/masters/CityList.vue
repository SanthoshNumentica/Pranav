<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
          Cities
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Manage city master data for patient and referer addresses.
        </p>
      </div>
      <button v-if="canAdd" @click="openAddDialog"
        class="flex items-center gap-2 px-4 py-2 bg-primary hover:opacity-90 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-primary/20 active:scale-95">
        <PlusIcon class="h-4 w-4" />
        Add City
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
              Name
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
            <tr v-for="(city, index) in cities" :key="city.id"
              class="group hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4 text-sm font-medium text-slate-500 font-mono">
                {{ (pagination?.from || 1) + index }}
              </td>
              <td class="px-6 py-4 text-sm font-bold text-slate-700">
                {{ city.name }}
              </td>
              <td class="px-6 py-4">
                <button @click="toggleStatus(city)" :class="cn(
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase transition-all duration-200 active:scale-95',
                  city.status === 'active'
                    ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20'
                    : 'bg-slate-500/10 text-slate-500 border border-slate-500/20',
                )
                  ">
                  <CircleIcon class="h-2 w-2 mr-1.5 fill-current" v-if="city.status === 'active'" />
                  {{ city.status }}
                </button>
              </td>
              <td class="px-6 py-4 text-xs font-medium text-slate-500">
                <div class="flex flex-col">
                  <span class="text-xs text-slate-600">{{
                    formatDate(city.created_at)
                  }}</span>
                  <span v-if="city.added_by_user" class="text-[10px] text-slate-400">
                    by {{ city.added_by_user?.name || "Unknown" }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-xs font-medium text-slate-500">
                <div class="flex flex-col">
                  <span class="text-xs text-slate-600">{{
                    formatDate(city.updated_at)
                  }}</span>
                  <span v-if="city.modified_by_user" class="text-[10px] text-slate-400">
                    by {{ city.modified_by_user?.name || "Unknown" }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-right">
                <TableActions :item="city" :permissions="{ canView, canEdit, canDelete }" view-title="View"
                  edit-title="Edit" delete-title="Delete" @view="openViewDialog($event)" @edit="openEditDialog($event)"
                  @delete="deleteCity($event)" />
              </td>
            </tr>
            <tr v-if="cities.length === 0">
              <td colspan="6" class="px-6 py-20 text-center">
                <p class="text-slate-400 italic">No cities found.</p>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <Pagination v-if="pagination" :pagination="pagination" @page-change="handlePageChange" />

    <!-- Dialog -->
    <MasterDataDialog :is-open="isModalOpen" :mode="dialogMode" title="City" label="City Name"
      :icon="MapPinIcon" :initial-data="editingCity" :loading="isSubmitting" :error="dialogError"
      @close="isModalOpen = false" @submit="handleDialogSubmit" />

    <!-- Confirmation Modal -->
    <ConfirmationModal :is-open="confirmOpen" title="Delete City"
      :description="`Are you sure you want to delete '${editingCity?.name}'?`" confirm-label="Delete" variant="danger"
      :loading="isSubmitting" @close="confirmOpen = false" @confirm="handleDeleteConfirm" />
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import {
  Plus as PlusIcon,
  Circle as CircleIcon,
  MapPin as MapPinIcon,
} from "lucide-vue-next";
import { formatDate } from "../../utils/format";
import MasterDataDialog from "../../components/master-data/MasterDataDialog.vue";
import TableActions from "../../components/ui/TableActions.vue";
import ConfirmationModal from "../../components/ui/ConfirmationModal.vue";
import Pagination from "../../components/ui/Pagination.vue";
import { useToast } from "../../composables/useToast";
import { usePermissions } from "../../composables/usePermissions";

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}

const { addToast } = useToast();
const { getModulePermissions } = usePermissions();
const { canAdd, canView, canEdit, canDelete } =
  getModulePermissions("cities");

const cities = ref([]);
const pagination = ref(null);
const loading = ref(true);

const isModalOpen = ref(false);
const dialogMode = ref("add");
const editingCity = ref(null);
const isSubmitting = ref(false);
const dialogError = ref(null);
const confirmOpen = ref(false);

const fetchCities = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/masters/cities", {
      params: {
        page,
        status: "all",
      },
    });
    if (response.data.success) {
      cities.value = response.data.data.data;
      pagination.value = response.data.data;
    }
  } catch (error) {
    console.error("Failed to fetch cities", error);
  } finally {
    loading.value = false;
  }
};

const handlePageChange = (page) => {
  fetchCities(page);
};

const openAddDialog = () => {
  dialogMode.value = "add";
  editingCity.value = null;
  dialogError.value = null;
  isModalOpen.value = true;
};

const openEditDialog = (city) => {
  dialogMode.value = "edit";
  editingCity.value = city;
  dialogError.value = null;
  isModalOpen.value = true;
};

const openViewDialog = (city) => {
  dialogMode.value = "view";
  editingCity.value = { id: city.id, name: city.name };
  dialogError.value = null;
  isModalOpen.value = true;
};

const handleDialogSubmit = async (formData) => {
  isSubmitting.value = true;
  dialogError.value = null;
  try {
    let response;
    if (dialogMode.value === "add") {
      response = await axios.post("/api/v1/masters/cities", {
        name: formData.name,
      });
    } else {
      response = await axios.put(
        `/api/v1/masters/cities/${formData.id}`,
        {
          name: formData.name,
        },
      );
    }

    if (response.data.success) {
      await fetchCities();
      isModalOpen.value = false;
      addToast({
        title: "Success",
        description: `City ${dialogMode.value === "add" ? "created" : "updated"} successfully.`,
        variant: "success",
      });
    }
  } catch (error) {
    console.error(error);
    dialogError.value = error.response?.data?.message || "Something went wrong";
    addToast({
      title: "Error",
      description: dialogError.value,
      variant: "error",
    });
  } finally {
    isSubmitting.value = false;
  }
};

const toggleStatus = async (city) => {
  const newStatus = city.status === "active" ? "inactive" : "active";
  try {
    await axios.post(`/api/v1/masters/cities/${city.id}/status`, {
      status: newStatus,
    });
    city.status = newStatus;
    addToast({
      title: "Success",
      description: "Status updated successfully",
      variant: "success",
    });
  } catch (error) {
    addToast({
      title: "Error",
      description: "Failed to update status",
      variant: "error",
    });
  }
};

const deleteCity = (city) => {
  editingCity.value = city;
  confirmOpen.value = true;
};

const handleDeleteConfirm = async () => {
  isSubmitting.value = true;
  try {
    const response = await axios.delete(
      `/api/v1/masters/cities/${editingCity.value.id}`,
    );
    if (response.data.success) {
      await fetchCities();
      confirmOpen.value = false;
      addToast({
        title: "Success",
        description: "City deleted successfully.",
        variant: "success",
      });
    }
  } catch (error) {
    console.error(error);
    addToast({
      title: "Error",
      description: "Failed to delete city.",
      variant: "error",
    });
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(fetchCities);
</script>
