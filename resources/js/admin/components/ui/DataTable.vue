<template>
  <div
    class="bg-white rounded-3xl border border-slate-200 shadow-soft-xl overflow-x-auto custom-scrollbar animate-in fade-in slide-in-from-bottom-4 duration-700 delay-200"
  >
    <table class="w-full border-separate border-spacing-0">
      <thead>
        <tr class="border-b border-slate-200 bg-slate-50/50">
          <th
            v-for="col in columns"
            :key="col.key"
            :class="[
              'px-6 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider',
              col.align === 'right' ? 'text-right' : 'text-left',
              col.width ? `w-[${col.width}]` : '',
            ]"
            :style="{ width: col.width }"
          >
            {{ col.label }}
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <!-- Loading State -->
        <template v-if="loading">
          <tr v-for="i in 5" :key="i" class="animate-pulse">
            <td v-for="col in columns" :key="col.key" class="px-6 py-4">
              <div class="h-4 bg-slate-100 rounded-md w-full"></div>
            </td>
          </tr>
        </template>

        <!-- Empty State -->
        <template v-else-if="items.length === 0">
          <tr>
            <td :colspan="columns.length" class="px-6 py-12 text-center">
              <div
                class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 mb-4"
              >
                <DatabaseIcon class="h-6 w-6 text-slate-400" />
              </div>
              <p class="text-sm font-medium text-slate-500">
                {{ emptyText }}
              </p>
            </td>
          </tr>
        </template>

        <!-- Data Rows -->
        <template v-else>
          <tr
            v-for="(item, index) in items"
            :key="item.id || index"
            class="group hover:bg-primary/5 transition-colors duration-300"
          >
            <td
              v-for="col in columns"
              :key="col.key"
              :class="[
                'px-6 py-4 text-sm text-slate-600',
                col.align === 'right' ? 'text-right' : 'text-left',
              ]"
            >
              <slot :name="`cell-${col.key}`" :item="item" :index="index">
                {{ item[col.key] }}
              </slot>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { Database as DatabaseIcon } from "lucide-vue-next";

defineProps({
  columns: {
    type: Array,
    required: true,
  },
  items: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  emptyText: {
    type: String,
    default: "No data found.",
  },
});
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
