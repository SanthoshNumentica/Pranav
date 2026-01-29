<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-500"
    >
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
          Case Reports
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Manage diagnostic case reports.
        </p>
      </div>
      <div class="flex items-center gap-3">
        <button
          @click="$router.push('/case-reports/new')"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-700 text-white rounded-xl text-sm font-semibold transition-all shadow-md shadow-primary/10 active:scale-95"
        >
          <PlusIcon class="h-4 w-4" />
          New Case Report
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
              placeholder="Search Case ID, Patient..."
              class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              @input="debouncedFetch"
            />
          </div>

          <!-- Status Select -->
          <Select v-model="filters.status" @update:modelValue="fetchReports">
            <SelectTrigger class="w-full md:w-44 bg-slate-50">
              <SelectValue placeholder="All Statuses" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All Statuses</SelectItem>
              <SelectItem value="pending">Pending</SelectItem>
              <SelectItem value="available">Available</SelectItem>
              <SelectItem value="expired">Expired</SelectItem>
              <SelectItem value="deleted">Deleted</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>
    </div>

    <!-- Main Table Container -->
    <div
      class="bg-white rounded-3xl border border-slate-200 shadow-soft-xl overflow-x-auto custom-scrollbar animate-in fade-in slide-in-from-bottom-4 duration-700 delay-200"
    >
      <CaseReportsTable
        :reports="reports"
        :loading="loading"
        @view-info="handleView"
        @send-whatsapp="confirmWhatsApp"
        @delete="confirmDelete"
      />

      <!-- WhatsApp Confirmation Modal -->
      <ConfirmationModal
        :is-open="isWhatsappModalOpen"
        title="Send WhatsApp Notification"
        description="Are you sure you want to send the WhatsApp notification to the doctor/patient for this case report? This will use the default template."
        confirm-label="Send WhatsApp"
        variant="success"
        :icon="MessageSquareIcon"
        :loading="sendingWhatsapp"
        @close="isWhatsappModalOpen = false"
        @confirm="handleSendWhatsApp"
      />

      <!-- Confirmation Modal -->
      <ConfirmationModal
        :is-open="isDeleteModalOpen"
        title="Delete Case Report"
        description="Are you sure you want to delete this case report? This action will mark it as deleted and it will be hidden from the active list."
        confirm-label="Delete Case"
        variant="danger"
        :icon="TrashIcon"
        :loading="deleting"
        @close="isDeleteModalOpen = false"
        @confirm="handleDelete"
      />

      <!-- Info Dialog -->
      <CaseReportInfoDialog
        :is-open="isInfoOpen"
        :report="selectedReport"
        @close="isInfoOpen = false"
      />

      <!-- Pagination -->
      <Pagination
        v-if="pagination"
        :pagination="pagination"
        @page-change="handlePageChange"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from "vue";
import {
  Plus as PlusIcon,
  Search as SearchIcon,
  Trash2 as TrashIcon,
  MessageSquare as MessageSquareIcon,
} from "lucide-vue-next";
import axios from "axios";
import { debounce } from "lodash";
import CaseReportsTable from "../../components/CaseReportsTable.vue";
import CaseReportInfoDialog from "../../components/CaseReportInfoDialog.vue";
import ConfirmationModal from "../../components/ConfirmationModal.vue";
import Pagination from "../../components/Pagination.vue";
import { useRouter } from "vue-router";
import { useToast } from "../../composables/useToast";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../../components/ui/select";

const { addToast } = useToast();

const reports = ref([]);
const pagination = ref(null);
const loading = ref(true);
const isInfoOpen = ref(false);
const selectedReport = ref(null);
const filters = reactive({
  search: "",
  status: "",
});

const isWhatsappModalOpen = ref(false);
const sendingWhatsapp = ref(false);
const reportForWhatsapp = ref(null);

const fetchReports = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/case-reports", {
      params: { ...filters, page },
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
  // 1. Check for sharing token
  if (!report.sharing_token) {
    addToast({
      title: "Cannot Send WhatsApp",
      description: "Sharing token is missing for this case report.",
      variant: "error",
    });
    return;
  }

  // 2. Check for expired status
  if (report.status?.toLowerCase() === "expired") {
    addToast({
      title: "Cannot Send WhatsApp",
      description: "This case report has expired and cannot be shared.",
      variant: "error",
    });
    return;
  }

  // 3. Check for DICOM files
  const hasDicom = report.items?.some((item) =>
    item.documents?.some((doc) => doc.toLowerCase().endsWith(".dcm")),
  );

  if (!hasDicom) {
    addToast({
      title: "Cannot Send WhatsApp",
      description: "No DICOM files found in this case report.",
      variant: "error",
    });
    return;
  }

  reportForWhatsapp.value = report;
  isWhatsappModalOpen.value = true;
};

const handleSendWhatsApp = async () => {
  if (!reportForWhatsapp.value) return;

  sendingWhatsapp.value = true;
  try {
    const response = await axios.post(
      `/api/v1/case-reports/${reportForWhatsapp.value.id}/whatsapp`,
    );
    if (response.data.success) {
      isWhatsappModalOpen.value = false;
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

onMounted(() => {
  fetchReports(1);
});
</script>
