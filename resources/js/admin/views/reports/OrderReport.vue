<template>
  <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Header -->
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm"
    >
      <div>
        <div class="flex items-center gap-3 mb-2">
          <div class="p-2 bg-primary/10 rounded-xl">
            <BarChart3Icon class="h-6 w-6 text-primary" />
          </div>
          <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
            Case Analysis Report
          </h1>
        </div>
      </div>
    </div>

    <!-- Filter Bar (Matching Screenshot) -->
    <div
      class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex flex-wrap items-center gap-4 no-print"
    >
      <div class="flex-1 min-w-[200px] relative">
        <label class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
          <CalendarIcon class="h-4 w-4" />
        </label>
        <input
          v-model="filters.from_date"
          type="date"
          class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all"
        />
      </div>
      <div class="flex-1 min-w-[200px] relative">
        <label class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
          <CalendarIcon class="h-4 w-4" />
        </label>
        <input
          v-model="filters.to_date"
          type="date"
          class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all"
        />
      </div>
      <button
        @click="fetchOrderStats"
        class="bg-primary text-white px-8 py-3 rounded-xl font-bold text-sm flex items-center gap-2 shadow-lg shadow-primary/20 hover:opacity-90 transition-all active:scale-95 shrink-0"
      >
        <FilterIcon class="h-4 w-4" />
        Filter
      </button>
      <button
        @click="exportToCSV"
        class="bg-slate-900 text-white px-8 py-3 rounded-xl font-bold text-sm flex items-center gap-2 shadow-lg shadow-slate-200 hover:bg-slate-800 transition-all active:scale-95 shrink-0"
      >
        <DownloadIcon class="h-4 w-4" />
        Export
      </button>
    </div>

    <!-- Stats Summary (The 3 things) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        v-for="stat in quickStats"
        :key="stat.label"
        class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex items-center gap-4 group hover:border-primary/50 transition-colors"
      >
        <div
          :class="
            cn(
              'p-4 rounded-2xl transition-transform group-hover:scale-110',
              stat.bg,
            )
          "
        >
          <component :is="stat.icon" :class="cn('h-7 w-7', stat.color)" />
        </div>
        <div>
          <p
            class="text-xs font-bold text-slate-400 uppercase tracking-[0.1em] leading-none mb-1"
          >
            {{ stat.label }}
          </p>
          <p class="text-3xl font-bold text-slate-900 mt-0.5">
            {{ stat.value }}
          </p>
        </div>
      </div>
    </div>

    <!-- Report Table -->
    <div
      class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden"
    >
      <div
        class="p-6 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between"
      >
        <h3
          class="text-sm font-bold text-slate-900 uppercase tracking-wider ml-2"
        >
          Detailed Order Log
        </h3>
        <div class="flex items-center gap-4">
          <!-- Filters -->
        </div>
      </div>

      <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-500">
              <th
                class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
              >
                S.No
              </th>
              <th
                class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
              >
                Case ID
              </th>
              <th
                class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
              >
                Patient
              </th>
              <th
                class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
              >
                Referrer
              </th>
              <th
                class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
              >
                Scanning Date
              </th>
              <th
                class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
              >
                Status
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
              <tr
                v-for="(report, index) in reports"
                :key="report.id"
                class="hover:bg-primary/5 transition-colors group"
              >
                <td class="px-3 py-4 text-sm text-slate-500">
                  {{ index + 1 }}
                </td>
                <td class="px-3 py-4">
                  <span class="text-sm text-primary font-medium">
                    {{ report.case_id }}
                  </span>
                </td>
                <td class="px-3 py-4">
                  <span class="text-sm font-semibold text-slate-900"
                    >{{ report.patient?.title?.title_name }}
                    {{ report.patient?.name }}</span
                  >
                </td>
                <td class="px-3 py-4">
                  <span class="text-sm text-slate-600">{{
                    report.referer?.name
                  }}</span>
                </td>
                <td class="px-3 py-4 text-xs text-slate-600">
                  {{ formatDate(report.created_at) }}
                </td>
                <td class="px-3 py-4">
                  <span
                    :class="
                      cn(
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase',
                        getStatusClass(report.status),
                      )
                    "
                  >
                    {{ report.status }}
                  </span>
                </td>
              </tr>
            </template>
            <tr v-if="!loading && reports.length === 0">
              <td
                colspan="5"
                class="px-6 py-20 text-center text-slate-400 italic"
              >
                No order records found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination (Matching Screenshot Style) -->
      <div
        v-if="pagination && pagination.total > 0"
        class="p-6 border-t border-slate-100 flex items-center justify-between no-print"
      >
        <div class="text-sm font-bold text-slate-400 uppercase tracking-wider">
          Showing <span class="text-slate-900">{{ pagination.from }}</span> to
          <span class="text-slate-900">{{ pagination.to }}</span> of
          <span class="text-slate-900">{{ pagination.total }}</span> entries
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="fetchOrderStats(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
          >
            <ChevronLeftIcon class="h-4 w-4 text-slate-600" />
          </button>
          <div class="flex items-center gap-1">
            <button
              v-for="p in pagination.last_page"
              :key="p"
              @click="fetchOrderStats(p)"
              :class="
                cn(
                  'w-10 h-10 rounded-xl font-bold text-sm transition-all',
                  p === pagination.current_page
                    ? 'bg-primary text-white shadow-lg shadow-primary/20'
                    : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50',
                )
              "
            >
              {{ p }}
            </button>
          </div>
          <button
            @click="fetchOrderStats(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
          >
            <ChevronRightIcon class="h-4 w-4 text-slate-600" />
          </button>
        </div>
      </div>
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
  ChevronLeft as ChevronLeftIcon,
  ChevronRight as ChevronRightIcon,
} from "lucide-vue-next";
import { formatDate } from "../../utils/format";

