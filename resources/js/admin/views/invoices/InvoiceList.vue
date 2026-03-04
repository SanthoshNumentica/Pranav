<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
          Invoices
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Manage and track billing for case reports.
        </p>
      </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm animate-in fade-in duration-700 delay-100">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3 flex-1">
          <div class="relative w-full md:w-72 group">
            <SearchIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors" />
            <input v-model="search" type="text" placeholder="Search by invoice no or patient name..."
              class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              @input="debouncedFetch" />
          </div>

          <div class="relative w-full md:w-48">
            <Select v-model="statusFilter" @update:modelValue="() => fetchInvoices(1)">
              <SelectTrigger class="w-full pl-10">
                <SelectValue placeholder="Status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Statuses</SelectItem>
                <SelectItem value="unpaid">Unpaid</SelectItem>
                <SelectItem value="due">Due</SelectItem>
                <SelectItem value="fully_paid">Fully Paid</SelectItem>
                <SelectItem value="cancelled">Cancelled</SelectItem>
              </SelectContent>
            </Select>
            <FilterIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none group-focus-within:text-primary transition-colors" />
          </div>
        </div>
      </div>
    </div>

    <!-- Invoices Table -->
    <div
      class="bg-white rounded-3xl border border-slate-200 shadow-soft-xl overflow-x-auto custom-scrollbar animate-in fade-in slide-in-from-bottom-4 duration-700 delay-200">
      <table class="w-full border-separate border-spacing-0">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/50">
            <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
              S.No
            </th>
            <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
              Invoice No
            </th>
            <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
              Case ID
            </th>
            <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
              Patient
            </th>
            <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
              Date
            </th>
            <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
              Amount
            </th>
            <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-3 py-4 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider">
              Action
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse">
            <td colspan="7" class="px-3 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-full"></div>
            </td>
          </tr>
          <tr v-else-if="invoices.length === 0">
            <td colspan="8" class="px-3 py-12 text-center text-slate-400 font-medium italic">
              No invoices found.
            </td>
          </tr>
          <tr v-for="(invoice, index) in invoices" :key="invoice.id"
            class="group hover:bg-primary/5 transition-colors duration-300">
            <td class="px-3 py-4 text-sm text-slate-500">
              {{ index + 1 }}
            </td>
            <td class="px-3 py-4">
              <span
                class="text-sm font-semibold text-slate-900 group-hover:text-primary transition-colors cursor-pointer"
                @click="viewInvoice(invoice.id)">
                {{ invoice.invoice_no }}
              </span>
            </td>
            <td class="px-3 py-4">
              <div v-if="invoice.case_report?.case_id" class="flex flex-col">
                <span
                  class="w-fit px-2 py-0.5 rounded-lg bg-primary/10 text-primary text-[11px] font-bold cursor-pointer hover:bg-primary/20 transition-colors mb-1"
                  @click="$router.push(`/case-reports/${invoice.case_report_id}/edit`)">
                  {{ invoice.case_report.case_id }}
                </span>
                <span class="text-[10px] text-slate-500 font-medium ml-1">
                  {{ invoice.branch?.name }}
                </span>
              </div>
              <span v-else class="text-slate-400 text-xs">—</span>
            </td>
            <td class="px-3 py-4">
              <span class="text-sm font-semibold text-slate-900">{{
                invoice.patient?.name
              }}</span>
            </td>
            <td class="px-3 py-4 text-sm text-slate-600 font-medium">
              {{ formatDate(invoice.invoice_date) }}
            </td>
            <td class="px-3 py-4">
              <span class="text-sm font-bold text-slate-900">₹{{ parseFloat(invoice.total_amount).toFixed(2) }}</span>
            </td>
            <td class="px-3 py-4">
              <StatusBadge :status="invoice.status" type="invoice" />
            </td>
            <td class="px-3 py-4 text-right">
              <TableActions :item="invoice" :permissions="modulePermissions" :show-edit="true" :show-delete="false"
                :show-print="true" edit-title="Edit Invoice" view-title="View Details" print-title="Print Invoice"
                @view="viewInvoice(invoice.id)" @edit="editInvoice(invoice)" @print="printInvoice(invoice.id)" />
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <Pagination v-if="pagination" :pagination="pagination" @page-change="fetchInvoices" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { Search as SearchIcon, Filter as FilterIcon } from "lucide-vue-next";
import Pagination from "../../components/ui/Pagination.vue";
import TableActions from "../../components/ui/TableActions.vue";
import StatusBadge from "../../components/ui/StatusBadge.vue";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../../components/ui/select";
import { usePermissions } from "../../composables/usePermissions";
import { useAuth } from "../../composables/useAuth";
import { useBranchContext } from "../../composables/useBranchContext";
import { debounce } from "lodash";

const router = useRouter();
const { getModulePermissions } = usePermissions();
const { selectedBranchId } = useBranchContext();
const modulePermissions = getModulePermissions("invoices");
const invoices = ref([]);
const loading = ref(true);
const search = ref("");
const statusFilter = ref("");
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 15,
});

const fetchInvoices = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/invoices", {
      params: {
        page,
        search: search.value,
        status: statusFilter.value === "all" ? "" : statusFilter.value,
        branch_id: selectedBranchId.value,
      },
    });
    if (response.data.success) {
      invoices.value = response.data.data.data;
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        total: response.data.data.total,
        per_page: response.data.data.per_page,
      };
    } else {
      // Fallback for old style if success key is missing
      invoices.value = response.data.data;
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        total: response.data.total,
        per_page: response.data.per_page,
      };
    }
  } catch (error) {
    console.error("Failed to fetch invoices", error);
  } finally {
    loading.value = false;
  }
};

const debouncedFetch = debounce(() => fetchInvoices(1), 300);

const formatDate = (date) => {
  if (!date) return "N/A";
  return new Date(date).toLocaleDateString();
};

const viewInvoice = (id) => {
  router.push(`/invoices/${id}`);
};

const editInvoice = (invoice) => {
  if (invoice.case_report_id) {
    router.push(`/invoices/${invoice.case_report_id}/edit`);
  }
};

const printInvoice = (id) => {
  window.open(`/api/v1/print/invoice/${id}`, "_blank");
};

watch(selectedBranchId, (newId) => {
  if (newId === "all") {
    window.location.reload();
  } else {
    fetchInvoices(1);
  }
});

onMounted(() => {
  fetchInvoices();
});
</script>
