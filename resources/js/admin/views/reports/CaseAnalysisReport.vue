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
            Case Analysis Report
          </h1>
          <p class="text-sm text-slate-500 mt-1">Detailed analysis of case registration and status.</p>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm no-print">
      <AdvancedDateFilter v-model="filters" @change="onDateFilterChange" />
    </div>

    <!-- Dynamic Summary Cards -->
    <div class="space-y-4">
      <div class="flex items-center justify-between mb-1">
        <h2 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Filter by Scan Type</h2>
      </div>

      <!-- Multi-section Grid - Compact -->
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-3">
        <!-- "All" Reset Card -->
        <SummaryCard label="All Scans" :value="stats.scan_type_stats.reduce((s, t) => s + t.count, 0)"
          :active="filters.scan_type_id === 'all'" orientation="vertical"
          @click="filters.scan_type_id = 'all'; fetchOrderStats(1)" />

        <!-- Dynamic Scan Type Cards -->
        <SummaryCard v-for="stat in stats.scan_type_stats" :key="stat.id" :label="stat.name" :value="stat.count"
          :active="filters.scan_type_id === stat.id" orientation="vertical"
          active-border-class="border-indigo-600 text-indigo-600" active-icon-color-class="text-indigo-600"
          active-label-color-class="text-indigo-600" @click="filters.scan_type_id = stat.id; fetchOrderStats(1)" />
      </div>
    </div>

    <!-- Report Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
      <!-- Table Header with Search and Export -->
      <div
        class="p-6 border-b border-slate-100 bg-slate-50/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="h-8 w-1 bg-primary rounded-full"></div>
          <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
            Case Reports <span class="text-slate-400 ml-1">({{ pagination?.total || 0 }})</span>
          </h3>
        </div>

        <div class="flex items-center gap-4 flex-1 justify-end">
          <!-- Global Search In Header -->
          <div class="relative w-full md:w-64 group no-print">
            <SearchIcon
              class="absolute left-3.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400 group-focus-within:text-primary transition-colors" />
            <input v-model="filters.search" type="text" placeholder="Search across all fields..."
              class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm group-hover:border-slate-300" />
            <button v-if="filters.search" @click="filters.search = ''"
              class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-500 transition-colors">
              <XIcon class="h-3.5 w-3.5" />
            </button>
          </div>

          <button @click="exportToCSV" :disabled="loading || reports.length === 0"
            class="bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs flex items-center gap-2 shadow-sm hover:opacity-90 transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed no-print">
            <DownloadIcon v-if="!loading" class="h-3.5 w-3.5" />
            <div v-else class="h-3.5 w-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
            Export
          </button>
        </div>
      </div>

      <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-100">
              <th class="px-4 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider w-16">
                Sl.No
              </th>
              <th class="px-4 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                Case ID
              </th>
              <th class="px-4 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                Scan Types
              </th>
              <th class="px-4 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                Patient
              </th>
              <th class="px-4 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                Mobile No
              </th>
              <th class="px-4 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                Referrer
              </th>
              <th class="px-4 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                Referrer Mobile
              </th>
              <th class="px-4 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                Scanning Date
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <template v-if="loading">
              <tr v-for="i in 5" :key="i" class="animate-pulse">
                <td v-for="j in 8" :key="j" class="px-4 py-4">
                  <div class="h-4 bg-slate-50 rounded-md w-full"></div>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr v-for="(report, index) in reports" :key="report.id"
                class="hover:bg-slate-50/80 transition-colors group">
                <td class="px-4 py-4 text-xs font-medium text-slate-400">
                  {{ (pagination?.from || 1) + index }}
                </td>
                <td class="px-4 py-4">
                  <div class="flex flex-col">
                    <span class="text-sm font-bold text-primary group-hover:underline cursor-pointer"
                      @click="$router.push(`/case-reports/${report.id}/edit`)">
                      {{ report.case_id }}
                    </span>
                    <span v-if="report.branch"
                      class="text-[9px] w-fit font-bold uppercase px-1.5 py-0.5 bg-slate-100 text-slate-500 rounded mt-0.5">
                      {{ report.branch.name }}
                    </span>
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex flex-wrap gap-1">
                    <span class="text-xs font-semibold text-slate-600">
                      {{report.items?.map(i => i.scan_type?.name).filter(Boolean).filter((v, i, a) => a.indexOf(v) ===
                        i).join(', ')}}
                    </span>
                  </div>
                </td>
                <td class="px-4 py-4">
                  <span class="text-sm font-bold text-slate-900 truncate block max-w-[150px]">
                    {{ report.patient?.name }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  <span class="text-xs text-slate-500 font-medium">
                    {{ report.patient?.whatsapp_no || report.patient?.mobile_no || 'N/A' }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  <span class="text-sm text-slate-600 font-medium">
                    {{ report.referer?.name }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  <span class="text-xs text-slate-500 font-medium">
                    {{ report.referer?.mobile_no || 'N/A' }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-1.5 text-xs text-slate-600 font-bold">
                    <CalendarIcon class="h-3 w-3 text-slate-400" />
                    {{ report.scanning_date ? new Date(report.scanning_date).toLocaleDateString('en-GB').replace(/\//g,
                      '-') :
                      'N/A' }}
                  </div>
                </td>
              </tr>
            </template>
            <tr v-if="!loading && reports.length === 0">
              <td colspan="8" class="px-6 py-20 text-center">
                <div class="flex flex-col items-center justify-center text-slate-400 italic">
                  <SearchIcon class="h-10 w-10 mb-2 opacity-20" />
                  <p>No order records found matching your criteria.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <Pagination v-if="pagination && pagination.total > 0" :pagination="pagination" @page-change="fetchOrderStats"
        class="no-print" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import {
  BarChart3 as BarChart3Icon,
  Download as DownloadIcon,
  Package as TotalIcon,
  CheckCircle2 as CompletedIcon,
  Clock as PendingIcon,
  Calendar as CalendarIcon,
  Filter as FilterIcon,
  Search as SearchIcon,
  X as XIcon,
  ChevronLeft as ChevronLeftIcon,
  ChevronRight as ChevronRightIcon,
  ArrowLeft as ArrowLeftIcon,
  MapPin as MapPinIcon,
  CheckCircle2 as CheckCircle2Icon,
} from "lucide-vue-next";
import { debounce } from "lodash";
import { formatDate } from "../../utils/format";
import Pagination from "../../components/ui/Pagination.vue";
import { useBranchContext } from "../../composables/useBranchContext";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../../components/ui/select";

const { selectedBranchId } = useBranchContext();
const reports = ref([]);
const loading = ref(true);
const pagination = ref(null);
const filters = ref({
  filter_type: "day",
  filter_option: "today",
  from_date: new Date().toISOString().split("T")[0],
  to_date: new Date().toISOString().split("T")[0],
  search: "",
  scan_type_id: "all",
});

import StatusBadge from "../../components/ui/StatusBadge.vue";
import AdvancedDateFilter from "../../components/reports/AdvancedDateFilter.vue";
import SummaryCard from "../../components/reports/SummaryCard.vue";


const stats = ref({
  total_cases: 0,
  scan_type_stats: [],
  branch_stats: [],
});

const currentDateTime = computed(() => {
  return new Date().toLocaleString("en-US", {
    dateStyle: "medium",
    timeStyle: "short",
  });
});

const cn = (...classes) => classes.filter(Boolean).join(" ");

const fetchOrderStats = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      limit: 10,
      page,
      filter_type: filters.value.filter_type,
      filter_option: filters.value.filter_option,
      start_date: filters.value.from_date,
      end_date: filters.value.to_date,
      search: filters.value.search,
      scan_types_fk_id: filters.value.scan_type_id,
      branch_id: selectedBranchId.value,
    };

    const response = await axios.get("/api/v1/reports/case-analysis", { params });

    if (response.data.success) {
      reports.value = response.data.data.data;
      pagination.value = response.data.data;

      if (response.data.report_stats) {
        stats.value = response.data.report_stats;
      }

      // No need to manually update label here as it's handled by component
    }
  } catch (err) {
    console.error("Failed to fetch order report data", err);
  } finally {
    loading.value = false;
  }
};


const getStatusClass = (status) => {
  switch (status) {
    case "available":
    case "completed":
      return "bg-emerald-50 text-emerald-600 border border-emerald-100";
    case "pending":
    case "draft":
      return "bg-amber-50 text-amber-600 border border-amber-100";
    case "cancelled":
      return "bg-rose-50 text-rose-600 border border-rose-100";
    default:
      return "bg-slate-50 text-slate-600 border border-slate-100";
  }
};

const debouncedSearch = debounce(() => {
  fetchOrderStats(1);
}, 500);

import { watch } from "vue";
watch(() => filters.value.search, () => {
  debouncedSearch();
});

watch(selectedBranchId, () => {
  fetchOrderStats(1);
});

/**
 * When the date filter changes, reset scan type to 'all' then fetch.
 */
const onDateFilterChange = () => {
  filters.value.scan_type_id = 'all';
  fetchOrderStats(1);
};

// Rely on AdvancedDateFilter's own onMounted emitChange to trigger the first fetch.
// Do NOT call fetchOrderStats() here to avoid a double-fetch on load.

const exportToCSV = async () => {
  loading.value = true;
  try {
    const params = {
      filter_type: filters.value.filter_type,
      filter_option: filters.value.filter_option,
      from_date: filters.value.from_date,
      to_date: filters.value.to_date,
      search: filters.value.search,
      scan_type_id: filters.value.scan_type_id,
      branch_id: selectedBranchId.value || 'all',
    };

    const response = await axios.get('/api/admin/v1/reports/case-analysis/export', {
      params,
      responseType: 'blob',
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Case_Analysis_Report_${new Date().toISOString().split('T')[0]}.xlsx`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (err) {
    console.error("Failed to export Excel report", err);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchOrderStats);
</script>

<style scoped>
/* No more print styles needed for now as per user request */
</style>
