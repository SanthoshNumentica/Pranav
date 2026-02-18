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
                      <BuildingIcon class="h-6 w-6 text-white" />
                    </div>
                    <div>
                      <h3 class="text-xl font-bold tracking-tight">
                        {{ branch ? "Edit Branch" : "Add New Branch" }}
                      </h3>
                      <p class="text-sm font-medium mt-1 opacity-90">
                        Manage organization branches and locations.
                      </p>
                    </div>
                  </div>
                  <button
                    @click.stop="close"
                    class="p-2 rounded-xl hover:bg-white/10 transition-colors"
                  >
                    <XIcon class="h-5 w-5 text-white" />
                  </button>
                </div>
              </div>

              <!-- Form Body -->
              <div
                class="flex-1 overflow-y-auto p-6 sm:p-8 custom-scrollbar space-y-6"
              >
                <form
                  id="branchForm"
                  @submit.prevent="handleSubmit"
                  class="space-y-6"
                >
                  <div class="space-y-2">
                    <label
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >Branch Name <span class="text-rose-500">*</span></label
                    >
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      placeholder="e.g. Main Hospital Branch"
                      class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                    />
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                        >Branch Code</label
                      >
                      <input
                        v-model="form.code"
                        type="text"
                        readonly
                        placeholder="e.g. MH01"
                        class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed outline-none transition-all font-medium"
                      />
                    </div>

                    <div class="space-y-2">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                        >Status <span class="text-rose-500">*</span></label
                      >
                      <Select v-model="form.status" required>
                        <SelectTrigger>
                          <SelectValue placeholder="Select Status" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="active">Active</SelectItem>
                          <SelectItem value="inactive">Inactive</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                        >Email Address</label
                      >
                      <input
                        v-model="form.email"
                        type="email"
                        placeholder="branch@example.com"
                        class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                      />
                    </div>

                    <div class="space-y-2">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                        >Phone Number</label
                      >
                      <input
                        v-model="form.phone"
                        type="text"
                        placeholder="+91 ..."
                        class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                      />
                    </div>
                  </div>

                  <div class="space-y-2">
                    <label
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >Address</label
                    >
                    <textarea
                      v-model="form.address"
                      rows="3"
                      placeholder="Enter complete branch address..."
                      class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium resize-none"
                    ></textarea>
                  </div>
                </form>
              </div>

              <!-- Footer Actions -->
              <div
                class="p-6 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0"
              >
                <button
                  @click.stop="close"
                  type="button"
                  class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-50 transition-all active:scale-95"
                >
                  Cancel
                </button>
                <button
                  form="branchForm"
                  type="submit"
                  :disabled="loading"
                  class="px-8 py-2.5 rounded-xl bg-primary text-white text-sm font-bold hover:opacity-90 transition-all active:scale-95 shadow-lg shadow-primary/20 flex items-center gap-2"
                >
                  <Loader2Icon v-if="loading" class="h-4 w-4 animate-spin" />
                  {{ branch ? "Update Branch" : "Create Branch" }}
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
import { ref, reactive, watch, onMounted } from "vue";
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import {
  Building2 as BuildingIcon,
  X as XIcon,
  Loader2 as Loader2Icon,
} from "lucide-vue-next";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../ui/select";
import axios from "axios";
import { useToast } from "../../composables/useToast";

const { addToast } = useToast();

const props = defineProps({
  isOpen: Boolean,
  branch: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["close", "saved"]);

const fetchNextCode = async () => {
  if (props.branch) return;
  try {
    const response = await axios.get("/api/v1/branches/next-code");
    if (response.data.success) {
      form.code = response.data.data;
    }
  } catch (err) {
    console.error("Failed to fetch next code", err);
  }
};

onMounted(() => {
  if (!props.branch) {
    fetchNextCode();
  }
});

watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal && !props.branch) {
      fetchNextCode();
    }
  },
);

const loading = ref(false);

const initialForm = {
  name: "",
  code: "",
  email: "",
  phone: "",
  address: "",
  status: "active",
};

const form = reactive({ ...initialForm });

watch(
  () => props.branch,
  (newVal) => {
    if (newVal) {
      form.name = newVal.name || "";
      form.code = newVal.code || "";
      form.email = newVal.email || "";
      form.phone = newVal.phone || "";
      form.address = newVal.address || "";
      form.status = newVal.status || "active";
    } else {
      Object.assign(form, initialForm);
      fetchNextCode();
    }
  },
  { immediate: true },
);

const close = () => {
  if (!props.isOpen) return;
  emit("close");
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    if (props.branch) {
      await axios.put(`/api/v1/branches/${props.branch.id}`, form);
    } else {
      await axios.post("/api/v1/branches", form);
    }
    addToast({
      title: "Success",
      description: `Branch ${props.branch ? "updated" : "created"} successfully.`,
      variant: "success",
    });
    emit("saved");
    emit("close");
  } catch (err) {
    console.error("Failed to save branch", err);
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors;
      Object.keys(errors).forEach((key) => {
        errors[key].forEach((msg) => {
          addToast({
            title: "Validation Error",
            description: msg,
            variant: "error",
          });
        });
      });
    } else {
      addToast({
        title: "Error",
        description:
          err.response?.data?.message ||
          "Failed to save branch. Please check your data.",
        variant: "error",
      });
    }
  } finally {
    loading.value = false;
  }
};
</script>
