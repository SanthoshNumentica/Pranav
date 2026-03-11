<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
          Case Reports
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Manage diagnostic case reports.
        </p>
      </div>
      <div class="flex items-center gap-3">
        <button v-if="modulePermissions.canAdd" @click="$router.push('/case-reports/new')"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-700 text-white rounded-xl text-sm font-semibold transition-all shadow-md shadow-primary/10 active:scale-95">
          <PlusIcon class="h-4 w-4" />
          New Case Report
        </button>
      </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm animate-in fade-in duration-700 delay-100">
      <div class="flex flex-wrap items-end gap-x-6 gap-y-4">
        <!-- Date Filter -->
        <AdvancedDateFilter v-model="filters" @change="() => fetchReports(1)" />

        <!-- Vertical Divider -->
        <div class="hidden lg:block w-px h-10 bg-slate-100 self-end mb-0.5"></div>

        <div class="flex flex-wrap items-center gap-3 flex-1">
          <!-- Search Inner -->
          <div class="relative w-full md:w-64 group">
            <SearchIcon
              class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400 group-focus-within:text-primary transition-colors" />
            <input v-model="filters.search" type="text" placeholder="Search cases..."
              class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all h-9"
              @input="debouncedFetch" />
          </div>

          <!-- Status Select -->
          <div class="w-full md:w-44">
            <Select v-model="filters.status" @update:modelValue="() => fetchReports(1)">
              <SelectTrigger class="w-full h-9 bg-slate-50">
                <SelectValue placeholder="All Statuses" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Statuses</SelectItem>
                <SelectItem value="pending">Pending</SelectItem>
                <SelectItem value="available">Available</SelectItem>
                <SelectItem value="expired">Expired</SelectItem>
                <SelectItem value="deleted">Deleted</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Table Container -->
    <div
      class="bg-white rounded-3xl border border-slate-200 shadow-soft-xl overflow-x-auto custom-scrollbar animate-in fade-in slide-in-from-bottom-4 duration-700 delay-200">
      <CaseReportsTable :reports="reports" :loading="loading" :permissions="modulePermissions"
        :start-index="pagination?.from || 1" @view-info="handleView" @send-whatsapp="confirmWhatsApp"
        @delete="confirmDelete" @open-check-out="confirmCheckOut" />

      <!-- WhatsApp Recipient Selection Modal -->
      <WhatsAppRecipientModal :is-open="isWhatsappModalOpen" :report="reportForWhatsapp" :loading="sendingWhatsapp"
        @close="isWhatsappModalOpen = false" @confirm="handleSendWhatsApp" />

      <!-- Confirmation Modal -->
      <ConfirmationModal :is-open="isDeleteModalOpen" title="Delete Case Report"
        description="Are you sure you want to delete this case report? This action will mark it as deleted and it will be hidden from the active list."
        confirm-label="Delete Case" variant="danger" :icon="TrashIcon" :loading="deleting"
        @close="isDeleteModalOpen = false" @confirm="handleDelete" />

      <!-- Info Dialog -->
      <CaseReportInfoDialog :is-open="isInfoOpen" :report="selectedReport" @close="isInfoOpen = false" />

      <!-- Check-out Modal -->
      <CheckOutModal :is-open="isCheckOutModalOpen" :report="reportForCheckOut" :loading="updatingCheckOut"
        @close="isCheckOutModalOpen = false" @confirm="handleCheckOut" />

      <!-- Pagination -->
      <Pagination v-if="pagination" :pagination="pagination" @page-change="handlePageChange" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive, watch } from "vue";
import {
  Plus as PlusIcon,
  Search as SearchIcon,
  Trash2 as TrashIcon,
  MessageSquare as MessageSquareIcon,
  MapPin as MapPinIcon,
} from "lucide-vue-next";
import axios from "axios";
import { debounce } from "lodash";
import CaseReportsTable from "../../components/case-reports/CaseReportsTable.vue";
import CaseReportInfoDialog from "../../components/case-reports/CaseReportInfoDialog.vue";
import CheckOutModal from "../../components/case-reports/CheckOutModal.vue";
import ConfirmationModal from "../../components/ui/ConfirmationModal.vue";
import WhatsAppRecipientModal from "../../components/notifications/WhatsAppRecipientModal.vue";
import Pagination from "../../components/ui/Pagination.vue";
import AdvancedDateFilter from "../../components/reports/AdvancedDateFilter.vue";
import { useRouter } from "vue-router";
import { useToast } from "../../composables/useToast";
import { usePermissions } from "../../composables/usePermissions";
import { useBranchContext } from "../../composables/useBranchContext";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../../components/ui/select";

