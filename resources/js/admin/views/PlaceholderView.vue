<template>
  <div class="flex flex-col items-center justify-center h-[60vh] text-center">
    <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-4">
      <FileText class="h-8 w-8 text-primary" />
    </div>
    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-2">
      {{ route.name }} Page
    </h1>
    <p class="text-slate-500 dark:text-slate-400">
      This page is currently under development.
    </p>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router';
import { FileText } from 'lucide-vue-next';
const route = useRoute();
</script>