const reports = ref([]);
const loading = ref(true);
const pagination = ref(null);
const filters = ref({
  from_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1)
    .toISOString()
    .split("T")[0],
  to_date: new Date().toISOString().split("T")[0],
});
const stats = ref({
  total: 0,
  completed: 0,
  pending: 0,
});

const currentDateTime = computed(() => {
  return new Date().toLocaleString("en-US", {
    dateStyle: "medium",
    timeStyle: "short",
  });
});

const quickStats = computed(() => [
  {
    label: "Total Orders",
    value: stats.value.total,
    icon: TotalIcon,
    bg: "bg-indigo-50",
    color: "text-indigo-500",
  },
  {
    label: "Completed",
    value: stats.value.completed,
    icon: CompletedIcon,
    bg: "bg-emerald-50",
    color: "text-emerald-500",
  },
  {
    label: "Pending",
    value: stats.value.pending,
    icon: PendingIcon,
    bg: "bg-amber-50",
    color: "text-amber-500",
  },
]);

const cn = (...classes) => classes.filter(Boolean).join(" ");

const fetchOrderStats = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/case-reports", {
      params: { limit: 10, page, ...filters.value },
    });
    if (response.data.success) {
      reports.value = response.data.data.data;
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        total: response.data.data.total,
        from: response.data.data.from,
        to: response.data.data.to,
      };

      if (response.data.report_stats) {
        stats.value.total = response.data.report_stats.total;
        stats.value.completed = response.data.report_stats.completed;
        stats.value.pending = response.data.report_stats.pending;
      }
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

const exportToCSV = () => {
  if (reports.value.length === 0) return;

  const headers = ["Case ID", "Patient Name", "Referer", "Date", "Status"];
  const rows = reports.value.map((r) => [
    r.case_id,
    r.patient?.name || "N/A",
    r.referer?.name || "N/A",
    formatDate(r.created_at),
    r.status,
  ]);

  const csvContent = [headers, ...rows].map((e) => e.join(",")).join("\n");
  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");
  const url = URL.createObjectURL(blob);
  link.setAttribute("href", url);
  link.setAttribute(
    "download",
    `Case_Report_${filters.value.from_date}_to_${filters.value.to_date}.csv`,
  );
  link.style.visibility = "hidden";
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

onMounted(fetchOrderStats);
</script>

<style scoped>
/* No more print styles needed for now as per user request */
</style>
