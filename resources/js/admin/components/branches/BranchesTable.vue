<template>
  <div class="min-w-full inline-block align-middle">
    <table class="min-w-full divide-y divide-slate-200">
      <thead class="bg-slate-50/50">
        <tr>
          <th
            scope="col"
            class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-widest"
          >
            Branch Info
          </th>
          <th
            scope="col"
            class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-widest"
          >
            Contact
          </th>
          <th
            scope="col"
            class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-widest"
          >
            Status
          </th>
          <th
            scope="col"
            class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-widest"
          >
            Actions
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-slate-100">
        <template v-if="loading">
          <tr v-for="i in 5" :key="i" class="animate-pulse">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="h-4 bg-slate-100 rounded w-3/4 mb-2"></div>
              <div class="h-3 bg-slate-50 rounded w-1/2"></div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="h-4 bg-slate-100 rounded w-1/2 mb-2"></div>
              <div class="h-3 bg-slate-50 rounded w-1/3"></div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="h-6 bg-slate-100 rounded-full w-16"></div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right">
              <div class="h-8 bg-slate-100 rounded-lg w-20 ml-auto"></div>
            </td>
          </tr>
        </template>

        <template v-else-if="branches.length > 0">
          <tr
            v-for="branch in branches"
            :key="branch.id"
            class="hover:bg-slate-50/50 transition-colors group"
          >
            <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-900">
              <div class="flex flex-col">
                <span class="font-semibold text-slate-900">{{
                  branch.name
                }}</span>
                <span class="text-xs text-slate-500">{{
                  branch.code || "No Code"
                }}</span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
              <div class="flex flex-col gap-1">
                <div v-if="branch.phone" class="flex items-center gap-1.5">
                  <PhoneIcon class="h-3 w-3 text-slate-400" />
                  <span>{{ branch.phone }}</span>
                </div>
                <div
                  v-if="branch.email"
                  class="flex items-center gap-1.5 font-medium text-primary"
                >
                  <MailIcon class="h-3 w-3" />
                  <span>{{ branch.email }}</span>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <button
                @click="$emit('toggle-status', branch)"
                :disabled="!permissions.canStatus"
                :class="
                  cn(
                    'px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider transition-all',
                    branch.status === 'active'
                      ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'
                      : 'bg-rose-50 text-rose-600 hover:bg-rose-100',
                  )
                "
              >
                {{ branch.status }}
              </button>
            </td>
            <td
              class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
            >
              <div class="flex items-center justify-end gap-2">
                <button
                  @click="$emit('view-info', branch)"
                  class="flex h-8 w-8 items-center justify-center rounded-xl text-blue-500 hover:bg-blue-500/10 transition-all active:scale-90"
                  title="View Branch Details"
                >
                  <EyeIcon class="h-4 w-4" />
                </button>
                <button
                  v-if="permissions.canEdit"
                  @click="$emit('edit', branch)"
                  class="flex h-8 w-8 items-center justify-center rounded-xl text-primary hover:bg-primary/10 transition-all active:scale-90"
                  title="Edit Branch"
                >
                  <EditIcon class="h-4 w-4" />
                </button>
                <button
                  v-if="permissions.canDelete"
                  @click="$emit('delete', branch)"
                  class="flex h-8 w-8 items-center justify-center rounded-xl text-rose-500 hover:bg-rose-500/10 transition-all active:scale-90"
                  title="Delete Branch"
                >
                  <TrashIcon class="h-4 w-4" />
                </button>
              </div>
            </td>
          </tr>
        </template>

        <tr v-else>
          <td colspan="4" class="px-6 py-12 text-center">
            <div class="flex flex-col items-center justify-center gap-3">
              <div class="p-4 bg-slate-50 rounded-2xl">
                <BuildingIcon class="h-8 w-8 text-slate-300" />
              </div>
              <div class="text-slate-400 font-medium">No branches found</div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import {
  Pencil as EditIcon,
  Trash2 as TrashIcon,
  Building2 as BuildingIcon,
  Phone as PhoneIcon,
  Mail as MailIcon,
  Eye as EyeIcon,
} from "lucide-vue-next";

defineProps({
  branches: {
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

defineEmits(["edit", "delete", "toggle-status", "view-info"]);

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}
</script>
