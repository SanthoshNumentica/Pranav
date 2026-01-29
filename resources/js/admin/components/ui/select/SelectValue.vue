<script setup>
import { SelectValue } from "radix-vue";

const props = defineProps({
  placeholder: { type: String, required: false },
});
</script>

<template>
  <SelectValue v-bind="props">
    <slot />
  </SelectValue>
</template>
