<script setup>
import { SelectItem, SelectItemIndicator, SelectItemText } from "radix-vue";
import { Check } from "lucide-vue-next";
import { cn } from "../../../utils/cn";

const props = defineProps({
  value: { type: [String, Number], required: true },
  disabled: { type: Boolean, required: false },
  textValue: { type: String, required: false },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
  class: { type: null, required: false },
});
</script>

<template>
  <SelectItem
    v-bind="props"
    :class="
      cn(
        'relative flex w-full cursor-pointer select-none items-center rounded-md py-2 px-3 text-sm outline-none focus:bg-primary/10 data-[disabled]:pointer-events-none data-[disabled]:opacity-50 transition-colors',
        props.class,
      )
    "
  >
    <SelectItemText>
      <slot />
    </SelectItemText>

    <span class="absolute right-2 flex h-3.5 w-3.5 items-center justify-center">
      <SelectItemIndicator>
        <Check class="h-4 w-4 text-primary" />
      </SelectItemIndicator>
    </span>
  </SelectItem>
</template>
