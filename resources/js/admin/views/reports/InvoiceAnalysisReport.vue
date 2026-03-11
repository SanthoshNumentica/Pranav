<template>
  <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Header -->
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
      <div class="flex items-center gap-4">
        <button @click="$router.push('/reports')"
          class="group/back h-10 w-10 flex items-center justify-center rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition-all active:scale-95 shadow-sm"
          title="Back to Reports">
          <ArrowLeftIcon class="h-5 w-5 text-slate-500 group-hover/back:text-slate-900 transition-colors" />
        </button>
        <div>
          <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
            Invoice Analysis Report
          </h1>
          <p class="text-sm text-slate-500 mt-1">Detailed analysis of revenue and payment status.</p>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm no-print">
      <AdvancedDateFilter v-model="filters" @change="onDateFilterChange" />
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <SummaryCard v-for="stat in quickStats" :key="stat.key" :label="stat.label" :value="stat.value"
        :subtitle="stat.subtitle" :icon="stat.icon" :icon-bg-class="stat.bg" :icon-color-class="stat.color"
        :active="selectedCard === stat.key" :active-border-class="stat.activeBorder"
        :active-label-color-class="stat.activeColor" :active-value-color-class="'text-slate-900'"
        @click="selectCard(stat.key)" />
    </div>

    <!-- Report Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
      <!-- Table Header -->
      <div
        class="p-6 border-b border-slate-100 bg-slate-50/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="h-8 w-1 bg-primary rounded-full"></div>
          <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
            Invoices <span class="text-slate-400 ml-1">({{ pagination?.total || 0 }})</span>
          </h3>
        </div>

        <div class="flex items-center gap-4 flex-1 justify-end">
          <!-- Global Search In Header -->
          <div class="relative w-full md:w-64 group no-print">
            <SearchIcon
              class="absolute left-3.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400 group-focus-within:text-primary transition-colors" />
            <input v-model="filters.search" type="text" placeholder="Search invoices..."
              class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm group-hover:border-slate-300" />
            <button v-if="filters.search" @click="filters.search = ''"
              class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-500 transition-colors">
              <XIcon class="h-3.5 w-3.5" />
            </button>
          </div>

          <button @click="exportToCSV" :disabled="loading || invoices.length === 0"
            class="bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs flex items-center gap-2 shadow-sm hover:opacity-90 transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed no-print shrink-0">
            <DownloadIcon v-if="!loading" class="h-3.5 w-3.5" />
            <div v-else class="h-3.5 w-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
            Export
          </button>
        </div>
      </div>

      <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-500">
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
                Referer
              </th>
              <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                Total Amount
              </th>
              <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                Paid Amount
              </th>
              <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                Balance
              </th>
              <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                Date
              </th>
              <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                Status
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <template v-if="loading">
              <tr v-for="i in 5" :key="i" class="animate-pulse">
                <td v-for="j in 10" :key="j" class="px-6 py-4">
                  <div class="h-4 bg-slate-100 rounded-md"></div>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr v-for="(invoice, index) in invoices" :key="invoice.id"
                class="hover:bg-primary/5 transition-colors group">
                <td class="px-3 py-4 text-sm text-slate-500">
                  {{ (pagination?.from || 1) + index }}
                </td>
                <td class="px-3 py-4">
                  <span class="text-sm font-medium text-primary cursor-pointer hover:underline">
                    {{ invoice.invoice_no }}
                  </span>
                </td>
                <!-- Case ID + Branch -->
                <td class="px-3 py-4">
                  <div class="flex flex-col">
                    <span class="text-sm font-bold text-primary cursor-pointer hover:underline"
                      @click="$router.push(`/case-reports/${invoice.case_report_id}/edit`)">
                      {{ invoice.case_report?.case_id || '—' }}
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium mt-0.5">
                      {{ invoice.case_report?.branch?.name || '' }}
                    </span>
                  </div>
                </td>
                <!-- Patient + Mobile -->
                <td class="px-3 py-4">
                  <div class="flex flex-col">
                    <span class="text-sm font-semibold text-slate-900">{{ invoice.patient?.name || '—' }}</span>
                    <span class="text-[10px] text-slate-400 font-medium mt-0.5">
                      {{ invoice.patient?.mobile_no || invoice.patient?.whatsapp_no || '' }}
                    </span>
                  </div>
                </td>
                <!-- Referer + Mobile -->
                <td class="px-3 py-4">
                  <div class="flex flex-col">
                    <span class="text-sm font-semibold text-slate-700">{{ invoice.case_report?.referer?.name || '—'
                    }}</span>
                    <span class="text-[10px] text-slate-400 font-medium mt-0.5">
                      {{ invoice.case_report?.referer?.mobile_no || '' }}
                    </span>
                  </div>
                </td>
                <td class="px-3 py-4">
                  <span class="text-sm font-semibold text-slate-900">₹{{ parseFloat(invoice.total_amount).toFixed(2)
                  }}</span>
                </td>
                <td class="px-3 py-4">
                  <span class="text-sm font-semibold text-emerald-600">₹{{
                    parseFloat(invoice.payments_sum_amount || 0).toFixed(2)
                  }}</span>
                </td>
                <td class="px-3 py-4">
                  <span class="text-sm font-semibold text-rose-600">₹{{
                    (
                      parseFloat(invoice.total_amount) -
                      parseFloat(invoice.payments_sum_amount || 0)
                    ).toFixed(2)
                  }}</span>
                </td>
                <td class="px-3 py-4 text-xs text-slate-600">
                  {{ formatDate(invoice.invoice_date) }}
                </td>
                <td class="px-3 py-4 text-sm">
                  <StatusBadge :status="invoice.status" type="invoice" />
                </td>
              </tr>
            </template>
            <tr v-if="!loading && invoices.length === 0">
              <td colspan="5" class="px-6 py-20 text-center text-slate-400 italic">
                No invoice records found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <Pagination v-if="pagination && pagination.total > 0" :pagination="pagination" @page-change="fetchInvoiceData"
        class="no-print" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import {
  FileText as FileTextIcon,
  Download as DownloadIcon,
  TrendingUp as RevenueIcon,
  CreditCard as PaidIcon,
  AlertCircle as PendingIcon,
  Calendar as CalendarIcon,
  Filter as FilterIcon,
  ChevronLeft as ChevronLeftIcon,
  ChevronRight as ChevronRightIcon,
  ArrowLeft as ArrowLeftIcon,
  Search as SearchIcon,
  X as XIcon,
} from "lucide-vue-next";
import { debounce } from "lodash";
import { formatDate } from "../../utils/format";
import Pagination from "../../components/ui/Pagination.vue";
import StatusBadge from "../../components/ui/StatusBadge.vue";
import { useBranchContext } from "../../composables/useBranchContext";
import AdvancedDateFilter from "../../components/reports/AdvancedDateFilter.vue";
import SummaryCard from "../../components/reports/SummaryCard.vue";

