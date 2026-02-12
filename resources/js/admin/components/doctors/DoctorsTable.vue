<template>
  <div class="overflow-x-auto custom-scrollbar">
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
            Name
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Doctor ID
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Mobile No
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Email ID
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Gender
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            DOB
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Status
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Created
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Modified
          </th>
          <th
            class="px-3 py-4 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Action
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <!-- Skeleton Loading State -->
        <template v-if="loading">
          <tr v-for="i in 5" :key="i" class="animate-pulse">
            <td class="px-3 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-8"></div>
            </td>
            <td class="px-3 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-32"></div>
            </td>
            <td class="px-3 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-24"></div>
            </td>
            <td class="px-3 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-28"></div>
            </td>
            <td class="px-3 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-36"></div>
            </td>
            <td class="px-3 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-16"></div>
            </td>
            <td class="px-3 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-24"></div>
            </td>
            <td class="px-3 py-4">
              <div class="h-6 bg-slate-100 rounded-full w-20"></div>
            </td>
            <td class="px-3 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-24"></div>
            </td>
            <td class="px-3 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-24"></div>
            </td>
            <td class="px-3 py-4 text-right">
              <div class="h-8 bg-slate-100 rounded-lg w-28 ml-auto"></div>
            </td>
          </tr>
        </template>

        <template v-else>
          <tr
            v-for="(doctor, index) in doctors"
            :key="doctor.id"
            class="group hover:bg-primary/5 transition-colors duration-300"
          >
            <td class="px-3 py-4 text-sm text-slate-500">
              {{ index + 1 }}
            </td>
            <td class="px-3 py-4">
              <span class="text-sm font-semibold text-slate-900">{{
                doctor.name
              }}</span>
            </td>
            <td class="px-3 py-4 text-sm text-primary font-medium">
              {{ doctor.doctor_id || "N/A" }}
            </td>
            <td class="px-3 py-4 text-sm text-slate-600">
              {{ doctor.mobile_no || "N/A" }}
            </td>
            <td class="px-3 py-4 text-sm text-slate-600 truncate max-w-[120px]">
              {{ doctor.email_id || "N/A" }}
            </td>
            <td class="px-3 py-4 text-sm text-slate-600">
              {{ doctor.gender?.gender_name || "N/A" }}
            </td>
            <td class="px-3 py-4 text-sm text-slate-600">
              {{ formatDate(doctor.dob) }}
            </td>
            <td class="px-3 py-4">
              <button
                type="button"
                @click="$emit('toggle-status', doctor)"
                :class="
                  cn(
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase transition-all duration-200 active:scale-95',
                    doctor.status === 'active'
                      ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20'
                      : 'bg-slate-500/10 text-slate-500 border border-slate-500/20',
                  )
                "
              >
                <CircleIcon
                  class="h-2 w-2 mr-1.5 fill-current"
                  v-if="doctor.status === 'active'"
                />
                {{ doctor.status }}
              </button>
            </td>
            <td class="px-3 py-4">
              <div class="flex flex-col">
                <span class="text-xs text-slate-600">{{
                  formatDate(doctor.created_at)
                }}</span>
                <span
                  v-if="doctor.added_by_user"
                  class="text-[10px] text-slate-400"
                >
                  by {{ doctor.added_by_user?.name || "Unknown" }}
                </span>
              </div>
            </td>
            <td class="px-3 py-4">
              <div class="flex flex-col">
                <span class="text-xs text-slate-600">{{
                  formatDate(doctor.updated_at)
                }}</span>
                <span
                  v-if="doctor.modified_by_user"
                  class="text-[10px] text-slate-400"
                >
                  by {{ doctor.modified_by_user?.name || "Unknown" }}
                </span>
                <span
                  v-else-if="doctor.added_by_user"
                  class="text-[10px] text-slate-400"
                >
                  by {{ doctor.added_by_user?.name || "Unknown" }}
                </span>
              </div>
            </td>
            <td class="px-3 py-4 text-right">
              <TableActions
                :item="doctor"
                :permissions="permissions"
                view-title="View Info"
                edit-title="Edit Doctor"
                delete-title="Delete Doctor"
                @view="$emit('view-info', $event)"
                @edit="$emit('edit', $event)"
                @delete="$emit('delete', $event)"
              />
            </td>
          </tr>
        </template>
      </tbody>
    </table>

    <div
      v-if="!loading && doctors.length === 0"
      class="text-center py-20 animate-in fade-in duration-500"
    >
      <div
        class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 mb-4"
      >
        <StethoscopeIcon class="h-6 w-6 text-slate-400" />
      </div>
      <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
        No doctors found.
      </p>
    </div>
  </div>
</template>

<script setup>
import {
  Stethoscope as StethoscopeIcon,
  Circle as CircleIcon,
} from "lucide-vue-next";
import { formatDate } from "../../utils/format";
import TableActions from "../ui/TableActions.vue";

defineProps({
  doctors: {
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
});

defineEmits(["view-info", "edit", "delete", "toggle-status"]);

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
