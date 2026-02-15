<template>
  <TransitionRoot :show="isOpen" as="template">
    <Dialog as="div" @close="handleClose" class="relative z-[70]">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel
              class="w-full max-w-md transform overflow-hidden rounded-[2.5rem] bg-white p-10 text-center shadow-2xl border border-slate-100 transition-all"
            >
              <!-- Confirmation State -->
              <div v-if="state === 'confirm'">
                <div
                  class="h-20 w-20 rounded-3xl bg-primary/10 flex items-center justify-center mx-auto mb-8 animate-in zoom-in duration-500"
                >
                  <FolderSyncIcon class="h-10 w-10 text-primary" />
                </div>
                <DialogTitle
                  as="h3"
                  class="text-2xl font-black text-slate-900 mb-3 tracking-tight"
                >
                  Upload {{ fileCount }} Files?
                </DialogTitle>
                <p
                  class="text-slate-500 text-sm font-medium leading-relaxed mb-10 px-4"
                >
                  You are about to upload a medical study containing
                  {{ fileCount }} DICOM files. This may take a few moments
                  depending on your connection.
                </p>
                <div class="flex gap-4">
                  <button
                    @click="handleClose"
                    class="flex-1 h-14 rounded-2xl border border-slate-200 text-slate-500 font-bold hover:bg-slate-50 transition-all active:scale-95"
                  >
                    Cancel
                  </button>
                  <button
                    @click="$emit('confirm')"
                    class="flex-1 h-14 rounded-2xl bg-primary text-white font-bold shadow-xl shadow-primary/20 hover:opacity-90 transition-all active:scale-95"
                  >
                    Start Upload
                  </button>
                </div>
              </div>

              <!-- Uploading State -->
              <div v-else-if="state === 'uploading'">
                <div class="relative h-20 w-20 mx-auto mb-8">
                  <div
                    class="absolute inset-0 rounded-full border-4 border-slate-100"
                  ></div>
                  <svg class="h-full w-full rotate-[-90deg]">
                    <circle
                      cx="40"
                      cy="40"
                      r="36"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="4"
                      class="text-primary transition-all duration-300"
                      :stroke-dasharray="226"
                      :stroke-dashoffset="226 - (226 * progress) / 100"
                    />
                  </svg>
                  <div
                    class="absolute inset-0 flex items-center justify-center"
                  >
                    <span class="text-xs font-black text-slate-900"
                      >{{ Math.round(progress) }}%</span
                    >
                  </div>
                </div>
                <DialogTitle
                  as="h3"
                  class="text-2xl font-black text-slate-900 mb-2 tracking-tight"
                >
                  Uploading Study
                </DialogTitle>
                <p
                  class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-8"
                >
                  Processing file {{ currentFileIndex + 1 }} of {{ totalFiles }}
                </p>

                <div
                  class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden mb-2"
                >
                  <div
                    class="bg-primary h-full transition-all duration-300 rounded-full shadow-[0_0_8px_rgba(var(--primary-rgb),0.4)]"
                    :style="{ width: `${progress}%` }"
                  ></div>
                </div>
                <p class="text-[11px] font-bold text-slate-500 truncate px-4">
                  {{ currentFileName }}
                </p>
              </div>

              <!-- General Alert State (Errors/Warning) -->
              <div v-else-if="state === 'alert'">
                <div
                  class="h-20 w-20 rounded-3xl flex items-center justify-center mx-auto mb-8 animate-in zoom-in duration-500"
                  :class="
                    variant === 'danger'
                      ? 'bg-rose-50 text-rose-500'
                      : 'bg-primary/5 text-primary'
                  "
                >
                  <component
                    :is="variant === 'danger' ? AlertCircleIcon : InfoIcon"
                    class="h-10 w-10"
                  />
                </div>
                <DialogTitle
                  as="h3"
                  class="text-2xl font-black text-slate-900 mb-3 tracking-tight"
                >
                  {{ title }}
                </DialogTitle>
                <p
                  class="text-slate-500 text-sm font-medium leading-relaxed mb-10 px-4"
                >
                  {{ description }}
                </p>
                <button
                  @click="handleClose"
                  class="w-full h-14 rounded-2xl bg-slate-900 text-white font-bold shadow-xl shadow-slate-900/10 hover:bg-slate-800 transition-all active:scale-95"
                >
                  Close
                </button>
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
} from "@headlessui/vue";
import {
  FolderSync as FolderSyncIcon,
  Loader2 as Loader2Icon,
  AlertCircle as AlertCircleIcon,
  Info as InfoIcon,
} from "lucide-vue-next";

defineProps({
  isOpen: Boolean,
  state: {
    type: String,
    default: "confirm", // 'confirm', 'uploading', 'alert'
  },
  fileCount: Number,
  progress: Number,
  currentFileIndex: Number,
  totalFiles: Number,
  currentFileName: String,
  title: String,
  description: String,
  variant: {
    type: String,
    default: "primary", // 'primary', 'danger'
  },
});

const emit = defineEmits(["close", "confirm"]);

const handleClose = () => {
  emit("close");
};
</script>
