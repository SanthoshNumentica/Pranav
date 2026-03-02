<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
          Payment Methods
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Manage available methods for recording invoice payments.
        </p>
      </div>
      <button v-if="canAdd" @click="openAddDialog"
        class="flex items-center gap-2 px-4 py-2 bg-primary hover:opacity-90 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-primary/20 active:scale-95">
        <PlusIcon class="h-4 w-4" />
        Add Method
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
            <tr v-for="(method, index) in methods" :key="method.id"
              class="group hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4 text-sm font-medium text-slate-500 font-mono">
                {{ (pagination?.from || 1) + index }}
              </td>
              <td class="px-6 py-4 text-sm font-bold text-slate-700">
                {{ method.name }}
              </td>
              <td class="px-6 py-4">
                <button @click="toggleStatus(method)" :class="cn(
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase transition-all duration-200 active:scale-95',
                  method.status === 'active'
                    ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20'
                    : 'bg-slate-500/10 text-slate-500 border border-slate-500/20',
                )
                  ">
                  <CircleIcon class="h-2 w-2 mr-1.5 fill-current" v-if="method.status === 'active'" />
                  {{ method.status }}
                </button>
              </td>
              <td class="px-6 py-4 text-xs font-medium text-slate-500">
                <div class="flex flex-col">
                  <span class="text-xs text-slate-600">{{
                    formatDate(method.created_at)
                  }}</span>
                  <span v-if="method.added_by_user" class="text-[10px] text-slate-400">
                    by {{ method.added_by_user?.name || "Unknown" }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-xs font-medium text-slate-500">
                <div class="flex flex-col">
                  <span class="text-xs text-slate-600">{{
                    formatDate(method.updated_at)
                  }}</span>
                  <span v-if="method.modified_by_user" class="text-[10px] text-slate-400">
                    by {{ method.modified_by_user?.name || "Unknown" }}
                  </span>
                  <span v-else-if="method.added_by_user" class="text-[10px] text-slate-400">
                    by {{ method.added_by_user?.name || "Unknown" }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-right">
                <TableActions :item="method" :permissions="{ canView, canEdit, canDelete }" view-title="View"
                  edit-title="Edit" delete-title="Delete" @view="openViewDialog($event)" @edit="openEditDialog($event)"
                  @delete="deleteMethod($event)" />
              </td>
            </tr>
            <tr v-if="methods.length === 0">
              <td colspan="6" class="px-6 py-20 text-center">
                <p class="text-slate-400 italic">No payment methods found.</p>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <Pagination v-if="pagination" :pagination="pagination" @page-change="handlePageChange" />

    <!-- Dialog -->
    <MasterDataDialog :is-open="isModalOpen" :mode="dialogMode" title="Payment Method" label="Payment Method"
      :icon="WalletIcon" :initial-data="editingMethod" :loading="isSubmitting" :error="dialogError"
      @close="isModalOpen = false" @submit="handleDialogSubmit" />

    <!-- Confirmation Modal -->
    <ConfirmationModal :is-open="confirmOpen" title="Delete Payment Method"
      :description="`Are you sure you want to delete '${editingMethod?.name}'?`" confirm-label="Delete" variant="danger"
      :loading="isSubmitting" @close="confirmOpen = false" @confirm="handleDeleteConfirm" />
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import {
  Plus as PlusIcon,
  Circle as CircleIcon,
  Wallet as WalletIcon,
  X as XIcon,
  AlertCircle as AlertCircleIcon,
  Loader2 as Loader2Icon,
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
const { canAdd, canView, canEdit, canDelete } =
  getModulePermissions("payment-methods");

const methods = ref([]);
const pagination = ref(null);
const loading = ref(true);

const isModalOpen = ref(false);
const dialogMode = ref("add");
const editingMethod = ref(null);
const isSubmitting = ref(false);
const dialogError = ref(null);
const confirmOpen = ref(false);

const form = ref({ name: "" });

const fetchMethods = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/masters/payment-methods", {
      params: {
        page,
        status: "all",
      },
    });
    if (response.data.success) {
      methods.value = response.data.data.data;
      pagination.value = response.data.data;
    }
  } catch (error) {
    console.error("Failed to fetch methods", error);
  } finally {
    loading.value = false;
  }
};

const handlePageChange = (page) => {
  fetchMethods(page);
};

const openAddDialog = () => {
  dialogMode.value = "add";
  editingMethod.value = null;
  form.value = { name: "" };
  dialogError.value = null;
  isModalOpen.value = true;
};

const openEditDialog = (method) => {
  dialogMode.value = "edit";
  editingMethod.value = method;
  form.value = { name: method.name };
  dialogError.value = null;
  isModalOpen.value = true;
};

const openViewDialog = (method) => {
  dialogMode.value = "view";
  editingMethod.value = { id: method.id, name: method.name };
  dialogError.value = null;
  isModalOpen.value = true;
};

const handleDialogSubmit = async (formData) => {
  isSubmitting.value = true;
  dialogError.value = null;
  try {
    let response;
    if (dialogMode.value === "add") {
      response = await axios.post("/api/v1/masters/payment-methods", {
        name: formData.name,
      });
    } else {
      response = await axios.put(
        `/api/v1/masters/payment-methods/${formData.id}`,
        {
          name: formData.name,
        },
      );
    }

    if (response.data.success) {
      await fetchMethods();
      isModalOpen.value = false;
      addToast({
        title: "Success",
        description: `Payment method ${dialogMode.value === "add" ? "created" : "updated"} successfully.`,
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

const toggleStatus = async (method) => {
  const newStatus = method.status === "active" ? "inactive" : "active";
  try {
    await axios.post(`/api/v1/masters/payment-methods/${method.id}/status`, {
      status: newStatus,
    });
    method.status = newStatus;
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

const deleteMethod = (method) => {
  editingMethod.value = method;
  confirmOpen.value = true;
};

const handleDeleteConfirm = async () => {
  isSubmitting.value = true;
  try {
    const response = await axios.delete(
      `/api/v1/masters/payment-methods/${editingMethod.value.id}`,
    );
    if (response.data.success) {
      await fetchMethods();
      confirmOpen.value = false;
      addToast({
        title: "Success",
        description: "Payment method deleted successfully.",
        variant: "success",
      });
    }
  } catch (error) {
    console.error(error);
    addToast({
      title: "Error",
      description: "Failed to delete method.",
      variant: "error",
    });
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(fetchMethods);
</script>
