<template>
  <TransitionRoot as="template" :show="isOpen">
    <Dialog as="div" class="relative z-50" @close="close">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div
          class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
        />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div
          class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
        >
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel
              class="relative transform overflow-hidden rounded-[32px] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200 flex flex-col h-[90vh] sm:h-[80vh]"
            >
              <!-- Premium Banner Header -->
              <div
                class="relative bg-primary px-6 py-8 sm:px-10 text-white overflow-hidden shrink-0"
              >
                <!-- Decorative Blur Elements -->
                <div
                  class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"
                />
                <div
                  class="absolute -bottom-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"
                />

                <!-- Header Content -->
                <div class="relative flex items-center justify-between">
                  <div class="flex items-center gap-4">
                    <div
                      class="h-12 w-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 shadow-inner"
                    >
                      <BuildingIcon class="h-6 w-6 text-white" />
                    </div>
                    <div>
                      <h3 class="text-xl font-bold tracking-tight">
                        Branch Details
                      </h3>
                      <div class="flex items-center gap-2 mt-1 opacity-90">
                        <span class="text-sm font-medium">{{
                          branch?.name
                        }}</span>
                        <div class="h-1 w-1 rounded-full bg-white/40" />
                        <span class="text-xs font-mono tracking-wider">{{
                          branch?.code || "NO-CODE"
                        }}</span>
                      </div>
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <button
                      v-if="canEdit"
                      @click="$emit('edit', branch)"
                      class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/20 hover:bg-white/30 text-white text-xs font-bold transition-all active:scale-95 border border-white/20 backdrop-blur-sm shadow-lg shadow-black/5"
                    >
                      <EditIcon class="h-4 w-4" />
                      Edit Branch
                    </button>
                    <button
                      @click="close"
                      class="p-2 rounded-xl hover:bg-white/10 transition-colors"
                    >
                      <XIcon class="h-5 w-5 text-white" />
                    </button>
                  </div>
                </div>
              </div>

              <!-- Content Area -->
              <div class="flex-1 overflow-y-auto custom-scrollbar">
                <div class="p-8 space-y-8">
                  <!-- Detailed Info Grid -->
                  <div class="grid grid-cols-1 gap-6">
                    <div
                      v-for="section in detailedInfo"
                      :key="section.label"
                      class="group"
                    >
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1 mb-2 block group-hover:text-primary transition-colors"
                      >
                        {{ section.label }}
                      </label>
                      <div
                        class="p-4 rounded-2xl bg-slate-50 border border-slate-100 group-hover:border-primary/20 group-hover:bg-primary/[0.02] transition-all"
                      >
                        <div class="flex items-start gap-3">
                          <component
                            :is="section.icon"
                            class="h-4 w-4 text-slate-400 mt-0.5 group-hover:text-primary transition-colors"
                          />
                          <span
                            class="text-sm font-semibold text-slate-700 leading-relaxed"
                            >{{ section.value || "Not provided" }}</span
                          >
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Footer -->
              <div
                class="p-6 border-t border-slate-100 bg-slate-50/50 flex justify-end shrink-0"
              >
                <button
                  @click="close"
                  class="px-8 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-50 transition-all active:scale-95 shadow-sm"
                >
                  Close Details
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
import { computed } from "vue";
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import {
  Building2 as BuildingIcon,
  X as XIcon,
  Edit as EditIcon,
  Mail as MailIcon,
  Phone as PhoneIcon,
  MapPin as MapPinIcon,
  Hash,
} from "lucide-vue-next";

const props = defineProps({
  isOpen: Boolean,
  branch: {
    type: Object,
    default: null,
  },
  canEdit: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["close", "edit"]);

const close = () => {
  emit("close");
};

const detailedInfo = computed(() => [
  {
    label: "Contact Email",
    value: props.branch?.email,
    icon: MailIcon,
  },
  {
    label: "Phone Number",
    value: props.branch?.phone,
    icon: PhoneIcon,
  },
  {
    label: "Address",
    value: props.branch?.address,
    icon: MapPinIcon,
  },
]);
</script>
