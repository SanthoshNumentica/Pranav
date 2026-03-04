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
            Profit & Loss Analysis
          </h1>
          <p class="text-sm text-slate-500 mt-1">Comprehensive financial performance and margin analysis.</p>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4 no-print">
      <AdvancedDateFilter v-model="filters" @change="fetchFinancialData" />
    </div>

    <!-- P&L Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <SummaryCard v-for="stat in quickStats" :key="stat.label" :label="stat.label" :value="stat.value"
        :icon="stat.icon" :icon-bg-class="stat.bg" :icon-color-class="stat.color" :clickable="false" />
    </div>

    <!-- Detailed Ledger -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
      <!-- Table Header -->
      <div
        class="p-6 border-b border-slate-100 bg-slate-50/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="h-8 w-1 bg-primary rounded-full"></div>
          <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
            Profit & Loss Records <span class="text-slate-400 ml-1">({{ pagination?.total || 0 }})</span>
          </h3>
        </div>

        <div class="flex items-center gap-4 flex-1 justify-end">
          <!-- Global Search In Header -->
          <div class="relative w-full md:w-64 group no-print">
            <SearchIcon
              class="absolute left-3.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400 group-focus-within:text-primary transition-colors" />
            <input v-model="filters.search" type="text" placeholder="Search transactions..."
              class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm group-hover:border-slate-300" />
            <button v-if="filters.search" @click="filters.search = ''"
              class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-500 transition-colors">
              <XIcon class="h-3.5 w-3.5" />
            </button>
          </div>

          <button @click="exportToCSV" :disabled="loading || ledger.length === 0"
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
                Reference
              </th>
              <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                Source
              </th>
              <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                Type
              </th>
              <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                Amount
              </th>
              <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                Date
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <template v-if="loading">
              <tr v-for="i in 5" :key="i" class="animate-pulse">
                <td v-for="j in 6" :key="j" class="px-6 py-4">
                  <div class="h-4 bg-slate-100 rounded-md"></div>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr v-for="(item, index) in ledger" :key="item.id" class="hover:bg-primary/5 transition-colors group">
                <td class="px-3 py-4 text-sm text-slate-500">
                  {{ (pagination?.from || 1) + index }}
                </td>
                <td class="px-3 py-4">
                  <span class="text-sm text-primary font-medium">
                    {{ item.ref }}
                  </span>
                </td>
                <td class="px-3 py-4">
                  <span class="text-sm font-semibold text-slate-900">{{
                    item.source
                  }}</span>
                </td>
                <td class="px-3 py-4">
                  <span class="text-sm font-semibold" :class="item.type === 'Income'
                    ? 'text-emerald-600'
                    : 'text-rose-600'
                    ">
                    {{ item.type === "Income" ? "+" : "-" }}₹{{ item.amount }}
                  </span>
                </td>
                <td class="px-3 py-4">
                  <StatusBadge :status="item.type" type="invoice" />
                </td>
                <td class="px-3 py-4 text-xs text-slate-600">
                  {{ formatDate(item.date) }}
                </td>
              </tr>
            </template>
            <tr v-if="!loading && ledger.length === 0">
              <td colspan="6" class="px-6 py-20 text-center text-slate-400 italic">
                No financial records found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <Pagination v-if="pagination && pagination.total > 0" :pagination="pagination" @page-change="fetchFinancialData"
        class="no-print" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import {
  TrendingUp as TrendingUpIcon,
  Download as DownloadIcon,
  Wallet as IncomeIcon,
  ArrowDownCircle as ExpenseIcon,
  BadgeDollarSign as ProfitIcon,
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
import AdvancedDateFilter from "../../components/reports/AdvancedDateFilter.vue";
import SummaryCard from "../../components/reports/SummaryCard.vue";

const loading = ref(true);
const ledger = ref([]);
const pagination = ref(null);
const filters = ref({
  filter_type: "day",
  filter_option: "today",
  from_date: new Date().toISOString().split("T")[0],
  to_date: new Date().toISOString().split("T")[0],
  search: "",
});
const stats = ref({
  total_income: 0,
  total_expenses: 0,
  net_profit: 0,
  profit_percentage: 0,
});

const currentDateTime = computed(() => {
  return new Date().toLocaleString("en-US", {
    dateStyle: "medium",
    timeStyle: "short",
  });
});

const quickStats = computed(() => [
  {
    label: "Total Revenue",
    value: "₹" + stats.value.total_income,
    icon: IncomeIcon,
    bg: "bg-emerald-50",
    color: "text-emerald-500",
  },
  {
    label: "Total Expenses",
    value: "₹" + stats.value.total_expenses,
    icon: ExpenseIcon,
    bg: "bg-rose-50",
    color: "text-rose-500",
  },
  {
    label: "Net Profit",
    value: "₹" + stats.value.net_profit,
    icon: ProfitIcon,
    bg: "bg-blue-50",
    color: "text-blue-500",
  },
  {
    label: "Profit Percentage",
    value: stats.value.profit_percentage.toFixed(2) + "%",
    icon: TrendingUpIcon,
    bg: "bg-amber-50",
    color: "text-amber-500",
  },
]);

const cn = (...classes) => classes.filter(Boolean).join(" ");

const fetchFinancialData = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      limit: 10,
      page,
      filter_type: filters.value.filter_type,
      filter_option: filters.value.filter_option,
      from_date: filters.value.from_date,
      to_date: filters.value.to_date,
      search: filters.value.search
    };
    const response = await axios.get("/api/v1/reports/profit-loss-analysis", {
      params,
    });

    if (response.data.success) {
      ledger.value = response.data.data.data.map(p => ({
        id: p.id,
        ref: p.transaction_id || "CASH",
        source: p.invoice?.patient?.name || "Walk-in",
        type: "Income",
        amount: p.amount,
        date: p.payment_date,
      }));

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
        stats.value = response.data.report_stats;
      }
    }
  } catch (err) {
    console.error("Failed to fetch financial data", err);
  } finally {
    loading.value = false;
  }
};

const exportToCSV = () => {
  if (ledger.value.length === 0) return;

  const headers = ["Reference", "Source", "Type", "Amount", "Date"];
  const rows = ledger.value.map((item) => [
    item.ref,
    item.source,
    item.type,
    item.amount,
    formatDate(item.date),
  ]);

  const csvContent = [headers, ...rows].map((e) => e.join(",")).join("\n");
  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");
  const url = URL.createObjectURL(blob);
  link.setAttribute("href", url);
  link.setAttribute(
    "download",
    `Profit_Loss_Report_${filters.value.from_date}_to_${filters.value.to_date}.csv`,
  );
  link.style.visibility = "hidden";
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const debouncedSearch = debounce(() => {
  fetchFinancialData(1);
}, 500);

import { watch } from "vue";
watch(() => filters.value.search, () => {
  debouncedSearch();
});

onMounted(fetchFinancialData);
</script>

<style scoped>
/* No more print styles for now */
</style>