const { selectedBranchId } = useBranchContext();
const invoices = ref([]);
const loading = ref(true);
const pagination = ref(null);
const selectedCard = ref('all');
const filters = ref({
  filter_type: "day",
  filter_option: "today",
  from_date: new Date().toISOString().split("T")[0],
  to_date: new Date().toISOString().split("T")[0],
  search: "",
});
const stats = ref({
  total_revenue_amount: 0,
  total_revenue_count: 0,
  fully_paid_amount: 0,
  fully_paid_count: 0,
  pending_amount: 0,
  pending_count_detail: 0,
});

const currentDateTime = computed(() => {
  return new Date().toLocaleString("en-US", {
    dateStyle: "medium",
    timeStyle: "short",
  });
});

const quickStats = computed(() => [
  {
    key: 'all',
    label: "Total Revenue",
    value: "₹" + parseFloat(stats.value.total_revenue_amount || 0).toFixed(2),
    subtitle: stats.value.total_revenue_count + " Invoice" + (stats.value.total_revenue_count !== 1 ? 's' : ''),
    icon: RevenueIcon,
    bg: "bg-violet-50",
    color: "text-violet-500",
    activeBorder: "border-violet-500",
    activeColor: "text-violet-600",
  },
  {
    key: 'fully_paid',
    label: "Fully Paid",
    value: "₹" + parseFloat(stats.value.fully_paid_amount || 0).toFixed(2),
    subtitle: stats.value.fully_paid_count + " Invoice" + (stats.value.fully_paid_count !== 1 ? 's' : ''),
    icon: PaidIcon,
    bg: "bg-emerald-50",
    color: "text-emerald-500",
    activeBorder: "border-emerald-500",
    activeColor: "text-emerald-600",
  },
  {
    key: 'pending',
    label: "Pending (Unpaid/Due)",
    value: "₹" + parseFloat(stats.value.pending_amount || 0).toFixed(2),
    subtitle: stats.value.pending_count_detail + " Invoice" + (stats.value.pending_count_detail !== 1 ? 's' : ''),
    icon: PendingIcon,
    bg: "bg-amber-50",
    color: "text-amber-500",
    activeBorder: "border-amber-500",
    activeColor: "text-amber-600",
  },
]);

