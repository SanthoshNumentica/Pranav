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
              class="relative transform overflow-hidden rounded-[32px] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-200 flex flex-col max-h-[90vh] sm:max-h-[85vh]"
            >
              <!-- Header -->
              <div
                class="relative bg-primary px-6 py-8 text-white overflow-hidden shrink-0"
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
                        {{ referer ? "Edit Referer" : "Add New Referer" }}
                      </h3>
                      <p class="text-sm font-medium mt-1 opacity-90">
                        Manage referer details.
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
                class="p-6 sm:p-8 overflow-y-auto flex-grow custom-scrollbar"
              >
                <form
                  id="refererForm"
                  @submit.prevent="handleSubmit"
                  class="space-y-6"
                >
                  <div class="space-y-2">
                    <label
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                    >
                      Referer Type <span class="text-rose-500">*</span>
                    </label>
                    <Select
                      v-model="form.referer_type_id"
                      v-model:open="isTypeOpen"
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Select Type" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem
                          v-for="t in refererTypes"
                          :key="t.id"
                          :value="t.id.toString()"
                        >
                          {{ t.name }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="space-y-2 md:col-span-1">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >
                        Title
                      </label>
                      <Select
                        v-model="form.title_id"
                        v-model:open="isTitleOpen"
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Select" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem
                            v-for="t in titles"
                            :key="t.id"
                            :value="t.id.toString()"
                          >
                            {{ t.title_name }}
                          </SelectItem>
                        </SelectContent>
                      </Select>
                    </div>

                    <div class="space-y-2 md:col-span-3">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >
                        Name <span class="text-rose-500">*</span>
                      </label>
                      <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Referer Name"
                        class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                      />
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >
                        Mobile No <span class="text-rose-500">*</span>
                      </label>
                      <input
                        v-model="form.mobile_no"
                        type="tel"
                        required
                        placeholder="Mobile Number"
                        class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                      />
                    </div>
                    <div class="space-y-2">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >
                        Email Address
                      </label>
                      <input
                        v-model="form.email_id"
                        type="email"
                        placeholder="Email"
                        class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                      />
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >
                        Hospital Name
                      </label>
                      <input
                        v-model="form.hospital_name"
                        type="text"
                        placeholder="Hospital Name"
                        class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                      />
                    </div>
                    <div class="space-y-2">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >
                        Hospital ID
                      </label>
                      <input
                        v-model="form.hospital_id"
                        type="text"
                        placeholder="Hospital ID"
                        class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                      />
                    </div>
                  </div>

                  <div class="space-y-2">
                    <label
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                    >
                      Place
                    </label>
                    <input
                      v-model="form.place"
                      type="text"
                      placeholder="City / Place"
                      class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                    />
                  </div>
                </form>
              </div>

              <!-- Footer Actions -->
              <div
                class="bg-slate-50 px-6 py-4 sm:px-8 border-t border-slate-200 flex items-center justify-end gap-3 shrink-0 rounded-b-[32px]"
              >
                <button
                  @click.stop="close"
                  type="button"
                  class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-50 transition-all active:scale-95"
                >
                  Cancel
                </button>
                <button
                  form="refererForm"
                  type="submit"
                  :disabled="loading"
                  class="px-8 py-2.5 rounded-xl bg-primary text-white text-sm font-bold hover:opacity-90 transition-all active:scale-95 shadow-lg shadow-primary/20 flex items-center gap-2"
                >
                  <Loader2Icon v-if="loading" class="h-4 w-4 animate-spin" />
                  {{ referer ? "Update Referer" : "Create Referer" }}
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
  X as XIcon,
  Loader2 as Loader2Icon,
  User as UserIcon,
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
  referer: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["close", "saved"]);

const loading = ref(false);
const refererTypes = ref([]);
const titles = ref([]);

const initialForm = {
  referer_type_id: "",
  title_id: "",
  name: "",
  mobile_no: "",
  email_id: "",
  place: "",
  hospital_name: "",
  hospital_id: "",
};

const form = reactive({ ...initialForm });
const isTypeOpen = ref(false);
const isTitleOpen = ref(false);

const fetchMasters = async () => {
  try {
    const response = await axios.get(
      "/api/v1/masters/referer-types?status=active&nopaginate=1",
    );
    refererTypes.value = response.data.data;
    const titleResponse = await axios.get(
      "/api/v1/masters/titles?status=active&nopaginate=1",
    );
    titles.value = titleResponse.data.data;
  } catch (err) {
    console.error("Failed to fetch referer types", err);
  }
};

onMounted(fetchMasters);

watch(
  () => props.referer,
  (newVal) => {
    if (newVal) {
      Object.keys(initialForm).forEach((key) => {
        let value = newVal[key];
        if (
          key.endsWith("_id") &&
          value !== null &&
          value !== undefined &&
          typeof value === "number"
        ) {
          value = value.toString();
        }
        form[key] = value || "";
      });
    } else {
      Object.assign(form, initialForm);
    }
  },
  { immediate: true },
);

// Auto-select title based on referer type
// Auto-select title based on referer type
watch(
  () => form.referer_type_id,
  (newTypeId) => {
    if (!props.referer && newTypeId) {
      // Only auto-select for new referers or if manually changed
      const selectedType = refererTypes.value.find(
        (t) => t.id.toString() === newTypeId.toString(),
      );
      if (selectedType) {
        let targetTitle = null;
        const typeName = selectedType.name.toLowerCase().trim();
        const typeSlug = selectedType.slug
          ? selectedType.slug.toLowerCase().trim()
          : "";

        if (typeSlug === "doctor" || typeName === "doctor") {
          targetTitle = titles.value.find((t) => {
            const tName = t.title_name.toLowerCase().replace(".", "").trim();
            return tName === "dr" || tName === "doctor";
          });
        } else {
          targetTitle = titles.value.find((t) => {
            const tName = t.title_name.toLowerCase().replace(".", "").trim();
            return tName === "mr";
          });
        }

        if (targetTitle) {
          form.title_id = targetTitle.id.toString();
        }
      }
    }
  },
);

const close = () => {
  if (isTypeOpen.value || isTitleOpen.value) return;
  if (!props.isOpen) return;
  emit("close");
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    let response;
    if (props.referer) {
      response = await axios.put(`/api/v1/referers/${props.referer.id}`, form);
    } else {
      response = await axios.post("/api/v1/referers", form);
    }
    addToast({
      title: "Success",
      description: `Referer ${
        props.referer ? "updated" : "created"
      } successfully.`,
      variant: "success",
    });
    emit("saved", response.data.data);
    close();
  } catch (err) {
    console.error("Failed to save referer", err);
    let errorDescription = "Failed to save referer. Please check your data.";

    if (err.response?.status === 422 && err.response?.data?.errors) {
      const errors = err.response.data.errors;
      errorDescription = Object.values(errors).flat().join(" ");
    } else if (err.response?.data?.message) {
      errorDescription = err.response.data.message;
    }

    addToast({
      title: "Error",
      description: errorDescription,
      variant: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 20px;
}
</style>
