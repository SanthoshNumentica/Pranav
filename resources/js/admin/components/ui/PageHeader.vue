<template>
  <div
    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-500"
  >
    <div>
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
        {{ title }}
      </h1>
      <p v-if="subtitle" class="text-sm text-slate-500 mt-1">
        {{ subtitle }}
      </p>
    </div>
    <div class="flex items-center gap-3">
      <slot name="actions" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: "",
  },
});
</script>
