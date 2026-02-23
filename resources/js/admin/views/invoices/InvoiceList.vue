<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-500"
    >
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
          Invoices
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Manage and track billing for case reports.
        </p>
      </div>
    </div>

    <!-- Filters & Search -->
    <div
      class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm animate-in fade-in duration-700 delay-100"
    >
      <div
        class="flex flex-col md:flex-row md:items-center justify-between gap-4"
      >
        <div class="flex flex-wrap items-center gap-3 flex-1">
          <div class="relative w-full md:w-72 group">
            <SearchIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input
              v-model="search"
              type="text"
              placeholder="Search by invoice no or patient name..."
              class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              @input="debouncedFetch"
            />
          </div>

          <div class="relative w-full md:w-48">
            <Select
              v-model="statusFilter"
              @update:modelValue="() => fetchInvoices(1)"
            >
              <SelectTrigger class="w-full pl-10">
                <SelectValue placeholder="Status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Statuses</SelectItem>
                <SelectItem value="pending">Pending</SelectItem>
                <SelectItem value="paid">Paid</SelectItem>
                <SelectItem value="cancelled">Cancelled</SelectItem>
              </SelectContent>
            </Select>
            <FilterIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none group-focus-within:text-primary transition-colors"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Invoices Table -->
    <div
      class="bg-white rounded-3xl border border-slate-200 shadow-soft-xl overflow-x-auto custom-scrollbar animate-in fade-in slide-in-from-bottom-4 duration-700 delay-200"
    >
      <table class="w-full border-separate border-spacing-0">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/50">
            <th
              class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
            >
              S.No
            </th>
            <th
              class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
            >
              Invoice No
            </th>
            <th
              class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
            >
              Patient
            </th>
            <th
              class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
            >
              Date
            </th>
            <th
              class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
            >
              Amount
            </th>
            <th
              class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
            >
              Status
            </th>
            <th
              class="px-3 py-4 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider"
            >
              Action
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse">
            <td colspan="7" class="px-3 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-full"></div>
            </td>
          </tr>
          <tr v-else-if="invoices.length === 0">
            <td
              colspan="7"
              class="px-3 py-12 text-center text-slate-400 font-medium italic"
            >
              No invoices found.
            </td>
          </tr>
          <tr
            v-for="(invoice, index) in invoices"
            :key="invoice.id"
            class="group hover:bg-primary/5 transition-colors duration-300"
          >
            <td class="px-3 py-4 text-sm text-slate-500">
              {{ index + 1 }}
            </td>
            <td class="px-3 py-4">
              <span
                class="text-sm font-semibold text-slate-900 group-hover:text-primary transition-colors cursor-pointer"
                @click="viewInvoice(invoice.id)"
              >
                {{ invoice.invoice_no }}
              </span>
            </td>
            <td class="px-3 py-4">
              <div class="flex flex-col">
                <span class="text-sm font-semibold text-slate-900">{{
                  invoice.patient?.name
                }}</span>
                <span class="text-[10px] text-slate-500 font-medium">{{
                  invoice.branch?.name
                }}</span>
              </div>
            </td>
            <td class="px-3 py-4 text-sm text-slate-600 font-medium">
              {{ formatDate(invoice.invoice_date) }}
            </td>
            <td class="px-3 py-4">
              <span class="text-sm font-bold text-slate-900"
                >₹{{ parseFloat(invoice.total_amount).toFixed(2) }}</span
              >
            </td>
            <td class="px-3 py-4">
              <span
                :class="[
                  'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                  invoice.status === 'paid'
                    ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20'
                    : invoice.status === 'pending'
                      ? 'bg-amber-500/10 text-amber-500 border border-amber-500/20'
                      : 'bg-slate-500/10 text-slate-500 border border-slate-500/20',
                ]"
              >
                {{ invoice.status }}
              </span>
            </td>
            <td class="px-3 py-4 text-right">
              <TableActions
                :item="invoice"
                :permissions="modulePermissions"
                :show-edit="false"
                :show-delete="false"
                :show-print="true"
                view-title="View Details"
                print-title="Print Invoice"
                @view="viewInvoice(invoice.id)"
                @print="printInvoice(invoice.id)"
              />
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <Pagination
        v-if="pagination"
        :pagination="pagination"
        @page-change="fetchInvoices"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { Search as SearchIcon, Filter as FilterIcon } from "lucide-vue-next";
import Pagination from "../../components/ui/Pagination.vue";
import TableActions from "../../components/ui/TableActions.vue";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../../components/ui/select";
import { usePermissions } from "../../composables/usePermissions";
import { useAuth } from "../../composables/useAuth";
import { debounce } from "lodash";

const router = useRouter();
const { getModulePermissions } = usePermissions();
const modulePermissions = getModulePermissions("invoices");
const invoices = ref([]);
const loading = ref(true);
const search = ref("");
const statusFilter = ref("");
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 15,
});

const fetchInvoices = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/invoices", {
      params: {
        page,
        search: search.value,
        status: statusFilter.value === "all" ? "" : statusFilter.value,
      },
    });
    invoices.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      total: response.data.total,
      per_page: response.data.per_page,
    };
  } catch (error) {
    console.error("Failed to fetch invoices", error);
  } finally {
    loading.value = false;
  }
};

const debouncedFetch = debounce(() => fetchInvoices(1), 300);

const formatDate = (date) => {
  if (!date) return "N/A";
  return new Date(date).toLocaleDateString();
};

const viewInvoice = (id) => {
  router.push(`/invoices/${id}`);
};

const printInvoice = (id) => {
  window.open(`/api/v1/print/invoice/${id}`, "_blank");
};

onMounted(() => {
  fetchInvoices();
});
</script>
