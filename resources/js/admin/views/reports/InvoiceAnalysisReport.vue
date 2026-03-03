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
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap items-center gap-4 no-print">
      <div class="flex-1 min-w-[200px] relative">
        <label class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
          <CalendarIcon class="h-4 w-4" />
        </label>
        <input v-model="filters.from_date" type="date"
          class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
      </div>
      <div class="flex-1 min-w-[200px] relative">
        <label class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
          <CalendarIcon class="h-4 w-4" />
        </label>
        <input v-model="filters.to_date" type="date"
          class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" />
      </div>
      <button @click="fetchInvoiceData"
        class="bg-primary text-white px-4 py-2 rounded-xl font-semibold text-sm flex items-center gap-2 shadow-md shadow-primary/10 hover:bg-primary/90 transition-all active:scale-95 shrink-0">
        <FilterIcon class="h-4 w-4" />
        Filter
      </button>
      <button @click="exportToCSV"
        class="bg-slate-900 text-white px-4 py-2 rounded-xl font-semibold text-sm flex items-center gap-2 shadow-md shadow-slate-200 hover:bg-slate-800 transition-all active:scale-95 shrink-0">
        <DownloadIcon class="h-4 w-4" />
        Export
      </button>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="stat in quickStats" :key="stat.label"
        class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 group hover:border-primary/50 transition-colors">
        <div :class="cn(
          'p-4 rounded-2xl transition-transform group-hover:scale-110',
          stat.bg,
        )
          ">
          <component :is="stat.icon" :class="cn('h-7 w-7', stat.color)" />
        </div>
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.1em] leading-none mb-1">
            {{ stat.label }}
          </p>
          <p class="text-2xl font-bold text-slate-900 mt-0.5">
            {{ stat.value }}
          </p>
        </div>
      </div>
    </div>

    <!-- Report Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="p-6 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
        <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">
          Recent Invoices
        </p>
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
                Patient
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
                <td v-for="j in 8" :key="j" class="px-6 py-4">
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
                <td class="px-3 py-4">
                  <span class="text-sm font-semibold text-slate-900">{{
                    invoice.patient?.name
                  }}</span>
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
                <td class="px-3 py-4">
                  <span :class="cn(
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase',
                    invoice.status === 'fully_paid'
                      ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20'
                      : invoice.status === 'due'
                        ? 'bg-amber-500/10 text-amber-500 border border-amber-500/20'
                        : invoice.status === 'unpaid'
                          ? 'bg-rose-500/10 text-rose-500 border border-rose-500/20'
                          : 'bg-slate-500/10 text-slate-500 border border-slate-500/20',
                  )
                    ">
                    {{ invoice.status.replace('_', ' ') }}
                  </span>
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
} from "lucide-vue-next";
import { formatDate } from "../../utils/format";
import Pagination from "../../components/ui/Pagination.vue";

const invoices = ref([]);
const loading = ref(true);
const pagination = ref(null);
const filters = ref({
  from_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1)
    .toISOString()
    .split("T")[0],
  to_date: new Date().toISOString().split("T")[0],
});
const stats = ref({
  total_revenue: "0.00",
  paid_count: 0,
  pending_count: 0,
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
    value: "₹" + stats.value.total_revenue,
    icon: RevenueIcon,
    bg: "bg-violet-50",
    color: "text-violet-500",
  },
  {
    label: "Fully Paid",
    value: stats.value.paid_count,
    icon: PaidIcon,
    bg: "bg-emerald-50",
    color: "text-emerald-500",
  },
  {
    label: "Pending (Unpaid/Due)",
    value: stats.value.pending_count,
    icon: PendingIcon,
    bg: "bg-amber-50",
    color: "text-amber-500",
  },
]);

const cn = (...classes) => classes.filter(Boolean).join(" ");

const fetchInvoiceData = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/invoices", {
      params: { limit: 10, page, ...filters.value },
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
        stats.value.total_revenue = parseFloat(
          response.data.report_stats.total_revenue,
        ).toFixed(2);
        stats.value.paid_count = response.data.report_stats.paid_count;
        stats.value.pending_count = response.data.report_stats.pending_count;
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

const exportToCSV = () => {
  if (invoices.value.length === 0) return;

  const headers = [
    "Invoice No",
    "Patient",
    "Total Amount",
    "Paid Amount",
    "Balance",
    "Date",
    "Status",
  ];
  const rows = invoices.value.map((i) => {
    const total = parseFloat(i.total_amount);
    const paid = parseFloat(i.payments_sum_amount || 0);
    const balance = total - paid;
    return [
      i.invoice_no,
      i.patient?.name || "N/A",
      total.toFixed(2),
      paid.toFixed(2),
      balance.toFixed(2),
      formatDate(i.invoice_date),
      i.status,
    ];
  });

  const csvContent = [headers, ...rows].map((e) => e.join(",")).join("\n");
  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");
  const url = URL.createObjectURL(blob);
  link.setAttribute("href", url);
  link.setAttribute(
    "download",
    `Invoice_Report_${filters.value.from_date}_to_${filters.value.to_date}.csv`,
  );
  link.style.visibility = "hidden";
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

onMounted(fetchInvoiceData);
</script>

<style scoped>
/* No more print styles as per user request */
</style>
