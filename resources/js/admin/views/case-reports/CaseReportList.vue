<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Case Reports</h1>
        <p class="text-sm text-slate-500 mt-1">Manage diagnostic case reports.</p>
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
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm animate-in fade-in duration-700 delay-100">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3 flex-1">
          <!-- Search Inner -->
          <div class="relative w-full md:w-72 group">
            <SearchIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors" />
            <input 
              v-model="filters.search"
              type="text" 
              placeholder="Search Case ID, Patient..."
              class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              @input="debouncedFetch"
            >
          </div>

          <!-- Status Select -->
          <select 
            v-model="filters.status"
            class="bg-slate-50 border-slate-200 rounded-xl py-2 px-4 text-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-slate-600"
            @change="fetchReports"
          >
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="closed">Closed</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Main Table Container -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-soft-xl overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-700 delay-200">
      <div v-if="loading" class="p-20 flex flex-col items-center justify-center">
        <div class="h-10 w-10 border-4 border-primary/20 border-t-primary rounded-full animate-spin mb-4" />
        <p class="text-sm font-medium text-slate-400 animate-pulse">Fetching case reports...</p>
      </div>
      
      <CaseReportsTable 
        v-else
        :reports="reports" 
        @view-info="handleView"
      />

      <!-- Info Dialog -->
      <CaseReportInfoDialog 
        :is-open="isInfoOpen"
        :report="selectedReport"
        @close="isInfoOpen = false"
      />

      <!-- Pagination -->
      <div v-if="!loading && reports.length > 0" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
        <p class="text-xs text-slate-500 font-medium">
          Showing results
        </p>
        <!-- Pagination controls would go here -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { 
  Plus as PlusIcon, 
  Search as SearchIcon
} from 'lucide-vue-next';
import axios from 'axios';
import { debounce } from 'lodash';
import CaseReportsTable from '../../components/CaseReportsTable.vue';
import CaseReportInfoDialog from '../../components/CaseReportInfoDialog.vue';

const reports = ref([]);
const loading = ref(true);
const isInfoOpen = ref(false);
const selectedReport = ref(null);
const filters = reactive({
  search: '',
  status: ''
});

const fetchReports = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/v1/case-reports', { params: filters });
    if (response.data.success) {
        reports.value = response.data.data.data; // Assuming paginated response
    }
  } catch (error) {
    console.error("Failed to fetch reports", error);
  } finally {
    loading.value = false;
  }
};

const debouncedFetch = debounce(fetchReports, 300);

const handleView = (report) => {
  selectedReport.value = report;
  isInfoOpen.value = true;
};

onMounted(() => {
  fetchReports();
});
</script>
