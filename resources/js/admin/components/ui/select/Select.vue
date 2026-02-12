<script setup>
import { SelectRoot, useForwardPropsEmits } from "radix-vue";

const props = defineProps({
  open: { type: Boolean, required: false },
  defaultOpen: { type: Boolean, required: false },
  modelValue: { type: [String, Number], required: false },
  defaultValue: { type: [String, Number], required: false },
  dir: { type: String, required: false },
  name: { type: String, required: false },
  autocomplete: { type: String, required: false },
  disabled: { type: Boolean, required: false },
  required: { type: Boolean, required: false },
});

const emits = defineEmits(["update:modelValue", "update:open"]);

const handleOpenChange = (val) => {
  if (!val) {
    // Delay setting open to false so modal close guards can see it's still open
    setTimeout(() => {
      emits("update:open", false);
    }, 100);
  } else {
    emits("update:open", true);
  }
};

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
  <SelectRoot v-bind="forwarded" :open="open" @update:open="handleOpenChange">
    <slot />
  </SelectRoot>
</template>
