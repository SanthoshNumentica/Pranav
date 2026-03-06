<template>
  <div class="space-y-8">
    <!-- Page Header -->
    <div class="animate-in fade-in slide-in-from-top-4 duration-500">
      <h1 class="text-3xl font-bold text-slate-900 tracking-tight">
        Dashboard
      </h1>
      <p class="text-sm text-slate-500 mt-1">
        Scan Center overview and real-time performance metrics.
      </p>
    </div>

    <!-- Stats Overview -->
    <div
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 animate-in fade-in slide-in-from-bottom-4 duration-700 delay-100">
      <div v-for="stat in statsCards" :key="stat.label"
        class="p-6 rounded-3xl bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 group">
        <div class="flex items-center justify-between mb-4">
          <div :class="cn(
            'p-3 rounded-2xl transition-colors duration-300',
            stat.colorClass,
          )
            ">
            <component :is="stat.icon" class="h-6 w-6" />
          </div>
          <span v-if="stat.trend"
            class="text-[10px] font-bold text-emerald-500 bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-500/20">
            {{ stat.trend }}
          </span>
        </div>
        <div>
          <p class="text-[10px] uppercase tracking-[0.2em] font-bold text-slate-400 mb-1 leading-none">
            {{ stat.label }}
          </p>
          <div v-if="loading" class="h-8 w-24 bg-slate-100 animate-pulse rounded"></div>
          <p v-else class="text-3xl font-bold text-slate-900 tracking-tight">
            {{ stat.value }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Recent Activity -->
      <div
        class="lg:col-span-2 bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden flex flex-col">
        <div class="p-6 border-b border-slate-50 flex items-center justify-between">
          <h2 class="text-lg font-bold text-slate-900">Recent Case Reports</h2>
          <a href="/monitor" target="_blank"
            class="px-4 py-2 bg-primary hover:bg-primary-700 text-white rounded-xl text-[10px] font-bold uppercase tracking-wider transition-all shadow-md shadow-primary/10 active:scale-95 flex items-center gap-2">
            View on Monitor
          </a>
        </div>
        <div class="flex-1 overflow-x-auto custom-scrollbar max-h-[460px] overflow-y-auto relative">
          <table class="w-full text-left border-collapse">
            <thead class="sticky top-0 z-10 bg-white shadow-[0_1px_0_0_rgba(0,0,0,0.05)]">
              <tr class="bg-slate-50/50">
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                  Case ID
                </th>
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                  Patient
                </th>
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                  Referer
                </th>
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">
                  Scanning Date
                </th>
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">
                  Check-in
                </th>
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">
                  Check-out
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <template v-if="loading">
                <tr v-for="i in 5" :key="i" class="animate-pulse">
                  <td class="px-6 py-4">
                    <div class="h-4 bg-slate-50 rounded w-20"></div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="h-4 bg-slate-50 rounded w-32"></div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="h-4 bg-slate-50 rounded w-24"></div>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="h-4 bg-slate-50 rounded w-16 ml-auto"></div>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="h-4 bg-slate-50 rounded w-12 ml-auto"></div>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="h-4 bg-slate-50 rounded w-12 ml-auto"></div>
                  </td>
                </tr>
              </template>
              <template v-else>
                <tr v-for="report in recentReports" :key="report.id" class="group hover:bg-slate-50 transition-colors">
                  <td class="px-6 py-4">
                    <div class="flex flex-col">
                      <span class="text-xs font-bold text-slate-900 leading-none">#{{ report.case_id }}</span>
                      <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tight mt-1">{{
                        report.branch?.name }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex flex-col">
                      <span class="text-sm text-slate-600 font-medium">{{ report.patient?.name }}</span>
                      <span v-if="report.patient?.mobile_no" class="text-[10px] text-slate-400 font-medium italic">
                        {{ report.patient.mobile_no }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex flex-col">
                      <span class="text-sm text-slate-500">{{ report.referer?.name }}</span>
                      <span v-if="report.referer?.mobile_no" class="text-[10px] text-slate-400 font-medium italic">
                        {{ report.referer.mobile_no }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-xs text-slate-400 text-right">
                    {{ report.scanning_date ? new Date(report.scanning_date).toLocaleDateString('en-GB', {
                      day: '2-digit', month: 'short', year: 'numeric'
                    }) : 'N/A' }}
                  </td>
                  <td class="px-6 py-4 text-xs text-slate-400 text-right font-medium">
                    {{ report.check_in || '—' }}
                  </td>
                  <td class="px-6 py-4 text-right">
                    <button @click="confirmCheckOut(report)" :disabled="!report.check_in"
                      class="px-2 py-1 rounded text-[10px] font-bold transition-all"
                      :class="report.check_out ? 'text-primary bg-primary/5' : 'text-slate-300 bg-slate-50'">
                      {{ report.check_out || "Set" }}
                    </button>
                  </td>
                </tr>
              </template>
              <tr v-if="!loading && recentReports.length === 0">
                <td colspan="6"
                  class="px-6 py-12 text-center text-slate-400 font-bold uppercase tracking-widest text-xs">
                  No Cases Found Today
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Scan Distribution -->
      <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm p-6 flex flex-col">
        <h2 class="text-lg font-bold text-slate-900 mb-6">Scan Distribution</h2>
        <div v-if="loading" class="space-y-6">
          <div v-for="i in 4" :key="i" class="animate-pulse flex items-center gap-4">
            <div class="h-10 w-10 rounded-xl bg-slate-50"></div>
            <div class="flex-1 space-y-2">
              <div class="h-3 bg-slate-50 rounded w-24"></div>
              <div class="h-2 bg-slate-100 rounded"></div>
            </div>
          </div>
        </div>
        <div v-else class="space-y-6">
          <div v-for="(stat, index) in scanStats" :key="stat.name" class="group">
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-bold text-slate-700">{{
                stat.name
              }}</span>
              <span class="text-xs font-bold text-slate-400">{{ stat.total }} Scans</span>
            </div>
            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
              <div class="h-full bg-primary transition-all duration-1000 ease-out"
                :style="{ width: getPercentage(stat.total) + '%' }"></div>
            </div>
          </div>
          <div v-if="scanStats.length === 0" class="py-12 text-center text-slate-400 italic text-sm">
            No scan data available
          </div>
        </div>
      </div>
    </div>

    <!-- Check-out Modal -->
    <CheckOutModal :is-open="isCheckOutModalOpen" :report="reportForCheckOut" :loading="updatingCheckOut"
      @close="isCheckOutModalOpen = false" @confirm="handleCheckOut" />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, onUnmounted } from "vue";
