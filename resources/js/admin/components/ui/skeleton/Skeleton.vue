<script setup>
import { cn } from "../../../utils/cn";

const props = defineProps({
  class: { type: null, required: false },
});
</script>

<template>
  <div :class="cn('animate-pulse rounded-md bg-slate-200/50', props.class)" />
</template>
