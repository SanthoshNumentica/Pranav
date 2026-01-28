<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="onClose" class="relative z-[60]">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-[2rem] bg-white p-8 text-left align-middle shadow-2xl border border-slate-100 transition-all">
              <div class="flex flex-col items-center text-center">
                <div :class="cn('w-16 h-16 rounded-2xl flex items-center justify-center mb-6', variant === 'danger' ? 'bg-rose-50 text-rose-500' : 'bg-primary/5 text-primary')">
                  <component :is="icon" class="h-8 w-8" />
                </div>
                
                <DialogTitle as="h3" class="text-xl font-bold leading-6 text-slate-900 mb-2">
                  {{ title }}
                </DialogTitle>
                
                <div class="mt-2">
                  <p class="text-sm text-slate-500">
                    {{ description }}
                  </p>
                </div>

                <div class="mt-8 flex w-full gap-3">
                  <button
                    type="button"
                    class="flex-1 inline-flex justify-center items-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-all active:scale-95 outline-none"
                    @click="onClose"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    :class="cn(
                        'flex-1 inline-flex justify-center items-center rounded-xl px-4 py-3 text-sm font-bold text-white transition-all active:scale-95 outline-none shadow-lg',
                        variant === 'danger' ? 'bg-rose-500 hover:bg-rose-600 shadow-rose-500/20' : 'bg-primary hover:opacity-90 shadow-primary/20'
                    )"
                    @click="onConfirm"
                    :disabled="loading"
                  >
                    <Loader2Icon v-if="loading" class="h-4 w-4 animate-spin mr-2" />
                    <span>{{ confirmLabel }}</span>
                  </button>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from '@headlessui/vue';
import { Loader2 as Loader2Icon, LogOut as LogOutIcon } from 'lucide-vue-next';

const props = defineProps({
  isOpen: Boolean,
  title: String,
  description: String,
  confirmLabel: {
    type: String,
    default: 'Confirm'
  },
  variant: {
    type: String,
    default: 'primary' // or 'danger'
  },
  icon: {
    type: [Object, Function],
    default: () => LogOutIcon
  },
  loading: Boolean
});

const emit = defineEmits(['close', 'confirm']);

const onClose = () => {
  console.log('Modal: Closing');
  emit('close');
};

const onConfirm = () => {
  console.log('Modal: Confirming');
  emit('confirm');
};

function cn(...classes) {
  return classes.filter(Boolean).join(' ');
}
</script>
