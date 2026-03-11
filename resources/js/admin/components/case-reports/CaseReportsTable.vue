<template>
  <div class="overflow-x-auto custom-scrollbar">
    <table class="w-full border-separate border-spacing-0">
      <thead>
        <tr class="border-b border-slate-200 bg-slate-50/50">
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
            S.No
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
            Case Id
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
            Patient
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
            Branch
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
            Total Amount
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
            Due Amount
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
            Payment Status
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
            Scanning Date
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
            Check In
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
            Check-out
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
            Expires On
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
            Modified By
          </th>
          <th
            class="px-4 py-4 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
            Action
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <!-- Skeleton Loading State -->
        <template v-if="loading">
          <tr v-for="i in 5" :key="i" class="animate-pulse">
            <td class="px-3 py-3">
              <div class="h-4 bg-slate-100 rounded-md w-8"></div>
            </td>
            <td class="px-3 py-3">
              <div class="h-4 bg-slate-100 rounded-md w-32"></div>
            </td>
            <td class="px-3 py-3">
              <div class="h-4 bg-slate-100 rounded-md w-28"></div>
            </td>
            <td class="px-3 py-3">
              <div class="h-4 bg-slate-100 rounded-md w-24"></div>
            </td>
            <td class="px-3 py-3">
              <div class="h-4 bg-slate-100 rounded-md w-20"></div>
            </td>
            <td class="px-3 py-3">
              <div class="h-4 bg-slate-100 rounded-md w-20"></div>
            </td>
            <td class="px-3 py-3">
              <div class="h-6 bg-slate-100 rounded-full w-16"></div>
            </td>
            <td class="px-3 py-3">
              <div class="h-4 bg-slate-100 rounded-md w-24"></div>
            </td>
            <td class="px-3 py-3">
              <div class="h-4 bg-slate-100 rounded-md w-16"></div>
            </td>
            <td class="px-3 py-3">
              <div class="h-4 bg-slate-100 rounded-md w-16"></div>
            </td>
            <td class="px-3 py-3">
              <div class="h-4 bg-slate-100 rounded-md w-24"></div>
            </td>
            <td class="px-3 py-3">
              <div class="h-4 bg-slate-100 rounded-md w-24"></div>
            </td>
            <td class="px-3 py-3 text-right">
              <div class="h-8 bg-slate-100 rounded-lg w-28 ml-auto"></div>
            </td>
          </tr>
        </template>

        <template v-else>
          <tr v-for="(report, index) in reports" :key="report.id"
            class="group hover:bg-primary/5 transition-colors duration-300">
            <td class="px-3 py-3 text-sm text-slate-500 whitespace-nowrap">{{ startIndex + index }}</td>
            <td class="px-3 py-3 whitespace-nowrap">
              <div class="flex flex-col">
                <span class="text-sm font-semibold text-slate-900 leading-none">{{ report.case_id }}</span>
              </div>
            </td>
            <td class="px-3 py-3 whitespace-nowrap">
              <div class="flex flex-col">
                <span class="text-sm font-medium text-slate-900">{{ report.patient?.name || "N/A" }}</span>
              </div>
            </td>
            <td class="px-3 py-3 whitespace-nowrap">
              <span class="text-sm font-medium text-slate-900">{{ report.branch?.name || "N/A" }}</span>
            </td>
            <td class="px-3 py-3 text-sm font-semibold text-slate-900 whitespace-nowrap">
              {{ report.invoice ? `₹${report.invoice.total_amount}` : "—" }}
            </td>
            <td class="px-3 py-3 text-sm font-semibold text-rose-600 whitespace-nowrap">
              {{
                report.invoice
                  ? `₹${(report.invoice.total_amount - (report.invoice.paid_amount || 0)).toFixed(2)}`
                  : "—"
              }}
            </td>
            <td class="px-3 py-3 text-sm whitespace-nowrap">
              <StatusBadge v-if="report.invoice" :status="report.invoice.status" type="invoice" />
              <span v-else class="text-slate-400 text-xs">—</span>
            </td>
            <td class="px-3 py-3 text-xs text-slate-600 whitespace-nowrap">
              {{ report.scanning_date ? formatDate(report.scanning_date) : "—" }}
            </td>
            <td class="px-3 py-3 text-[11px] text-slate-500 font-medium whitespace-nowrap">
              {{ report.check_in || "—" }}
            </td>
            <td class="px-3 py-3 whitespace-nowrap">
              <button @click="$emit('open-check-out', report)"
                class="px-2 py-1 rounded text-[11px] font-bold transition-all whitespace-nowrap"
                :class="report.check_out ? 'text-primary bg-primary/5 hover:bg-primary/10' : 'text-slate-400 bg-slate-50 hover:bg-slate-100/80'"
                :disabled="!report.check_in">
                {{ report.check_out || "Set Time" }}
              </button>
            </td>
            <td class="px-3 py-3 text-xs text-slate-600 whitespace-nowrap">
              {{ report.expires_at ? formatDate(report.expires_at) : "—" }}
            </td>
            <td class="px-3 py-3 whitespace-nowrap">
              <div class="flex flex-col">
                <span class="text-[11px] font-medium text-slate-700 leading-tight">
                  {{ report.modified_by_user?.name || "System" }}
                </span>
                <span v-if="report.updated_at" class="text-[10px] text-slate-400 font-medium mt-0.5">
                  {{ formatDateTime(report.updated_at) }}
                </span>
              </div>
            </td>
            <td class="px-3 py-4 text-right whitespace-nowrap">
              <TableActions :item="report" :permissions="permissions" :show-whatsapp="(report.status || '').toLowerCase() === 'available'
                " view-title="View Info" edit-title="Edit Case Report" delete-title="Delete Case Report"
                @view="$emit('view-info', $event)" @edit="router.push(`/case-reports/${$event.id}/edit`)"
                @delete="$emit('delete', $event)" @whatsapp="$emit('send-whatsapp', $event)" />
            </td>
          </tr>
        </template>
      </tbody>
    </table>

    <div v-if="!loading && reports.length === 0" class="text-center py-20 animate-in fade-in duration-500">
      <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 mb-4">
        <FileTextIcon class="h-6 w-6 text-slate-400" />
      </div>
      <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
        No case reports found.
      </p>
    </div>
  </div>
</template>

<script setup>
import {
  FileText as FileTextIcon,
  Circle as CircleIcon,
  MapPin as MapPinIcon,
} from "lucide-vue-next";
import { useRouter } from "vue-router";
import { formatDate, formatDateTime } from "../../utils/format";
import TableActions from "../ui/TableActions.vue";
import StatusBadge from "../ui/StatusBadge.vue";

const props = defineProps({
  reports: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  permissions: {
    type: Object,
    required: true,
  },
  startIndex: {
    type: Number,
    default: 1,
  },
});

const router = useRouter();

defineEmits(["view-info", "delete", "send-whatsapp", "open-check-out"]);


function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}
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
