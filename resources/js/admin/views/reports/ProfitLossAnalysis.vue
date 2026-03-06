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
            Referer Scan Analysis
          </h1>
          <p class="text-sm text-slate-500 mt-1">Scan type counts per referer based on selected period.</p>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4 no-print">
      <AdvancedDateFilter v-model="filters" @change="fetchMatrix" />
    </div>

    <!-- Dynamic Summary Cards -->
    <div class="space-y-4 no-print" v-if="!loading && rows.length > 0">
      <div class="flex items-center justify-between mb-1">
        <h2 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Scan Type Summary</h2>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-3">
        <!-- "All" Card -->
        <SummaryCard label="All Scans" :value="grandTotal" :active="true" :clickable="false" orientation="vertical" />

        <!-- Dynamic Scan Type Cards -->
        <SummaryCard v-for="st in scanTypes" :key="st.id" :label="st.name" :value="columnTotals[st.id] ?? 0"
          :clickable="false" orientation="vertical" />
      </div>
    </div>

    <!-- Matrix Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
      <!-- Table Header Bar -->
      <div
        class="p-5 border-b border-slate-100 bg-slate-50/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="h-8 w-1 bg-primary rounded-full"></div>
          <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
            Referer × Scan Type
            <span class="text-slate-400 ml-1 normal-case font-normal">({{ pagination?.total || 0 }} referers · {{
              grandTotal }} scans)</span>
          </h3>
        </div>

        <div class="flex items-center gap-3 flex-1 justify-end">
          <!-- Search -->
          <div class="relative w-full md:w-64 group no-print">
            <SearchIcon
              class="absolute left-3.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400 group-focus-within:text-primary transition-colors" />
            <input v-model="search" type="text" placeholder="Search referer..."
              class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm group-hover:border-slate-300" />
            <button v-if="search" @click="search = ''"
              class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-500 transition-colors">
              <XIcon class="h-3.5 w-3.5" />
            </button>
          </div>

          <!-- Export -->
          <button @click="exportToCSV" :disabled="loading || rows.length === 0"
            class="bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs flex items-center gap-2 shadow-sm hover:opacity-90 transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed no-print shrink-0">
            <DownloadIcon v-if="!loading" class="h-3.5 w-3.5" />
            <div v-else class="h-3.5 w-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
            Export
          </button>
        </div>
      </div>

      <div class="overflow-x-auto custom-scrollbar">
        <!-- Loading skeleton -->
        <template v-if="loading">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50/50 border-b border-slate-100 animate-pulse">
                <th class="px-4 py-4 w-48">
                  <div class="h-3 bg-slate-100 rounded w-24"></div>
                </th>
                <th v-for="i in 4" :key="i" class="px-4 py-4">
                  <div class="h-3 bg-slate-100 rounded w-16 mx-auto"></div>
                </th>
                <th class="px-4 py-4">
                  <div class="h-3 bg-slate-100 rounded w-12 mx-auto"></div>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="i in 6" :key="i" class="animate-pulse">
                <td class="px-4 py-3.5">
                  <div class="h-3.5 bg-slate-100 rounded w-32"></div>
                </td>
                <td v-for="j in 5" :key="j" class="px-4 py-3.5 text-center">
                  <div class="h-3.5 bg-slate-100 rounded w-8 mx-auto"></div>
                </td>
              </tr>
            </tbody>
          </table>
        </template>

        <!-- Empty state -->
        <template v-else-if="rows.length === 0">
          <div class="flex flex-col items-center justify-center py-24 text-slate-400">
            <SearchIcon class="h-10 w-10 mb-3 opacity-20" />
            <p class="text-sm italic">No scan data found for the selected period.</p>
          </div>
        </template>

        <!-- Pivot Matrix -->
        <template v-else>
          <table class="w-full text-left border-collapse min-w-max">
            <thead>
              <tr class="bg-slate-50/50 border-b border-slate-200">
                <!-- Sticky Referer Column -->
                <th
                  class="px-4 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider sticky left-0 bg-slate-50/80 backdrop-blur-sm z-10 min-w-[180px] border-r border-slate-200">
                  Referer Name
                </th>
                <!-- Dynamic Scan Type Columns -->
                <th v-for="st in scanTypes" :key="st.id"
                  class="px-4 py-4 text-center text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap min-w-[100px]">
                  {{ st.name }}
                </th>
                <!-- Total column -->
                <th
                  class="px-4 py-4 text-center text-[11px] font-bold text-primary uppercase tracking-wider min-w-[80px] border-l border-slate-200 bg-primary/5">
                  Total
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <!-- Data rows -->
              <tr v-for="row in rows" :key="row.referer_id" class="hover:bg-slate-50/80 transition-colors group">
                <td
                  class="px-4 py-3.5 sticky left-0 bg-white/90 backdrop-blur-sm z-10 border-r border-slate-100 group-hover:bg-slate-50/80">
                  <span class="text-sm font-semibold text-slate-900">{{ row.referer_name }}</span>
                </td>
                <td v-for="st in scanTypes" :key="st.id" class="px-4 py-3.5 text-center">
                  <span :class="[
                    'text-sm font-bold tabular-nums',
                    (row.counts[st.id] ?? 0) > 0 ? 'text-slate-800' : 'text-slate-300'
                  ]">
                    {{ row.counts[st.id] ?? 0 }}
                  </span>
                </td>
                <td class="px-4 py-3.5 text-center border-l border-slate-100 bg-primary/5">
                  <span class="text-sm font-black text-primary tabular-nums">{{ row.total }}</span>
                </td>
              </tr>

            </tbody>
          </table>
        </template>
      </div>

      <!-- Pagination -->
      <Pagination v-if="pagination && pagination.total > 0" :pagination="pagination" @page-change="fetchMatrix"
        class="no-print" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import {
  Download as DownloadIcon,
  ArrowLeft as ArrowLeftIcon,
  Search as SearchIcon,
  X as XIcon,
} from "lucide-vue-next";
import { debounce } from "lodash";
import Pagination from "../../components/ui/Pagination.vue";
import AdvancedDateFilter from "../../components/reports/AdvancedDateFilter.vue";
import SummaryCard from "../../components/reports/SummaryCard.vue";
import { useBranchContext } from "../../composables/useBranchContext";
import { watch } from "vue";

