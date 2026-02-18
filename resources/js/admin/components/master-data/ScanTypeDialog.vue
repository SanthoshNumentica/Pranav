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
              class="relative transform overflow-hidden rounded-[32px] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200 flex flex-col h-[90vh] sm:h-[80vh]"
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
                      <ScanIcon class="h-6 w-6 text-white" />
                    </div>
                    <div>
                      <DialogTitle
                        as="h3"
                        class="text-xl font-bold tracking-tight text-white"
                      >
                        {{
                          mode === "view"
                            ? "View Scan Type"
                            : mode === "edit"
                              ? "Edit Scan Type"
                              : "Add New Scan Type"
                        }}
                      </DialogTitle>
                      <p class="text-sm font-medium mt-1 opacity-90">
                        {{
                          mode === "view"
                            ? "Viewing scan type and its included procedures."
                            : "Define scan types and manage their sub-scans."
                        }}
                      </p>
                    </div>
                  </div>
                  <button
                    @click.stop="onClose"
                    class="p-2 rounded-xl hover:bg-white/10 transition-colors"
                  >
                    <XIcon class="h-5 w-5 text-white" />
                  </button>
                </div>
              </div>

              <form @submit.prevent="handleSubmit">
                <div
                  class="flex-1 overflow-y-auto p-6 sm:p-8 custom-scrollbar space-y-6"
                >
                  <!-- Error Message -->
                  <div
                    v-if="error"
                    class="bg-rose-50 text-rose-500 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2"
                  >
                    <AlertCircleIcon class="h-4 w-4 shrink-0" />
                    <span>{{ error }}</span>
                  </div>

                  <!-- Name Field -->
                  <div class="space-y-1.5">
                    <label
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >Scan Type Name</label
                    >
                    <input
                      v-model="form.name"
                      type="text"
                      placeholder="e.g. MRI, CT Scan"
                      class="w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm focus:ring-primary/20 focus:border-primary transition-all duration-200 disabled:opacity-75 disabled:cursor-not-allowed"
                      :disabled="mode === 'view' || loading"
                      required
                    />
                  </div>

                  <!-- Multiple Scans Field -->
                  <div class="space-y-3">
                    <div class="flex items-center justify-between">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                        >Included Scans</label
                      >
                      <button
                        v-if="mode !== 'view'"
                        type="button"
                        @click="addScan"
                        class="text-xs font-bold text-primary hover:bg-primary/10 px-2 py-1 rounded-lg transition-all"
                      >
                        + Add Scan
                      </button>
                    </div>

                    <div class="space-y-2">
                      <div
                        v-for="(scan, index) in form.scans"
                        :key="index"
                        class="flex items-center gap-2 group"
                      >
                        <div class="relative flex-[2]">
                          <input
                            v-model="scan.name"
                            type="text"
                            placeholder="Scan Name"
                            class="w-full bg-slate-50/50 border-slate-100 group-hover:bg-white group-hover:border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-primary/20 focus:border-primary transition-all duration-200 disabled:opacity-75 disabled:cursor-not-allowed"
                            :disabled="mode === 'view' || loading"
                            required
                          />
                        </div>
                        <div class="relative flex-1">
                          <input
                            v-model="scan.amount"
                            type="number"
                            placeholder="Amount"
                            class="w-full bg-slate-50/50 border-slate-100 group-hover:bg-white group-hover:border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-primary/20 focus:border-primary transition-all duration-200 disabled:opacity-75 disabled:cursor-not-allowed"
                            :disabled="mode === 'view' || loading"
                            min="0"
                            step="0.01"
                          />
                        </div>
                        <button
                          v-if="mode !== 'view'"
                          @click="removeScan(index)"
                          type="button"
                          class="h-9 w-9 flex items-center justify-center rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all duration-200"
                        >
                          <TrashIcon class="h-4 w-4" />
                        </button>
                      </div>

                      <div
                        v-if="form.scans.length === 0"
                        class="text-center py-8 border-2 border-dashed border-slate-100 rounded-3xl"
                      >
                        <p class="text-xs text-slate-400">
                          No scans added yet.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <div
                  class="bg-slate-50 px-6 py-6 border-t border-slate-100 flex justify-end gap-3 rounded-b-[32px] shrink-0"
                >
                  <button
                    type="button"
                    @click.stop="onClose"
                    class="px-6 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-100 transition-all active:scale-95 bg-white shadow-sm"
                    :disabled="loading"
                  >
                    {{ mode === "view" ? "Close" : "Cancel" }}
                  </button>
                  <button
                    v-if="mode !== 'view'"
                    type="submit"
                    class="px-8 py-2.5 rounded-xl bg-primary text-sm font-bold text-white hover:opacity-90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center min-w-[120px] active:scale-95"
                    :disabled="loading"
                  >
                    <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
                    <span v-else>{{
                      mode === "edit" ? "Update Scan Type" : "Create Scan Type"
                    }}</span>
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch } from "vue";
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import {
  X as XIcon,
  Scan as ScanIcon,
  Plus as PlusIcon,
  Trash2 as TrashIcon,
  Loader2,
  AlertCircle as AlertCircleIcon,
} from "lucide-vue-next";

const props = defineProps({
  isOpen: Boolean,
  mode: {
    type: String,
    default: "add",
  },
  initialData: {
    type: Object,
    default: () => ({}),
  },
  loading: Boolean,
  error: String,
});

const emit = defineEmits(["close", "submit"]);

const form = ref({
  name: "",
  scans: [],
});

watch(
  () => props.isOpen,
  (val) => {
    if (val) {
      if (
        (props.mode === "edit" || props.mode === "view") &&
        props.initialData
      ) {
        form.value = {
          id: props.initialData.id,
          name: props.initialData.name,
          scans: props.initialData.scans
            ? props.initialData.scans.map((s) => ({
                name: s.name || s, // Handle if s is string or object
                amount: s.amount || "",
              }))
            : [],
        };
      } else {
        form.value = {
          name: "",
          scans: [],
        };
      }
    }
  },
);

const addScan = () => {
  form.value.scans.push({ name: "", amount: "" });
};

const removeScan = (index) => {
  form.value.scans.splice(index, 1);
};

const onClose = () => {
  if (!props.isOpen) return;
  emit("close");
};

const handleSubmit = () => {
  emit("submit", form.value);
};
</script>