const { addToast } = useToast();
const { getModulePermissions } = usePermissions();
const { selectedBranchId } = useBranchContext();
const modulePermissions = getModulePermissions("case-reports");

const reports = ref([]);
const pagination = ref(null);
const loading = ref(true);
const isInfoOpen = ref(false);
const selectedReport = ref(null);
const filters = reactive({
  search: "",
  status: "all",
  branch_id: selectedBranchId.value,
  filter_type: "day",
  filter_option: "today",
  from_date: new Date().toISOString().split("T")[0],
  to_date: new Date().toISOString().split("T")[0],
});

const isWhatsappModalOpen = ref(false);
const sendingWhatsapp = ref(false);
const reportForWhatsapp = ref(null);

const isCheckOutModalOpen = ref(false);
const updatingCheckOut = ref(false);
const reportForCheckOut = ref(null);

const confirmCheckOut = (report) => {
  reportForCheckOut.value = report;
  isCheckOutModalOpen.value = true;
};

const handleCheckOut = async (time) => {
  if (!reportForCheckOut.value) return;

  updatingCheckOut.value = true;
  try {
    const response = await axios.put(
      `/api/v1/case-reports/${reportForCheckOut.value.id}/check-out`,
      { check_out: time },
    );
    if (response.data.success) {
      isCheckOutModalOpen.value = false;
      fetchReports();
      addToast({
        title: "Success",
        description: "Check-out time updated successfully.",
        variant: "success",
      });
    }
  } catch (error) {
    console.error("Failed to update check-out", error);
    addToast({
      title: "Error",
      description: error.response?.data?.message || "Failed to update check-out time.",
      variant: "error",
    });
  } finally {
    updatingCheckOut.value = false;
  }
};

const fetchReports = async (page = 1) => {
  loading.value = true;
  try {
    const params = { ...filters, page };
    if (params.status === "all") {
      delete params.status;
    }
    const response = await axios.get("/api/v1/case-reports", {
      params,
    });
    if (response.data.success) {
      reports.value = response.data.data.data;
      pagination.value = response.data.data;
    }
  } catch (error) {
    console.error("Failed to fetch reports", error);
  } finally {
    loading.value = false;
  }
};

const handlePageChange = (page) => {
  fetchReports(page);
};

const debouncedFetch = debounce(fetchReports, 300);

const handleView = (report) => {
  selectedReport.value = report;
  isInfoOpen.value = true;
};

const isDeleteModalOpen = ref(false);
const deleting = ref(false);
const reportToDelete = ref(null);

const confirmDelete = (report) => {
  reportToDelete.value = report;
  isDeleteModalOpen.value = true;
};

const handleDelete = async () => {
  if (!reportToDelete.value) return;

  deleting.value = true;
  try {
    const response = await axios.delete(
      `/api/v1/case-reports/${reportToDelete.value.id}`,
    );
    if (response.data.success) {
      isDeleteModalOpen.value = false;
      fetchReports();
      addToast({
        title: "Success",
        description: "Case report deleted successfully.",
        variant: "success",
      });
    }
  } catch (error) {
    console.error("Failed to delete report", error);
    addToast({
      title: "Error",
      description: "Failed to delete case report.",
      variant: "error",
    });
  } finally {
    deleting.value = false;
  }
};

const confirmWhatsApp = (report) => {
  reportForWhatsapp.value = report;
  isWhatsappModalOpen.value = true;
};

const handleSendWhatsApp = async (recipients) => {
  if (!reportForWhatsapp.value) return;

  sendingWhatsapp.value = true;
  try {
    const response = await axios.post(
      `/api/v1/case-reports/${reportForWhatsapp.value.id}/whatsapp`,
      { recipients },
    );
    if (response.data.success) {
      isWhatsappModalOpen.value = false;
      fetchReports(); // Refresh to show updated expiry/status
      addToast({
        title: "Success",
        description:
          response.data.message || "WhatsApp notification sent successfully.",
        variant: "success",
      });
    } else {
      addToast({
        title: "Error",
        description:
          response.data.message || "Failed to send WhatsApp notification.",
        variant: "error",
      });
    }
  } catch (error) {
    console.error("Failed to send WhatsApp notification", error);
    addToast({
      title: "Error",
      description: "An error occurred while sending WhatsApp notification.",
      variant: "error",
    });
  } finally {
    sendingWhatsapp.value = false;
  }
};

watch(selectedBranchId, (newId) => {
  if (newId === "all") {
    window.location.reload();
  } else {
    filters.branch_id = newId;
    fetchReports(1);
  }
});

onMounted(() => {
  fetchReports(1);
});
</script>
