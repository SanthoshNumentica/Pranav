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
            Branch
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
              <div class="h-4 bg-slate-100 rounded-md w-36"></div>
            </td>
            <td class="px-3 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-16"></div>
            </td>
            <td class="px-3 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-20"></div>
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
              {{ user.role?.name || "N/A" }}
            </td>
            <td class="px-3 py-4 text-sm text-slate-600">
              <div
                class="flex items-center gap-1.5"
                v-if="user.role?.name.toLowerCase() === 'super-admin'"
              >
                <div class="h-1.5 w-1.5 rounded-full bg-indigo-500"></div>
                <span class="font-medium text-slate-900">All Branches</span>
              </div>
              <div class="flex items-center gap-1.5" v-else-if="user.branch">
                <div class="h-1.5 w-1.5 rounded-full bg-primary/40"></div>
                {{ user.branch.name }}
              </div>
              <span v-else class="text-slate-300 italic text-xs"
                >Unassigned</span
              >
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
            <td class="px-3 py-4">
              <div class="flex flex-col">
                <span class="text-xs text-slate-600">{{
                  formatDate(user.created_at)
                }}</span>
                <span
                  v-if="user.added_by_user"
                  class="text-[10px] text-slate-400"
                >
                  by {{ user.added_by_user?.name || "Unknown" }}
                </span>
              </div>
            </td>
            <td class="px-3 py-4">
              <div class="flex flex-col">
                <span class="text-xs text-slate-600">{{
                  formatDate(user.updated_at)
                }}</span>
                <span
                  v-if="user.modified_by_user"
                  class="text-[10px] text-slate-400"
                >
                  by {{ user.modified_by_user?.name || "Unknown" }}
                </span>
                <span
                  v-else-if="user.added_by_user"
                  class="text-[10px] text-slate-400"
                >
                  by {{ user.added_by_user?.name || "Unknown" }}
                </span>
              </div>
            </td>
            <td class="px-3 py-4 text-right">
              <TableActions
                :item="user"
                :permissions="permissions"
                view-title="View User"
                edit-title="Edit User"
                delete-title="Delete User"
                @view="$emit('view', $event)"
                @edit="$emit('edit', $event)"
                @delete="$emit('delete', $event)"
              />
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
import { User as UserIcon, Circle as CircleIcon } from "lucide-vue-next";
import { formatDate } from "../../utils/format";
import TableActions from "../ui/TableActions.vue";

defineProps({
  users: {
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
