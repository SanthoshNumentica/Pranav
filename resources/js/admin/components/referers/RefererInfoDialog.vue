<template>
  <TransitionRoot as="template" :show="isOpen">
    <Dialog as="div" class="relative z-50" @close="() => {}">
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
              class="relative transform overflow-hidden rounded-[32px] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200 flex flex-col h-[80vh] sm:h-auto sm:max-h-[85vh]"
            >
              <!-- Header/Banner - Fixed -->
              <div
                class="relative bg-primary px-6 py-8 sm:px-10 text-white overflow-hidden shrink-0"
              >
                <div
                  class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"
                ></div>
                <div
                  class="absolute -bottom-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"
                ></div>

                <div class="relative flex items-center justify-between">
                  <div class="flex items-center gap-4">
                    <div
                      class="h-12 w-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30"
                    >
                      <UserIcon class="h-6 w-6 text-white" />
                    </div>
                    <div>
                      <h3 class="text-xl font-bold tracking-tight">
                        Referer Details
                      </h3>
                      <div class="flex items-center gap-2 mt-1 opacity-90">
                        <span class="text-sm font-medium">{{
                          referer?.name
                        }}</span>
                        <span class="h-1 w-1 rounded-full bg-white/50"></span>
                        <span
                          class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full border border-white/20"
                        >
                          {{ referer?.referer_type?.name || "N/A" }}
                        </span>
                        <span class="h-1 w-1 rounded-full bg-white/50"></span>
                        <div class="flex items-center gap-1.5 opacity-80">
                          <MapPinIcon class="h-3 w-3" />
                          <span class="text-xs font-semibold">{{
                            referer?.place || "N/A"
                          }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <button
                      @click="$emit('edit', referer)"
                      class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/20 hover:bg-white/30 text-white text-xs font-bold transition-all active:scale-95"
                    >
                      <EditIcon class="h-4 w-4" />
                      Edit
                    </button>
                    <button
                      @click.stop="close"
                      class="p-2 rounded-xl hover:bg-white/10 transition-colors"
                    >
                      <XIcon class="h-5 w-5 text-white" />
                    </button>
                  </div>
                </div>
              </div>

              <!-- Content - Scrollable -->
              <div
                class="p-6 sm:p-8 space-y-8 overflow-y-auto flex-grow custom-scrollbar"
              >
                <!-- Status Badge -->
                <div class="flex justify-end">
                  <span
                    :class="
                      cn(
                        'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider',
                        referer?.status === 'active'
                          ? 'bg-emerald-50 text-emerald-600 border border-emerald-100'
                          : 'bg-slate-50 text-slate-600 border border-slate-100',
                      )
                    "
                  >
                    {{ referer?.status }}
                  </span>
                </div>

                <!-- Fields Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                  <div
                    v-for="(field, label) in detailedInfo"
                    :key="label"
                    class="space-y-1"
                  >
                    <span
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 block ml-1"
                      >{{ label }}</span
                    >
                    <p
                      class="text-sm font-semibold text-slate-700 bg-slate-50/50 p-3 rounded-xl border border-slate-100/50"
                    >
                      {{ field || "N/A" }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Footer - Fixed -->
              <div
                class="bg-slate-50 px-6 py-4 sm:px-8 flex flex-col sm:flex-row justify-between items-center gap-4 shrink-0 border-t border-slate-200 rounded-b-[32px]"
              >
                <div
                  class="flex flex-col items-center sm:items-start text-center sm:text-left"
                >
                  <span
                    class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"
                  >
                    Created on {{ formatDate(referer?.created_at) }}
                    <template v-if="referer?.added_by_user">
                      by {{ referer.added_by_user.name }}
                    </template>
                  </span>
                  <span
                    v-if="
                      referer?.modified_by_user &&
                      referer?.modified_by !== referer?.added_by
                    "
                    class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"
                  >
                    Last modified by {{ referer.modified_by_user.name }}
                  </span>
                </div>
                <button
                  type="button"
                  class="inline-flex w-full justify-center rounded-xl bg-white px-6 py-2.5 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:w-auto transition-all active:scale-95"
                  @click="close"
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
import { computed } from "vue";
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import {
  X as XIcon,
  User as UserIcon,
  Edit as EditIcon,
  MapPin as MapPinIcon,
} from "lucide-vue-next";
import { formatDate } from "../../utils/format";

const props = defineProps({
  isOpen: Boolean,
  referer: Object,
});

const emit = defineEmits(["close", "edit"]);

const close = () => {
  if (!props.isOpen) return;
  emit("close");
};

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}

const detailedInfo = computed(() => {
  if (!props.referer) return {};
  return {
    "Referer Name": props.referer.name,
    "Referer Type": props.referer.referer_type?.name,
    "Mobile No": props.referer.mobile_no,
    "Email ID": props.referer.email_id,
    Place: props.referer.place,
    "Hospital Name": props.referer.hospital_name,
    "Hospital ID": props.referer.hospital_id,
    "Added By": props.referer.added_by_user?.name || "N/A",
    "Modified By": props.referer.modified_by_user?.name || "N/A",
  };
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 20px;
}
</style>