import axios from "axios";
import {
  Users as PatientsIcon,
  User as RefererIcon,
  FolderTree as CaseIcon,
  Activity as PulseIcon,
  Calendar as TodayIcon,
  Clock as ClockIcon,
} from "lucide-vue-next";
import { formatDate } from "../../utils/format";
import CheckOutModal from "../../components/case-reports/CheckOutModal.vue";
import { useToast } from "../../composables/useToast";
import { useBranchContext } from "../../composables/useBranchContext";

const { addToast } = useToast();
const { selectedBranchId } = useBranchContext();

const loading = ref(true);
const dashboardData = ref({
  stats: {},
  recent_reports: [],
  scan_stats: [],
});

const statsCards = computed(() => [
  {
    label: "Total Patients",
    value: dashboardData.value.stats.total_patients || 0,
    icon: PatientsIcon,
    colorClass:
      "bg-blue-50 text-blue-500 group-hover:bg-blue-500 group-hover:text-white",
  },
  {
    label: "Total Referers",
    value: dashboardData.value.stats.total_referers || 0,
    icon: RefererIcon,
    colorClass:
      "bg-indigo-50 text-indigo-500 group-hover:bg-indigo-500 group-hover:text-white",
  },
  {
    label: "Total Cases",
    value: dashboardData.value.stats.total_case_reports || 0,
    icon: CaseIcon,
    colorClass:
      "bg-violet-50 text-violet-500 group-hover:bg-violet-500 group-hover:text-white",
  },
  {
    label: "Today's Cases",
    value: dashboardData.value.stats.today_case_reports || 0,
    icon: TodayIcon,
    trend: "Live",
    colorClass:
      "bg-rose-50 text-rose-500 group-hover:bg-rose-500 group-hover:text-white",
  },
]);

const recentReports = computed(() => dashboardData.value.recent_reports);
const scanStats = computed(() => dashboardData.value.scan_stats);

const maxScans = computed(() => {
  return Math.max(...scanStats.value.map((s) => s.total), 1);
});

const getPercentage = (total) => {
  return (total / maxScans.value) * 100;
};

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
      fetchDashboardData();
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

const fetchDashboardData = async (showLoading = true) => {
  if (showLoading) loading.value = true;
  try {
    const params = {};
    if (selectedBranchId.value && selectedBranchId.value !== 'all') {
      params.branch_id = selectedBranchId.value;
    }
    const response = await axios.get("/api/v1/dashboard", { params });
    if (response.data.success) {
      dashboardData.value = response.data.data;
    }
  } catch (err) {
    console.error("Failed to fetch dashboard data", err);
  } finally {
    if (showLoading) loading.value = false;
  }
};

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}

let refreshInterval = null;

onMounted(() => {
  fetchDashboardData();

  // Auto-refresh every 5 seconds
  refreshInterval = setInterval(() => {
    fetchDashboardData(false);
  }, 5000);
});

watch(selectedBranchId, () => {
  fetchDashboardData();
});


onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval);
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}

.animate-in {
  animation-fill-mode: forwards;
}
</style>
