<template>
  <div v-if="pagination && pagination.total > 0"
    class="flex items-center justify-between px-6 py-4 border-t border-slate-100 bg-slate-50/50">
    <div class="text-sm text-slate-500">
      Displaying
      <span class="font-bold text-slate-700">{{ pagination.from || 0 }}</span>
      -
      <span class="font-bold text-slate-700">{{ pagination.to || 0 }}</span>
      of
      <span class="font-bold text-slate-700">{{ pagination.total || 0 }}</span>
      entries
    </div>

    <div v-if="pagination.last_page > 1" class="flex items-center gap-2">
      <!-- First Page Button -->
      <button @click="changePage(1)" :disabled="pagination.current_page === 1" :class="cn(
        'h-9 w-9 flex items-center justify-center rounded-xl transition-all',
        pagination.current_page !== 1
          ? 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 active:scale-95 shadow-sm'
          : 'bg-slate-50 text-slate-300 border border-slate-100 cursor-not-allowed',
      )
        " title="First Page">
        <ChevronsLeftIcon class="h-4 w-4" />
      </button>

      <!-- Previous Button -->
      <button @click="changePage(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" :class="cn(
        'h-9 w-9 flex items-center justify-center rounded-xl transition-all',
        pagination.prev_page_url
          ? 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 active:scale-95 shadow-sm'
          : 'bg-slate-50 text-slate-300 border border-slate-100 cursor-not-allowed',
      )
        " title="Previous Page">
        <ChevronLeftIcon class="h-4 w-4" />
      </button>

      <!-- Page Numbers -->
      <div class="flex items-center gap-1 mx-1">
        <button v-for="page in visiblePages" :key="page" @click="changePage(page)" :class="cn(
          'h-9 min-w-[36px] px-2 flex items-center justify-center rounded-xl text-sm font-bold transition-all',
          page === pagination.current_page
            ? 'bg-primary text-white shadow-lg shadow-primary/20'
            : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 active:scale-95',
        )
          ">
          {{ page }}
        </button>
      </div>

      <!-- Next Button -->
      <button @click="changePage(pagination.current_page + 1)" :disabled="!pagination.next_page_url" :class="cn(
        'h-9 w-9 flex items-center justify-center rounded-xl transition-all',
        pagination.next_page_url
          ? 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 active:scale-95 shadow-sm'
          : 'bg-slate-50 text-slate-300 border border-slate-100 cursor-not-allowed',
      )
        " title="Next Page">
        <ChevronRightIcon class="h-4 w-4" />
      </button>

      <!-- Last Page Button -->
      <button @click="changePage(pagination.last_page)" :disabled="pagination.current_page === pagination.last_page"
        :class="cn(
          'h-9 w-9 flex items-center justify-center rounded-xl transition-all',
          pagination.current_page !== pagination.last_page
            ? 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 active:scale-95 shadow-sm'
            : 'bg-slate-50 text-slate-300 border border-slate-100 cursor-not-allowed',
        )
          " title="Last Page">
        <ChevronsRightIcon class="h-4 w-4" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  ChevronLeft as ChevronLeftIcon,
  ChevronRight as ChevronRightIcon,
  ChevronsLeft as ChevronsLeftIcon,
  ChevronsRight as ChevronsRightIcon,
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