const cn = (...classes) => classes.filter(Boolean).join(" ");

const fetchInvoiceData = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      limit: 10,
      page,
      filter_type: filters.value.filter_type,
      filter_option: filters.value.filter_option,
      from_date: filters.value.from_date,
      to_date: filters.value.to_date,
      search: filters.value.search,
      status_filter: selectedCard.value,
      branch_id: selectedBranchId.value,
    };
    const response = await axios.get("/api/v1/reports/invoice-analysis", {
      params,
    });
    if (response.data.success) {
      invoices.value = response.data.data.data;
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        total: response.data.data.total,
        from: response.data.data.from,
        to: response.data.data.to,
        prev_page_url: response.data.data.prev_page_url,
        next_page_url: response.data.data.next_page_url,
      };

      if (response.data.report_stats) {
        const s = response.data.report_stats;
        stats.value.total_revenue_amount = s.total_revenue_amount ?? s.total_revenue ?? 0;
        stats.value.total_revenue_count = s.total_revenue_count ?? 0;
        stats.value.fully_paid_amount = s.fully_paid_amount ?? 0;
        stats.value.fully_paid_count = s.fully_paid_count ?? s.paid_count ?? 0;
        stats.value.pending_amount = s.pending_amount ?? 0;
        stats.value.pending_count_detail = s.pending_count_detail ?? s.pending_count ?? 0;
      }
    }
  } catch (err) {
    console.error("Failed to fetch invoice report data", err);
  } finally {
    loading.value = false;
  }
};

const getStatusClass = (status) => {
  switch (status) {
    case "fully_paid":
      return "bg-emerald-50 text-emerald-600 border border-emerald-100";
    case "due":
      return "bg-amber-50 text-amber-600 border border-amber-100";
    case "unpaid":
      return "bg-rose-50 text-rose-600 border border-rose-100";
    default:
      return "bg-slate-50 text-slate-600 border border-slate-100";
  }
};

const exportToCSV = async () => {
  loading.value = true;
  try {
    const params = {
      filter_type: filters.value.filter_type,
      filter_option: filters.value.filter_option,
      from_date: filters.value.from_date,
      to_date: filters.value.to_date,
      search: filters.value.search,
      status_filter: selectedCard.value,
      branch_id: selectedBranchId.value || 'all',
    };

    const response = await axios.get('/api/admin/v1/reports/invoice-analysis/export', {
      params,
      responseType: 'blob',
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Invoice_Analysis_Report_${new Date().toISOString().split('T')[0]}.xlsx`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (err) {
    console.error("Failed to export Excel report", err);
  } finally {
    loading.value = false;
  }
};

const debouncedSearch = debounce(() => {
  fetchInvoiceData(1);
}, 500);

import { watch } from "vue";
watch(() => filters.value.search, () => {
  debouncedSearch();
});

watch(selectedBranchId, () => {
  fetchInvoiceData(1);
});

/**
 * Select a card and re-fetch the table data with the new status filter.
 */
const selectCard = (key) => {
  selectedCard.value = key;
  fetchInvoiceData(1);
};

/**
 * When date filter changes, reset card to 'all' and fetch.
 */
const onDateFilterChange = () => {
  selectedCard.value = 'all';
  fetchInvoiceData(1);
};

onMounted(fetchInvoiceData);
</script>

<style scoped>
/* No more print styles as per user request */
</style>
