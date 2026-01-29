<template>
  <div
    v-if="pagination && pagination.last_page > 1"
    class="flex items-center justify-between px-6 py-4 border-t border-slate-100 bg-slate-50/50"
  >
    <div class="text-sm text-slate-500">
      Showing
      <span class="font-bold text-slate-700">{{ pagination.from || 0 }}</span>
      to
      <span class="font-bold text-slate-700">{{ pagination.to || 0 }}</span>
      of
      <span class="font-bold text-slate-700">{{ pagination.total || 0 }}</span>
      results
    </div>

    <div class="flex items-center gap-2">
      <!-- Previous Button -->
      <button
        @click="changePage(pagination.current_page - 1)"
        :disabled="!pagination.prev_page_url"
        :class="
          cn(
            'h-9 w-9 flex items-center justify-center rounded-xl transition-all',
            pagination.prev_page_url
              ? 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 active:scale-95'
              : 'bg-slate-100 text-slate-300 cursor-not-allowed',
          )
        "
      >
        <ChevronLeftIcon class="h-4 w-4" />
      </button>

      <!-- Page Numbers -->
      <div class="flex items-center gap-1">
        <button
          v-for="page in visiblePages"
          :key="page"
          @click="changePage(page)"
          :class="
            cn(
              'h-9 min-w-[36px] px-2 flex items-center justify-center rounded-xl text-sm font-bold transition-all',
              page === pagination.current_page
                ? 'bg-primary text-white shadow-lg shadow-primary/20'
                : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 active:scale-95',
            )
          "
        >
          {{ page }}
        </button>
      </div>

      <!-- Next Button -->
      <button
        @click="changePage(pagination.current_page + 1)"
        :disabled="!pagination.next_page_url"
        :class="
          cn(
            'h-9 w-9 flex items-center justify-center rounded-xl transition-all',
            pagination.next_page_url
              ? 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 active:scale-95'
              : 'bg-slate-100 text-slate-300 cursor-not-allowed',
          )
        "
      >
        <ChevronRightIcon class="h-4 w-4" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  ChevronLeft as ChevronLeftIcon,
  ChevronRight as ChevronRightIcon,
} from "lucide-vue-next";

const props = defineProps({
  pagination: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["page-change"]);

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}

const visiblePages = computed(() => {
  const current = props.pagination.current_page;
  const last = props.pagination.last_page;
  const delta = 2;
  const pages = [];

  for (
    let i = Math.max(1, current - delta);
    i <= Math.min(last, current + delta);
    i++
  ) {
    pages.push(i);
  }

  return pages;
});

const changePage = (page) => {
  if (page >= 1 && page <= props.pagination.last_page) {
    emit("page-change", page);
  }
};
</script>
