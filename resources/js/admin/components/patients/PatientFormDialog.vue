<template>
  <TransitionRoot as="template" :show="isOpen">
    <Dialog as="div" class="relative z-50" @close="() => { }">
      <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild as="template" enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <DialogPanel
              class="relative transform overflow-hidden rounded-[32px] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200 flex flex-col h-[90vh] sm:h-[80vh]">
              <!-- Header/Banner - Fixed -->
              <div class="relative bg-primary px-6 py-8 sm:px-10 text-white overflow-hidden shrink-0">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>

                <div class="relative flex items-center justify-between">
                  <div class="flex items-center gap-4">
                    <div
                      class="h-12 w-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30">
                      <UserIcon class="h-6 w-6 text-white" />
                    </div>
                    <div>
                      <h3 class="text-xl font-bold tracking-tight">
                        {{ patient ? "Edit Patient" : "Add New Patient" }}
                      </h3>
                      <p class="text-sm font-medium mt-1 opacity-90">
                        Enter patient personal and contact details.
                      </p>
                    </div>
                  </div>
                  <button @click.stop="close" class="p-2 rounded-xl hover:bg-white/10 transition-colors">
                    <XIcon class="h-5 w-5 text-white" />
                  </button>
                </div>
              </div>

              <!-- Form Body -->
              <div class="flex-1 overflow-y-auto p-6 sm:p-8 custom-scrollbar">
                <form id="patientForm" @submit.prevent="handleSubmit" class="space-y-8">
                  <!-- ID Display (ReadOnly) -->
                  <div v-if="patient" class="grid grid-cols-1 gap-6 pb-4">
                    <div class="space-y-2">
                      <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Patient
                        ID</label>
                      <input :value="patient.patient_id" readonly
                        class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-100 bg-slate-50 text-slate-500 font-bold outline-none cursor-not-allowed" />
                    </div>

                    <div class="space-y-2">
                      <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">MRN ID</label>
                      <input v-model="form.mrn_id" type="text" placeholder="MRN Number"
                        class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium" />
                    </div>
                  </div>

                  <!-- Personal Info -->
                  <div class="space-y-6">
                    <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                      <div class="h-1 w-1 rounded-full bg-primary"></div>
                      Personal Information
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div class="md:col-span-2 space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Title & Name
                          <span class="text-rose-500">*</span></label>
                        <div class="flex gap-2">
                          <Select v-model="form.title_fk_id" v-model:open="isTitleOpen">
                            <SelectTrigger class="w-24">
                              <SelectValue placeholder="Title" />
                            </SelectTrigger>
                            <SelectContent>
                              <SelectItem v-for="t in titles" :key="t.id" :value="t.id.toString()">
                                {{ t.title_name }}
                              </SelectItem>
                            </SelectContent>
                          </Select>
                          <input v-model="form.name" type="text" required placeholder="Full Name"
                            class="flex-1 rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium" />
                        </div>
                      </div>

                      <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Guardian /
                          Father Name</label>
                        <input v-model="form.father_name" type="text" placeholder="Guardian Name"
                          class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium" />
                      </div>

                      <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Age <span
                            class="text-rose-500">*</span></label>
                        <input v-model="age" type="number" placeholder="Age" min="0" max="120" required
                          @input="handleAgeInput"
                          class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium" />
                      </div>

                      <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Date of
                          Birth
                          <span class="text-rose-500">*</span></label>
                        <input v-model="form.dob" type="date" required @change="handleDobChange"
                          class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium" />
                      </div>

                      <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Gender <span
                            class="text-rose-500">*</span></label>
                        <Select v-model="form.gender_fk_id" v-model:open="isGenderOpen" required>
                          <SelectTrigger>
                            <SelectValue placeholder="Select Gender" />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem v-for="g in genders" :key="g.id" :value="g.id.toString()">
                              {{ g.gender_name }}
                            </SelectItem>
                          </SelectContent>
                        </Select>
                      </div>

                    </div>
                  </div>

                  <!-- Contact Info -->
                  <div class="space-y-6 pt-4">
                    <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                      <div class="h-1 w-1 rounded-full bg-primary"></div>
                      Contact Information
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Mobile No
                          <span class="text-rose-500">*</span></label>
                        <input v-model="form.mobile_no" type="tel" required placeholder="Mobile Number"
                          class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium" />
                      </div>

                      <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">WhatsApp
                          No</label>
                        <input v-model="form.whatsapp_no" type="tel" placeholder="WhatsApp (Optional)"
                          class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium" />
                      </div>

                      <div class="md:col-span-2 space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Email
                          Address</label>
                        <input v-model="form.email_id" type="email" placeholder="email@example.com"
                          class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium" />
                      </div>
                    </div>
                  </div>

                  <!-- Address Info -->
                  <div class="space-y-6 pt-4">
                    <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                      <div class="h-1 w-1 rounded-full bg-primary"></div>
                      Address Details
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div class="md:col-span-2 space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Place <span
                            class="text-rose-500">*</span></label>
                        <input v-model="form.place" type="text" required placeholder="City / Place"
                          class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium" />
                      </div>
                    </div>
                  </div>

                  <div class="space-y-6 pt-4">
                    <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                      <div class="h-1 w-1 rounded-full bg-primary"></div>
                      Additional Info
                    </h4>

                    <div class="space-y-2">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Remarks</label>
                      <textarea v-model="form.remarks" rows="2" placeholder="Any additional notes..."
                        class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all resize-none font-medium"></textarea>
                    </div>
                  </div>
                </form>
              </div>

              <!-- Footer Actions -->
              <div class="p-6 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
                <button @click.stop="close" type="button"
                  class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-50 transition-all active:scale-95">
                  Cancel
                </button>
                <button form="patientForm" type="submit" :disabled="loading"
                  class="px-8 py-2.5 rounded-xl bg-primary text-white text-sm font-bold hover:opacity-90 transition-all active:scale-95 shadow-lg shadow-primary/20 flex items-center gap-2">
                  <Loader2Icon v-if="loading" class="h-4 w-4 animate-spin" />
                  {{ patient ? "Update Patient" : "Create Patient" }}
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
import { ref, reactive, watch, onMounted, computed } from "vue";
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import {
  X as XIcon,
  User as UserIcon,
  Loader2 as Loader2Icon,
  MapPin as MapPinIcon,
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
import { useAuth } from "../../composables/useAuth";

const { addToast } = useToast();
const { user: authUser } = useAuth();

const props = defineProps({
  isOpen: Boolean,
  patient: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["close", "saved"]);

const loading = ref(false);
const titles = ref([]);
const genders = ref([]);

const referers = ref([]);

const initialForm = {
  mrn_id: "",
  title_fk_id: "",
  name: "",
  father_name: "",
  email_id: "",
  dob: "",
  mobile_no: "",
  whatsapp_no: "",


  gender_fk_id: "",

  place: "",
  remarks: "",
  referer_id: "",
};

const form = reactive({ ...initialForm });

const isTitleOpen = ref(false);
const isGenderOpen = ref(false);

const isRefererOpen = ref(false);

const loggedInUserIsSuperAdmin = computed(
  () => authUser.value?.role?.name.toLowerCase() === "super-admin",
);

const fetchMasters = async () => {
  try {
    const [tRes, gRes] = await Promise.all([
      axios.get("/api/v1/masters/titles?status=active&nopaginate=1"),
      axios.get("/api/v1/masters/genders?status=active&nopaginate=1"),
    ]);
    titles.value = tRes.data.data;
    genders.value = gRes.data.data;

    const dRes = await axios.get(
      "/api/v1/masters/referers?status=active&nopaginate=1",
    );
    referers.value = dRes.data.data;
  } catch (err) {
    console.error("Failed to fetch masters", err);
  }
};

onMounted(fetchMasters);

const age = ref("");

watch(
  () => props.patient,
  (newVal) => {
    if (newVal) {
      Object.keys(initialForm).forEach((key) => {
        let value = newVal[key];
        if (key.endsWith("_fk_id") && value !== null && value !== undefined) {
          value = value.toString();
        }
        form[key] = value || "";
      });
      // Format date for input
      if (newVal.dob) {
        form.dob = newVal.dob.split("T")[0];
        calculateAgeFromDob(form.dob);
      }
    } else {
      Object.assign(form, initialForm);
      // Auto-assign branch for non-super-admins
      form.title_fk_id = "1"; // Default to Mr

      age.value = "";
    }
  },
  { immediate: true },
);

watch(
  () => form.mobile_no,
  (newVal, oldVal) => {
    if (!form.whatsapp_no) {
      form.whatsapp_no = newVal;
    } else if (form.whatsapp_no === oldVal) {
      form.whatsapp_no = newVal;
    }
  },
);

const handleAgeInput = () => {
  if (!age.value) return;
  const today = new Date();
  const birthDate = new Date(
    today.getFullYear() - age.value,
    today.getMonth(),
    today.getDate(),
  );
  form.dob = birthDate.toISOString().split("T")[0];
};

const handleDobChange = () => {
  if (!form.dob) return;
  calculateAgeFromDob(form.dob);
};

const calculateAgeFromDob = (dob) => {
  if (!dob) return;
  const today = new Date();
  const birthDate = new Date(dob);
  let calculatedAge = today.getFullYear() - birthDate.getFullYear();
  const m = today.getMonth() - birthDate.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
    calculatedAge--;
  }
  age.value = calculatedAge;
};

const close = () => {
  if (isTitleOpen.value || isGenderOpen.value) return;
  if (!props.isOpen) return;
  emit("close");
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    let response;
    if (props.patient) {
      response = await axios.put(`/api/v1/patients/${props.patient.id}`, form);
    } else {
      response = await axios.post("/api/v1/patients", form);
    }
    addToast({
      title: "Success",
      description: `Patient ${props.patient ? "updated" : "created"
        } successfully.`,
      variant: "success",
    });
    emit("saved", response.data.data);
    close();
  } catch (err) {
    console.error("Failed to save patient", err);
    let errorDescription = "Failed to save patient. Please check your data.";

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
