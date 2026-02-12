<script setup>
import {
  SelectContent,
  SelectPortal,
  SelectViewport,
  useForwardPropsEmits,
} from "radix-vue";
import { cn } from "../../../utils/cn";

const props = defineProps({
  forceMount: { type: Boolean, required: false },
  position: { type: String, default: "popper" },
  bodyLock: { type: Boolean, required: false },
  side: { type: String, required: false },
  sideOffset: { type: Number, default: 4 },
  align: { type: String, required: false },
  alignOffset: { type: Number, required: false },
  avoidCollisions: { type: Boolean, required: false },
  collisionBoundary: { type: null, required: false },
  collisionPadding: { type: [Number, Object], required: false },
  arrowPadding: { type: Number, required: false },
  sticky: { type: String, required: false },
  hideWhenDetached: { type: Boolean, required: false },
  class: { type: null, required: false },
});
const emits = defineEmits([
  "closeAutoFocus",
  "escapeKeyDown",
  "pointerDownOutside",
]);

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
  <SelectPortal>
    <SelectContent
      v-bind="forwarded"
      @pointerdown.stop
      @mousedown.stop
      @click.stop
      @touchend.stop
      @pointer-down-outside="
        (event) => {
          const originalEvent = event.detail.originalEvent;
          if (originalEvent) {
            originalEvent.stopPropagation();
            originalEvent.preventDefault();
          }
        }
      "
      @escape-key-down="
        (event) => {
          const originalEvent = event.detail.originalEvent;
          if (originalEvent) {
            originalEvent.stopPropagation();
            originalEvent.preventDefault();
          }
        }
      "
      :class="
        cn(
          'relative z-50 max-h-56 min-w-[8rem] overflow-hidden rounded-lg border bg-white text-slate-950 shadow-lg data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',
          position === 'popper' &&
            'data-[side=bottom]:translate-y-1 data-[side=left]:-translate-x-1 data-[side=right]:translate-x-1 data-[side=top]:-translate-y-1',
          props.class,
        )
      "
    >
      <SelectViewport
        :class="
          cn(
            'p-1',
            position === 'popper' &&
              'h-[var(--radix-select-content-available-height)] w-full min-w-[var(--radix-select-trigger-width)]',
          )
        "
      >
        <slot />
      </SelectViewport>
    </SelectContent>
  </SelectPortal>
</template>
