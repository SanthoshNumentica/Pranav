<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-bold text-slate-900 tracking-tight">
        Reports Overview
      </h1>
      <p class="text-sm text-slate-500 mt-1">
        Access all clinic reports and analytics in one place.
      </p>
    </div>

    <!-- Reports Grid (3 in a row) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div
        v-for="report in reports"
        :key="report.title"
        class="group bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-primary/5 transition-all duration-500 flex flex-col items-center text-center relative overflow-hidden"
      >
        <!-- Background Decoration -->
        <div 
          class="absolute -right-4 -top-4 w-32 h-32 bg-slate-50 rounded-full group-hover:bg-primary/5 transition-colors duration-500"
        ></div>

        <!-- Icon -->
        <div
          :class="[
            'w-20 h-20 rounded-[2rem] flex items-center justify-center mb-6 shadow-lg transition-transform duration-500 group-hover:scale-110',
            report.bgClass
          ]"
        >
          <component :is="report.icon" class="h-10 w-10 text-white" />
        </div>

        <!-- Content -->
        <h2 class="text-2xl font-bold text-slate-900 mb-2 group-hover:text-primary transition-colors">
          {{ report.title }}
        </h2>
        <p class="text-slate-500 text-sm mb-8 max-w-[200px] leading-relaxed">
          {{ report.description }}
        </p>

        <!-- Action -->
        <router-link
          :to="report.url"
          class="w-full py-4 px-6 rounded-2xl bg-slate-50 text-slate-600 font-bold text-sm transition-all duration-300 hover:bg-primary hover:text-white flex items-center justify-center gap-2 group/btn"
        >
          <span>View Report</span>
          <ArrowRightIcon class="h-4 w-4 transition-transform duration-300 group-hover/btn:translate-x-1" />
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  Users as PatientsIcon, 
  FileText as InvoiceIcon, 
  CreditCard as PaymentIcon, 
  BarChart3 as OrderIcon,
  ArrowRight as ArrowRightIcon
} from "lucide-vue-next";

const reports = [
  {
    title: "Case Report",
    description: "Track all case registration, status distribution and trends.",
    url: "/reports/orders",
    icon: OrderIcon,
    bgClass: "bg-indigo-500 shadow-indigo-200"
  },
  {
    title: "Invoice Report",
    description: "View, filter and download invoice and revenue data.",
    url: "/reports/invoices",
    icon: InvoiceIcon,
    bgClass: "bg-emerald-500 shadow-emerald-200"
  },
  {
    title: "Profit & Loss",
    description: "Comprehensive financial performance and margin analysis.",
    url: "/reports/profit-loss",
    icon: PaymentIcon,
    bgClass: "bg-rose-500 shadow-rose-200"
  }
];
</script>

<style scoped>
.animate-in {
  animation-fill-mode: forwards;
}
</style>
