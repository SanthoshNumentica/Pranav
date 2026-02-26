<template>
  <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Header -->
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm"
    >
      <div>
        <div class="flex items-center gap-3 mb-2">
          <div class="p-2 bg-primary/10 rounded-xl">
            <CreditCardIcon class="h-6 w-6 text-primary" />
          </div>
          <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
            Payment Analysis Report
          </h1>
        </div>
        <p class="text-sm text-slate-500">
          Generated on {{ currentDateTime }} • Payment methods and collection history
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
        class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex items-center gap-4 group hover:border-primary/50 transition-colors"
      >
        <div :class="cn('p-4 rounded-2xl transition-transform group-hover:scale-110', stat.bg)">
          <component :is="stat.icon" :class="cn('h-7 w-7', stat.color)" />
        </div>
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.1em] leading-none mb-1">
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
          Recent Transactions
        </h3>
      </div>

      <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-500">
              <th class="px-3 py-4 text-left text-[11px] font-bold uppercase tracking-wider">
                S.No
              </th>
              <th class="px-3 py-4 text-left text-[11px] font-bold uppercase tracking-wider">
                Ref No
              </th>
              <th class="px-3 py-4 text-left text-[11px] font-bold uppercase tracking-wider">
                Patient / Invoice
              </th>
              <th class="px-3 py-4 text-left text-[11px] font-bold uppercase tracking-wider">
                Amount
              </th>
              <th class="px-3 py-4 text-left text-[11px] font-bold uppercase tracking-wider">
                Method
              </th>
              <th class="px-3 py-4 text-left text-[11px] font-bold uppercase tracking-wider">
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
              <tr
                v-for="(payment, index) in payments"
                :key="payment.id"
                class="hover:bg-primary/5 transition-colors group"
              >
                <td class="px-3 py-4 text-sm text-slate-500">
                  {{ index + 1 }}
                </td>
                <td class="px-3 py-4">
                  <span class="text-sm font-semibold text-slate-900">
                    {{ payment.transaction_id || 'N/A' }}
                  </span>
                </td>
                <td class="px-3 py-4">
                  <div class="flex flex-col">
                    <span class="text-sm font-semibold text-slate-900">{{ payment.invoice?.patient?.name }}</span>
                    <span class="text-xs text-slate-400 font-bold tracking-tight">INV: {{ payment.invoice?.invoice_no }}</span>
                  </div>
                </td>
                <td class="px-3 py-4">
                  <span class="text-sm font-bold text-emerald-600">+₹{{ payment.amount }}</span>
                </td>
                <td class="px-3 py-4">
                  <span class="text-[10px] font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full uppercase">
                    {{ payment.payment_method?.method_name || 'Cash' }}
                  </span>
                </td>
                <td class="px-3 py-4 text-sm text-slate-600">
                  {{ formatDate(payment.payment_date) }}
                </td>
              </tr>
            </template>
            <tr v-if="!loading && payments.length === 0">
              <td colspan="5" class="px-6 py-20 text-center text-slate-400 italic">
                No payment records found.
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
  CreditCard as CreditCardIcon,
  Printer as PrinterIcon,
  Banknote as CashIcon,
  PieChart as MethodIcon,
  History as HistoryIcon,
} from "lucide-vue-next";
import { formatDate } from "../../utils/format";

const payments = ref([]);
const loading = ref(true);
const stats = ref({
  total_collected: 0,
  transaction_count: 0,
  top_method: 'N/A'
});

const currentDateTime = computed(() => {
  return new Date().toLocaleString("en-US", {
    dateStyle: "medium",
    timeStyle: "short",
  });
});

const quickStats = computed(() => [
  {
    label: "Total Collected",
    value: "₹" + stats.value.total_collected,
    icon: CashIcon,
    bg: "bg-emerald-50",
    color: "text-emerald-500",
  },
  {
    label: "Transactions",
    value: stats.value.transaction_count,
    icon: HistoryIcon,
    bg: "bg-blue-50",
    color: "text-blue-500",
  },
  {
    label: "Top Method",
    value: stats.value.top_method,
    icon: MethodIcon,
    bg: "bg-amber-50",
    color: "text-amber-500",
  },
]);

const cn = (...classes) => classes.filter(Boolean).join(" ");

const fetchPaymentData = async () => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/payments", {
      params: { limit: 1000 }
    });
    if (response.data.success) {
      payments.value = response.data.data.data;
      stats.value.total_collected = payments.value.reduce((acc, p) => acc + parseFloat(p.amount), 0).toFixed(2);
      stats.value.transaction_count = payments.value.length;
      
      // Calculate top method
      const methods = payments.value.map(p => p.payment_method?.method_name || 'Cash');
      const counts = methods.reduce((acc, m) => {
        acc[m] = (acc[m] || 0) + 1;
        return acc;
      }, {});
      stats.value.top_method = Object.keys(counts).reduce((a, b) => counts[a] > counts[b] ? a : b, 'N/A');
    }
  } catch (err) {
    console.error("Failed to fetch payment report data", err);
  } finally {
    loading.value = false;
  }
};

const printReport = () => {
  window.print();
};

onMounted(fetchPaymentData);
</script>

<style scoped>
@media print {
  .flex-1, aside, header, .no-print { display: none !important; }
  .bg-slate-50 { background-color: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .space-y-6 { margin: 0 !important; padding: 20px !important; }
  table { width: 100% !important; border-collapse: collapse !important; }
  [class*="rounded-"] { border-radius: 0 !important; }
  [class*="shadow-"] { box-shadow: none !important; }
  .border { border: 1px solid #e2e8f0 !important; }
}
</style>
