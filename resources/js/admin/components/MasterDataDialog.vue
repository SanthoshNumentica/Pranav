<template>
  <TransitionRoot as="template" :show="isOpen">
    <Dialog as="div" class="relative z-50" @close="onClose">
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
              class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100"
            >
              <div class="bg-white px-6 py-6 border-b border-slate-100">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div
                      class="h-10 w-10 bg-primary/10 rounded-xl flex items-center justify-center"
                    >
                      <component :is="icon" class="h-5 w-5 text-primary" />
                    </div>
                    <DialogTitle
                      as="h3"
                      class="text-xl font-bold leading-6 text-slate-900"
                    >
                      {{ mode === "edit" ? `Edit ${title}` : `Add ${title}` }}
                    </DialogTitle>
                  </div>
                  <button
                    @click.stop="onClose"
                    class="text-slate-400 hover:text-slate-500 transition-colors"
                  >
                    <XIcon class="h-6 w-6" />
                  </button>
                </div>
              </div>

              <form @submit.prevent="handleSubmit">
                <div class="bg-white px-6 py-8 space-y-6">
                  <!-- Error Message -->
                  <div
                    v-if="error"
                    class="bg-rose-50 text-rose-500 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2"
                  >
                    <AlertCircleIcon class="h-4 w-4 shrink-0" />
                    <span>{{ error }}</span>
                  </div>

                  <div class="space-y-1.5">
                    <label class="text-sm font-bold text-slate-700 ml-1"
                      >{{ label }} Name</label
                    >
                    <input
                      v-model="form.name"
                      type="text"
                      :placeholder="`Enter ${label.toLowerCase()} name`"
                      class="w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm focus:ring-primary/20 focus:border-primary transition-all duration-200"
                      required
                    />
                  </div>
                </div>

                <div
                  class="bg-slate-50 px-6 py-4 flex justify-end gap-3 rounded-b-3xl"
                >
                  <button
                    type="button"
                    @click.stop="onClose"
                    class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-100 transition-all active:scale-95"
                    :disabled="loading"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    class="px-6 py-2.5 rounded-xl bg-primary text-sm font-bold text-white hover:opacity-90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center min-w-[100px] active:scale-95"
                    :disabled="loading"
                  >
                    <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
                    <span v-else>{{
                      mode === "edit" ? "Update" : "Create"
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
  Loader2,
  AlertCircle as AlertCircleIcon,
} from "lucide-vue-next";

const props = defineProps({
  isOpen: Boolean,
  mode: {
    type: String,
    default: "add",
  },
  title: String,
  label: String,
  icon: [Object, Function],
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
});

watch(
  () => props.isOpen,
  (val) => {
    if (val) {
      if (props.mode === "edit" && props.initialData) {
        form.value = {
          id: props.initialData.id,
          name: props.initialData.name || props.initialData.gender_name || "",
        };
      } else {
        form.value = {
          name: "",
        };
      }
    }
  },
);

const onClose = () => {
  if (!props.isOpen) return;
  emit("close");
};

const handleSubmit = () => {
  emit("submit", form.value);
};
</script>
