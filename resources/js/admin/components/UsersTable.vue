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
            Email
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Role
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Status
          </th>
          <th
            class="px-3 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider"
          >
            Updated On
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
              <div class="h-4 bg-slate-100 rounded-md w-36"></div>
            </td>
            <td class="px-3 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-16"></div>
            </td>
            <td class="px-3 py-4">
              <div class="h-6 bg-slate-100 rounded-full w-20"></div>
            </td>
            <td class="px-3 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-24"></div>
            </td>
            <td class="px-3 py-4 text-right">
              <div class="h-8 bg-slate-100 rounded-lg w-20 ml-auto"></div>
            </td>
          </tr>
        </template>

        <template v-else>
          <tr
            v-for="(user, index) in users"
            :key="user.id"
            class="group hover:bg-primary/5 transition-colors duration-300"
          >
            <td class="px-3 py-4 text-sm text-slate-500">
              {{ index + 1 }}
            </td>
            <td class="px-3 py-4">
              <span class="text-sm font-semibold text-slate-900">{{
                user.name
              }}</span>
            </td>
            <td class="px-3 py-4 text-sm text-slate-600">
              {{ user.email || "N/A" }}
            </td>
            <td class="px-3 py-4 text-sm text-slate-600 capitalize">
              {{ user.role || "N/A" }}
            </td>
            <td class="px-3 py-4">
              <button
                type="button"
                @click="$emit('toggle-status', user)"
                :class="
                  cn(
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase transition-all duration-200 active:scale-95',
                    user.status === 'active'
                      ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20'
                      : 'bg-slate-500/10 text-slate-500 border border-slate-500/20',
                  )
                "
              >
                <CircleIcon
                  class="h-2 w-2 mr-1.5 fill-current"
                  v-if="user.status === 'active'"
                />
                {{ user.status }}
              </button>
            </td>
            <td class="px-3 py-4 text-sm text-slate-500">
              {{ formatDate(user.updated_at) }}
            </td>
            <td class="px-3 py-4 text-right">
              <div
                class="flex justify-end gap-1.5 transition-opacity duration-200"
              >
                <button
                  @click="$emit('view', user)"
                  class="flex h-8 w-8 items-center justify-center rounded-lg text-primary hover:bg-primary/10 hover:text-primary-600 transition-all duration-200"
                  title="View User"
                >
                  <Eye class="h-4 w-4" />
                </button>
                <button
                  @click="$emit('edit', user)"
                  class="flex h-8 w-8 items-center justify-center rounded-lg text-emerald-500 hover:bg-emerald-500/10 hover:text-emerald-600 transition-all duration-200"
                  title="Edit User"
                >
                  <Edit class="h-4 w-4" />
                </button>
                <button
                  @click="$emit('delete', user)"
                  class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-500/10 hover:text-rose-600 transition-all duration-200"
                  title="Delete User"
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
      v-if="!loading && users.length === 0"
      class="text-center py-20 animate-in fade-in duration-500"
    >
      <div
        class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 mb-4"
      >
        <UserIcon class="h-6 w-6 text-slate-400" />
      </div>
      <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
        No users found.
      </p>
    </div>
  </div>
</template>

<script setup>
import {
  User as UserIcon,
  Circle as CircleIcon,
  Edit,
  Trash2,
  Eye,
} from "lucide-vue-next";
import { formatDate } from "../utils/format";

defineProps({
  users: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["edit", "delete", "toggle-status", "view"]);

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
