<template>
  <div class="overflow-x-auto custom-scrollbar">
    <table class="w-full border-separate border-spacing-0">
      <thead>
        <tr class="border-b border-slate-200 bg-slate-50/50">
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            S.No
          </th>
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            SRF No
          </th>
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            Patient
          </th>
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            Referer
          </th>
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            Link Status
          </th>
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            Payment Status
          </th>
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            Scanning Date
          </th>
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            Check In
          </th>
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            Check-out
          </th>
          <th class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
            Expires On
          </th>
          <th class="px-4 py-4 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider">
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
              <div class="h-4 bg-slate-100 rounded-md w-28"></div>
            </td>
            <td class="px-3 py-3">
              <div class="h-6 bg-slate-100 rounded-full w-20"></div>
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
            <td class="px-3 py-3 text-right">
              <div class="h-8 bg-slate-100 rounded-lg w-28 ml-auto"></div>
            </td>
          </tr>
        </template>

        <template v-else>
          <tr v-for="(report, index) in reports" :key="report.id"
            class="group hover:bg-primary/5 transition-colors duration-300">
            <td class="px-3 py-3 text-sm text-slate-500">{{ startIndex + index }}</td>
            <td class="px-3 py-3">
              <div class="flex flex-col">
                <span class="text-sm font-semibold text-slate-900 leading-none mb-1">{{ report.case_id }}</span>
                <span class="text-[10px] text-slate-500 font-medium">{{ report.branch?.name }}</span>
              </div>
            </td>
            <td class="px-3 py-3">
              <div class="flex flex-col">
                <span class="text-sm font-medium text-slate-900">{{ report.patient?.name || "N/A" }}</span>
                <span v-if="report.patient?.mobile_no" class="text-[10px] text-slate-400 font-medium">
                  {{ report.patient.mobile_no }}
                </span>
              </div>
            </td>
            <td class="px-3 py-3">
              <div class="flex flex-col">
                <span class="text-sm font-medium text-slate-900">{{ report.referer?.name || "N/A" }}</span>
                <span v-if="report.referer?.mobile_no" class="text-[10px] text-slate-400 font-medium">
                  {{ report.referer.mobile_no }}
                </span>
              </div>
            </td>
            <td class="px-3 py-3 text-sm">
              <StatusBadge :status="report.status || 'pending'" type="case" />
            </td>
            <td class="px-3 py-3 text-sm">
              <StatusBadge v-if="report.invoice" :status="report.invoice.status" type="invoice" />
              <span v-else class="text-slate-400 text-xs">—</span>
            </td>
            <td class="px-3 py-3 text-xs text-slate-600">
              {{ report.scanning_date ? formatDate(report.scanning_date) : "—" }}
            </td>
            <td class="px-3 py-3 text-[11px] text-slate-500 font-medium">
              {{ report.check_in || "—" }}
            </td>
            <td class="px-3 py-3">
              <button @click="$emit('open-check-out', report)"
                class="px-2 py-1 rounded text-[11px] font-bold transition-all"
                :class="report.check_out ? 'text-primary bg-primary/5 hover:bg-primary/10' : 'text-slate-400 bg-slate-50 hover:bg-slate-100/80'"
                :disabled="!report.check_in">
                {{ report.check_out || "Set Time" }}
              </button>
            </td>
            <td class="px-3 py-3 text-xs text-slate-600">
              {{ report.expires_at ? formatDate(report.expires_at) : "—" }}
            </td>
            <td class="px-3 py-4 text-right">
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
import { formatDate } from "../../utils/format";
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
