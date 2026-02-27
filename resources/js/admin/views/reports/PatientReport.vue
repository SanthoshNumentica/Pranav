<template>
  <div class="space-y-6">
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
            Patient Analysis Report
          </h1>
        </div>
        <p class="text-sm text-slate-500">
          Generated on {{ currentDateTime }} • Total Patients:
          {{ totalPatients }}
        </p>
      </div>

      <div class="flex items-center gap-3">
        <button
          @click="printReport"
          class="flex items-center gap-2 px-6 py-3 bg-slate-900 text-white rounded-2xl font-bold text-sm hover:bg-slate-800 transition-all active:scale-95 shadow-lg shadow-slate-200"
        >
          <PrinterIcon class="h-4 w-4" />
          Print Report
        </button>
      </div>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        v-for="stat in quickStats"
        :key="stat.label"
        class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex items-center gap-4"
      >
        <div :class="cn('p-3 rounded-2xl', stat.bg)">
          <component :is="stat.icon" :class="cn('h-6 w-6', stat.color)" />
        </div>
        <div>
          <p
            class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1"
          >
            {{ stat.label }}
          </p>
          <p class="text-2xl font-bold text-slate-900 mt-0.5">
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
          class="text-sm font-bold text-slate-400 uppercase tracking-widest ml-2"
        >
          Detailed Patient List
        </h3>
        <div class="flex items-center gap-4">
          <!-- Filters could go here -->
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
                Patient ID
              </th>
              <th
                class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
              >
                Full Name
              </th>
              <th
                class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
              >
                Contact
              </th>
              <th
                class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
              >
                Gender
              </th>
              <th
                class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
              >
                City
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
                <td v-for="j in 7" :key="j" class="px-6 py-4">
                  <div class="h-4 bg-slate-100 rounded-md"></div>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr
                v-for="(patient, index) in patients"
                :key="patient.id"
                class="hover:bg-primary/5 transition-colors group"
              >
                <td class="px-3 py-4 text-sm text-slate-500">
                  {{ index + 1 }}
                </td>
                <td class="px-3 py-4">
                  <span class="text-sm text-primary font-medium">{{
                    patient.patient_id
                  }}</span>
                </td>
                <td class="px-3 py-4">
                  <div class="flex flex-col">
                    <span class="text-sm font-semibold text-slate-900">{{
                      patient.name
                    }}</span>
                    <span
                      class="text-xs text-slate-400 uppercase font-medium mt-0.5"
                      >{{ patient.father_name }} (G)</span
                    >
                  </div>
                </td>
                <td class="px-3 py-4">
                  <div class="flex flex-col">
                    <span class="text-sm font-medium text-slate-600">{{
                      patient.mobile_no
                    }}</span>
                    <span class="text-xs text-slate-400">{{
                      patient.email_id
                    }}</span>
                  </div>
                </td>
                <td class="px-3 py-4">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-600 border border-slate-200"
                    >{{ patient.gender?.gender_name || "N/A" }}</span
                  >
                </td>
                <td class="px-3 py-4 text-sm text-slate-600">
                  {{ patient.city }}
                </td>
                <td class="px-3 py-4">
                  <span
                    :class="
                      cn(
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase',
                        patient.status === 'active'
                          ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20'
                          : 'bg-rose-500/10 text-rose-500 border border-rose-500/20',
                      )
                    "
                  >
                    {{ patient.status }}
                  </span>
                </td>
              </tr>
            </template>
            <tr v-if="!loading && patients.length === 0">
              <td
                colspan="6"
                class="px-6 py-20 text-center text-slate-400 italic"
              >
                No patient records found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import {
  BarChart3 as BarChart3Icon,
  Printer as PrinterIcon,
  Users as UsersIcon,
  UserCheck as ActiveIcon,
  MapPin as LocationIcon,
  Loader2 as Loader2Icon,
} from "lucide-vue-next";
import { formatDate } from "../../utils/format";

const patients = ref([]);
const loading = ref(true);
const totalPatients = ref(0);

const currentDateTime = computed(() => {
  return new Date().toLocaleString("en-US", {
    dateStyle: "medium",
    timeStyle: "short",
  });
});

const quickStats = computed(() => [
  {
    label: "Total Registrations",
    value: totalPatients.value,
    icon: UsersIcon,
    bg: "bg-blue-50",
    color: "text-blue-500",
  },
  {
    label: "Active Patients",
    value: patients.value.filter((p) => p.status === "active").length,
    icon: ActiveIcon,
    bg: "bg-emerald-50",
    color: "text-emerald-500",
  },
  {
    label: "Service Coverage",
    value: new Set(patients.value.map((p) => p.city)).size + " Cities",
    icon: LocationIcon,
    bg: "bg-amber-50",
    color: "text-amber-500",
  },
]);

const cn = (...classes) => classes.filter(Boolean).join(" ");

const fetchAllPatients = async () => {
  loading.value = true;
  try {
    // We use the masters endpoint or a large limit on index to get "all" for a report
    const response = await axios.get("/api/v1/patients", {
      params: { limit: 1000, status: "all" },
    });
    if (response.data.success) {
      patients.value = response.data.data.data;
      totalPatients.value = response.data.data.total;
    }
  } catch (err) {
    console.error("Failed to fetch patients for report", err);
  } finally {
    loading.value = false;
  }
};

const printReport = () => {
  window.print();
};

onMounted(fetchAllPatients);
</script>

<style scoped>
@media print {
  .flex-1,
  aside,
  header,
  .no-print {
    display: none !important;
  }
  .bg-slate-50 {
    background-color: #f8fafc !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  .space-y-6 {
    margin: 0 !important;
    padding: 20px !important;
  }
  table {
    width: 100% !important;
    border-collapse: collapse !important;
  }
  [class*="rounded-"] {
    border-radius: 0 !important;
  }
  [class*="shadow-"] {
    box-shadow: none !important;
  }
  .border {
    border: 1px solid #e2e8f0 !important;
  }
}
</style>
