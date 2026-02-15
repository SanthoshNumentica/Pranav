<script setup>
import { ref, computed } from "vue";
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
const forwarded = useForwardPropsEmits(props, emits);

// We only want to pass the 'open' prop to SelectRoot if it's explicitly controlled.
// This allows Radix-Vue to manage internal state natively when uncontrolled.
const rootProps = computed(() => {
  const p = { ...forwarded };
  if (props.open === undefined) {
    delete p.open;
  }
  return p;
});

const value = computed({
  get: () => props.modelValue,
  set: (val) => emits("update:modelValue", val),
});

const handleOpenChange = (val) => {
  emits("update:open", val);
};
</script>

<template>
  <SelectRoot
    v-bind="rootProps"
    v-model="value"
    @update:open="handleOpenChange"
  >
    <slot />
  </SelectRoot>
</template>