const { selectedBranchId } = useBranchContext();
const loading = ref(true);
const scanTypes = ref([]);   // [{id, name}]
const rows = ref([]);        // [{referer_id, referer_name, counts: {id: count}, total}]
const columnTotals = ref({}); // {scan_type_id: count}
const grandTotal = ref(0);
const search = ref("");
const pagination = ref(null);

const filters = ref({
  filter_type: "day",
  filter_option: "today",
  from_date: new Date().toISOString().split("T")[0],
  to_date: new Date().toISOString().split("T")[0],
});

// ── Search ──────────────────────────────
const debouncedSearch = debounce(() => {
  fetchMatrix(1);
}, 500);

watch(search, () => {
  debouncedSearch();
});

watch(selectedBranchId, () => {
  fetchMatrix(1);
});

// Remove computed properties (now handled by backend/pagination)

// ── Fetch ───────────────────────────────
const fetchMatrix = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      limit: 10,
      filter_type: filters.value.filter_type,
      filter_option: filters.value.filter_option,
      from_date: filters.value.from_date,
      to_date: filters.value.to_date,
      search: search.value,
      branch_id: selectedBranchId.value,
    };
    const { data } = await axios.get("/api/v1/reports/profit-loss-analysis", { params });
    if (data.success) {
      scanTypes.value = data.scan_types;
      rows.value = data.rows;
      columnTotals.value = data.column_totals;
      grandTotal.value = data.grand_total;
      pagination.value = data.data;
    }
  } catch (err) {
    console.error("Failed to fetch matrix data", err);
  } finally {
    loading.value = false;
  }
};

// ── Export ──────────────────────────────
const exportToCSV = async () => {
  loading.value = true;
  try {
    const params = {
      filter_type: filters.value.filter_type,
      filter_option: filters.value.filter_option,
      from_date: filters.value.from_date,
      to_date: filters.value.to_date,
      search: search.value,
      branch_id: selectedBranchId.value || 'all',
    };

    const response = await axios.get('/api/admin/v1/reports/profit-loss-analysis/export', {
      params,
      responseType: 'blob',
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Referer_Scan_Analysis_${new Date().toISOString().split('T')[0]}.xlsx`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (err) {
    console.error("Failed to export Excel report", err);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchMatrix);
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 20px;
}
</style>
