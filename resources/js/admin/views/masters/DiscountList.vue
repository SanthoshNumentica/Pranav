<template>
  <div class="space-y-6">
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4"
    >
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
          Discounts
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Manage discounts and their percentage values.
        </p>
      </div>
      <button
        v-if="canAdd"
        @click="openAddDialog"
        class="flex items-center gap-2 px-4 py-2 bg-primary hover:opacity-90 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-primary/20 active:scale-95"
      >
        <PlusIcon class="h-4 w-4" />
        Add Discount
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
              Discount Name
            </th>
            <th
              class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase"
            >
              Percentage
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
                <div class="h-4 bg-slate-50 rounded-md w-16"></div>
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
              v-for="(discount, index) in filteredDiscounts"
              :key="discount.id"
              class="group hover:bg-slate-50/50 transition-colors"
            >
              <td
                class="px-6 py-4 text-sm font-medium text-slate-500 font-mono"
              >
                {{ index + 1 }}
              </td>
              <td class="px-6 py-4 text-sm font-bold text-slate-700">
                {{ discount.name }}
              </td>
              <td class="px-6 py-4 text-sm font-mono text-slate-600">
                {{ discount.percentage }}%
              </td>
              <td class="px-6 py-4">
                <button
                  @click="toggleStatus(discount)"
                  :class="
                    cn(
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase transition-all duration-200 active:scale-95',
                      discount.status === 'active'
                        ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20'
                        : 'bg-slate-500/10 text-slate-500 border border-slate-500/20',
                    )
                  "
                >
                  <CircleIcon
                    class="h-2 w-2 mr-1.5 fill-current"
                    v-if="discount.status === 'active'"
                  />
                  {{ discount.status }}
                </button>
              </td>
              <td class="px-6 py-4 text-xs font-medium text-slate-500">
                <div class="flex flex-col">
                  <span class="text-xs text-slate-600">{{
                    formatDate(discount.created_at)
                  }}</span>
                  <span
                    v-if="discount.added_by_user"
                    class="text-[10px] text-slate-400"
                  >
                    by {{ discount.added_by_user?.name || "Unknown" }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-xs font-medium text-slate-500">
                <div class="flex flex-col">
                  <span class="text-xs text-slate-600">{{
                    formatDate(discount.updated_at)
                  }}</span>
                  <span
                    v-if="discount.modified_by_user"
                    class="text-[10px] text-slate-400"
                  >
                    by {{ discount.modified_by_user?.name || "Unknown" }}
                  </span>
                  <span
                    v-else-if="discount.added_by_user"
                    class="text-[10px] text-slate-400"
                  >
                    by {{ discount.added_by_user?.name || "Unknown" }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-right">
                <TableActions
                  :item="discount"
                  :permissions="{ canView, canEdit, canDelete }"
                  view-title="View"
                  edit-title="Edit"
                  delete-title="Delete"
                  @view="openViewDialog($event)"
                  @edit="openEditDialog($event)"
                  @delete="deleteDiscount($event)"
                />
              </td>
            </tr>
            <tr v-if="filteredDiscounts.length === 0">
              <td colspan="7" class="px-6 py-20 text-center">
                <p class="text-slate-400 italic">
                  No discounts found matching your filter.
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
      title="Discount"
      label="Discount"
      :icon="TagIcon"
      :initial-data="selectedDiscount"
      :loading="dialogLoading"
      :error="dialogError"
      @close="dialogOpen = false"
      @submit="handleDialogSubmit"
    >
      <template #default="{ form, mode, loading }">
        <div class="space-y-4">
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
            >
              Discount Name
            </label>
            <div class="relative">
              <input
                v-model="form.name"
                type="text"
                required
                :disabled="mode === 'view' || loading"
                placeholder="Enter discount name"
                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed text-slate-700"
              />
              <TagIcon
                class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"
              />
            </div>
          </div>

          <div class="space-y-1.5">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
            >
              Percentage (%)
            </label>
            <div class="relative">
              <input
                v-model="form.percentage"
                type="number"
                step="0.01"
                min="0"
                max="100"
                required
                :disabled="mode === 'view' || loading"
                placeholder="0.00"
                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed text-slate-700"
              />
              <PercentIcon
                class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"
              />
            </div>
          </div>
        </div>
      </template>
    </MasterDataDialog>

    <!-- Confirmation Modal -->
    <ConfirmationModal
      :is-open="confirmOpen"
      title="Delete Discount"
      :description="`Are you sure you want to delete '${selectedDiscount?.name}'?`"
      confirm-label="Delete"
      variant="danger"
      :loading="dialogLoading"
      @close="confirmOpen = false"
      @confirm="handleDeleteConfirm"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from "vue";
import axios from "axios";
import {
  Plus as PlusIcon,
  Circle as CircleIcon,
  Tag as TagIcon,
  Percent as PercentIcon,
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
// Assuming 'discounts' permission module exists or admins have access
const { canAdd, canView, canEdit, canDelete } =
  getModulePermissions("discounts");

const discounts = ref([]);
const pagination = ref(null);
const loading = ref(true);

const dialogOpen = ref(false);
const dialogMode = ref("add");
const selectedDiscount = ref(null);
const dialogLoading = ref(false);
const dialogError = ref(null);
const confirmOpen = ref(false);

const formData = reactive({
  id: null,
  name: "",
  percentage: "",
});

const filteredDiscounts = computed(() => {
  return discounts.value;
});

const fetchDiscounts = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/masters/discounts", {
      params: {
        page,
      },
    });
    if (response.data.success) {
      discounts.value = response.data.data.data;
      pagination.value = response.data.data;
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const handlePageChange = (page) => {
  fetchDiscounts(page);
};

const openAddDialog = () => {
  dialogMode.value = "add";
  selectedDiscount.value = null;
  formData.id = null;
  formData.name = "";
  formData.percentage = "";
  dialogError.value = null;
  dialogOpen.value = true;
};

const openEditDialog = (discount) => {
  dialogMode.value = "edit";
  selectedDiscount.value = {
    id: discount.id,
    name: discount.name,
    percentage: discount.percentage,
  };
  dialogError.value = null;
  dialogOpen.value = true;
};

const openViewDialog = (discount) => {
  dialogMode.value = "view";
  selectedDiscount.value = {
    id: discount.id,
    name: discount.name,
    percentage: discount.percentage,
    added_by_user: discount.added_by_user,
    modified_by_user: discount.modified_by_user,
  };
  dialogError.value = null;
  dialogOpen.value = true;
};

const handleDialogSubmit = async (formData) => {
  dialogLoading.value = true;
  dialogError.value = null;
  try {
    let response;
    if (dialogMode.value === "add") {
      response = await axios.post("/api/v1/masters/discounts", {
        name: formData.name,
        percentage: formData.percentage,
      });
    } else {
      response = await axios.put(`/api/v1/masters/discounts/${formData.id}`, {
        name: formData.name,
        percentage: formData.percentage,
      });
    }

    if (response.data.success) {
      await fetchDiscounts();
      dialogOpen.value = false;
      addToast({
        title: "Success",
        description: `Discount ${dialogMode.value === "add" ? "created" : "updated"} successfully.`,
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

const toggleStatus = async (discount) => {
  const newStatus = discount.status === "active" ? "inactive" : "active";
  try {
    const response = await axios.post(
      `/api/v1/masters/discounts/${discount.id}/status`,
      {
        status: newStatus,
      },
    );
    if (response.data.success) {
      discount.status = newStatus;
      addToast({
        title: "Success",
        description: "Discount status updated successfully.",
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

const deleteDiscount = (discount) => {
  selectedDiscount.value = discount;
  confirmOpen.value = true;
};

const handleDeleteConfirm = async () => {
  dialogLoading.value = true;
  try {
    const response = await axios.delete(
      `/api/v1/masters/discounts/${selectedDiscount.value.id}`,
    );
    if (response.data.success) {
      await fetchDiscounts();
      confirmOpen.value = false;
      addToast({
        title: "Success",
        description: "Discount deleted successfully.",
        variant: "success",
      });
    }
  } catch (e) {
    console.error(e);
    addToast({
      title: "Error",
      description: "Failed to delete discount.",
      variant: "error",
    });
  } finally {
    dialogLoading.value = false;
  }
};

onMounted(fetchDiscounts);
</script>
