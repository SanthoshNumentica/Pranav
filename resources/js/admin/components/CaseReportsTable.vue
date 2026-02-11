<template>
  <div class="overflow-x-auto custom-scrollbar">
    <table class="w-full border-separate border-spacing-0">
      <thead>
        <tr class="border-b border-slate-200 bg-slate-50/50">
          <th
            class="px-4 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            S.No
          </th>
          <th
            class="px-4 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Scan Report ID
          </th>
          <th
            class="px-4 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Patient
          </th>
          <th
            class="px-4 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Mobile No
          </th>
          <th
            class="px-4 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Doctor
          </th>
          <th
            class="px-4 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Link Status
          </th>
          <th
            class="px-4 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Created At
          </th>
          <th
            class="px-4 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Expires On
          </th>
          <th
            class="px-4 py-4 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Action
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <!-- Skeleton Loading State -->
        <template v-if="loading">
          <tr v-for="i in 5" :key="i" class="animate-pulse">
            <td class="px-4 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-8"></div>
            </td>
            <td class="px-4 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-32"></div>
            </td>
            <td class="px-4 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-40"></div>
            </td>
            <td class="px-4 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-28"></div>
            </td>
            <td class="px-4 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-36"></div>
            </td>
            <td class="px-4 py-4">
              <div class="h-6 bg-slate-100 rounded-full w-20"></div>
            </td>
            <td class="px-4 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-24"></div>
            </td>
            <td class="px-4 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-24"></div>
            </td>
            <td class="px-4 py-4 text-right">
              <div class="h-8 bg-slate-100 rounded-lg w-28 ml-auto"></div>
            </td>
          </tr>
        </template>

        <template v-else>
          <tr
            v-for="(report, index) in reports"
            :key="report.id"
            class="group hover:bg-primary/5 transition-colors duration-300"
          >
            <td class="px-4 py-4 text-sm text-slate-500">
              {{ index + 1 }}
            </td>
            <td class="px-4 py-4 text-sm font-semibold text-slate-900">
              {{ report.case_id }}
            </td>
            <td class="px-4 py-4">
              <span class="text-sm font-medium text-slate-900">{{
                report.patient?.name || "N/A"
              }}</span>
            </td>
            <td class="px-4 py-4 text-sm text-slate-600">
              {{ report.patient?.mobile_no || "N/A" }}
            </td>
            <td class="px-4 py-4 text-sm text-slate-600">
              {{ report.doctor?.name || "N/A" }}
            </td>
            <td class="px-4 py-4">
              <span
                :class="
                  cn(
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase transition-colors duration-200',
                    statusStyles[(report.status || 'pending').toLowerCase()] ||
                      'bg-slate-100 text-slate-800',
                  )
                "
              >
                {{ report.status }}
              </span>
            </td>
            <td class="px-4 py-4 text-sm text-slate-500">
              {{ formatDate(report.created_at) }}
            </td>
            <td class="px-4 py-4 text-sm font-medium">
              <span v-if="report.expires_at" class="text-rose-500">
                {{ formatDate(report.expires_at) }}
              </span>
              <span v-else class="text-slate-400">---</span>
            </td>
            <td class="px-4 py-4 text-right">
              <div
                class="flex justify-end gap-1.5 transition-opacity duration-200"
              >
                <button
                  @click="$emit('view-info', report)"
                  class="flex h-8 w-8 items-center justify-center rounded-lg text-blue-500 hover:bg-blue-500/10 hover:text-blue-600 transition-all duration-200"
                  title="View Info"
                >
                  <Eye class="h-4 w-4" />
                </button>
                <button
                  @click="$emit('send-whatsapp', report)"
                  class="flex h-8 w-8 items-center justify-center rounded-lg text-emerald-500 hover:bg-emerald-500/10 hover:text-emerald-600 transition-all duration-200"
                  title="Send via WhatsApp"
                >
                  <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
                    <path
                      d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.438 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"
                    />
                  </svg>
                </button>
                <button
                  @click="router.push(`/case-reports/${report.id}/edit`)"
                  class="flex h-8 w-8 items-center justify-center rounded-lg text-emerald-500 hover:bg-emerald-500/10 hover:text-emerald-600 transition-all duration-200"
                  title="Edit Case"
                >
                  <Edit class="h-4 w-4" />
                </button>
                <button
                  v-if="report.status !== 'deleted'"
                  @click="$emit('delete', report)"
                  class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-500/10 hover:text-rose-600 transition-all duration-200"
                  title="Delete Case"
                >
                  <Trash2 class="h-4 w-4" />
                </button>
              </div>
            </td>
          </tr>
        </template>
      </tbody>
    </table>

    <div
      v-if="!loading && reports.length === 0"
      class="text-center py-20 animate-in fade-in duration-500"
    >
      <div
        class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 mb-4"
      >
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
  Eye,
  Edit,
  Trash2,
  FileText as FileTextIcon,
  MessageSquare,
} from "lucide-vue-next";
import { useRouter } from "vue-router";
import { formatDate } from "../utils/format";

const props = defineProps({
  reports: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const router = useRouter();

defineEmits(["view-info", "delete", "send-whatsapp"]);

const statusStyles = {
  pending: "bg-amber-500/10 text-amber-500 border-amber-500/20",
  available: "bg-emerald-500/10 text-emerald-500 border-emerald-500/20",
  expired: "bg-rose-500/10 text-rose-500 border-rose-500/20",
  deleted: "bg-slate-500/10 text-slate-500 border-slate-500/20",
};

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
