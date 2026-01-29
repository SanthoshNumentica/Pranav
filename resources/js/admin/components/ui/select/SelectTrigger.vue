<script setup>
import { SelectIcon, SelectTrigger, useForwardProps } from "radix-vue";
import { ChevronDown } from "lucide-vue-next";
import { cn } from "../../../utils/cn";

const props = defineProps({
  disabled: { type: Boolean, required: false },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
  class: { type: null, required: false },
});

const forwardedProps = useForwardProps(props);
</script>

<template>
  <SelectTrigger
    v-bind="forwardedProps"
    :class="
      cn(
        'flex h-10 w-full items-center justify-between rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary disabled:cursor-not-allowed disabled:opacity-50 [&>span]:line-clamp-1 font-medium text-slate-700 transition-all',
        props.class,
      )
    "
  >
    <slot />
    <SelectIcon as-child>
      <ChevronDown class="h-4 w-4 opacity-50 text-slate-400" />
    </SelectIcon>
  </SelectTrigger>
</template>
