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
              <!-- Header -->
              <div class="px-6 py-6 border-b border-slate-100 shrink-0">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div
                      class="h-10 w-10 rounded-xl bg-primary/10 flex items-center justify-center"
                    >
                      <StethoscopeIcon class="h-5 w-5 text-primary" />
                    </div>
                    <div>
                      <h3 class="text-lg font-bold text-slate-900">
                        {{ doctor ? "Edit Doctor" : "Add New Doctor" }}
                      </h3>
                      <p class="text-xs text-slate-500">
                        Manage referring clinician details.
                      </p>
                    </div>
                  </div>
                  <button
                    @click.stop="close"
                    class="p-2 rounded-xl hover:bg-slate-50 transition-colors"
                  >
                    <XIcon class="h-5 w-5 text-slate-400" />
                  </button>
                </div>
              </div>

              <!-- Form Body -->
              <div class="flex-1 overflow-y-auto p-6 sm:p-8 custom-scrollbar">
                <form
                  id="doctorForm"
                  @submit.prevent="handleSubmit"
                  class="space-y-8"
                >
                  <!-- ID Display (ReadOnly) -->
                  <div v-if="doctor" class="grid grid-cols-1 gap-6 pb-4">
                    <div class="space-y-2">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                        >Doctor ID</label
                      >
                      <input
                        :value="doctor.doctor_id"
                        readonly
                        class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-100 bg-slate-50 text-slate-500 font-bold outline-none cursor-not-allowed"
                      />
                    </div>
                  </div>

                  <!-- Professional Info -->
                  <div class="space-y-6">
                    <h4
                      class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2"
                    >
                      <div class="h-1 w-1 rounded-full bg-primary"></div>
                      Professional Information
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div class="md:col-span-2 space-y-2">
                        <label
                          class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                          >Title & Name
                          <span class="text-rose-500">*</span></label
                        >
                        <div class="flex gap-2">
                          <Select
                            v-model="form.title_fk_id"
                            v-model:open="isTitleOpen"
                          >
                            <SelectTrigger class="w-24">
                              <SelectValue placeholder="Title" />
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
                          <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Dr. Name"
                            class="flex-1 rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                          />
                        </div>
                      </div>

                      <div class="space-y-2">
                        <label
                          class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                          >Gender</label
                        >
                        <Select
                          v-model="form.gender_fk_id"
                          v-model:open="isGenderOpen"
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Select Gender" />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem
                              v-for="g in genders"
                              :key="g.id"
                              :value="g.id.toString()"
                            >
                              {{ g.gender_name }}
                            </SelectItem>
                          </SelectContent>
                        </Select>
                      </div>

                      <div class="space-y-2">
                        <label
                          class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                          >Blood Group</label
                        >
                        <Select
                          v-model="form.blood_group_fk_id"
                          v-model:open="isBloodGroupOpen"
                        >
                          <SelectTrigger>
                            <SelectValue placeholder="Select Blood Group" />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem
                              v-for="bg in bloodGroups"
                              :key="bg.id"
                              :value="bg.id.toString()"
                            >
                              {{ bg.name }}
                            </SelectItem>
                          </SelectContent>
                        </Select>
                      </div>

                      <div class="space-y-2">
                        <label
                          class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                          >Age</label
                        >
                        <input
                          v-model="age"
                          type="number"
                          placeholder="Age"
                          min="0"
                          max="120"
                          @input="handleAgeInput"
                          class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                        />
                      </div>

                      <div class="space-y-2">
                        <label
                          class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                          >Date of Birth</label
                        >
                        <input
                          v-model="form.dob"
                          type="date"
                          @change="handleDobChange"
                          class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                        />
                      </div>
                    </div>
                  </div>

                  <!-- Contact Info -->
                  <div class="space-y-6 pt-4">
                    <h4
                      class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2"
                    >
                      <div class="h-1 w-1 rounded-full bg-primary"></div>
                      Contact Information
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div class="space-y-2">
                        <label
                          class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                          >Mobile No <span class="text-rose-500">*</span></label
                        >
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
                          >Email Address</label
                        >
                        <input
                          v-model="form.email_id"
                          type="email"
                          placeholder="doctor@hospital.com"
                          class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                        />
                      </div>
                    </div>
                  </div>

                  <!-- Location Info -->
                  <div class="space-y-6 pt-4">
                    <h4
                      class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2"
                    >
                      <div class="h-1 w-1 rounded-full bg-primary"></div>
                      Clinic / Hospital Address
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div class="md:col-span-2 space-y-2">
                        <label
                          class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                          >Address Line</label
                        >
                        <input
                          v-model="form.address"
                          type="text"
                          placeholder="Clinic Name, Building, Area"
                          class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                        />
                      </div>
                      <div class="space-y-2">
                        <label
                          class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                          >Street</label
                        >
                        <input
                          v-model="form.street"
                          type="text"
                          placeholder="Street Name"
                          class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                        />
                      </div>
                      <div class="space-y-2">
                        <label
                          class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                          >City</label
                        >
                        <input
                          v-model="form.city"
                          type="text"
                          placeholder="City"
                          class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                        />
                      </div>
                      <div class="space-y-2">
                        <label
                          class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                          >Pincode</label
                        >
                        <input
                          v-model="form.pincode"
                          type="text"
                          placeholder="Pincode"
                          class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                        />
                      </div>
                    </div>
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
                  form="doctorForm"
                  type="submit"
                  :disabled="loading"
                  class="px-8 py-2.5 rounded-xl bg-primary text-white text-sm font-bold hover:opacity-90 transition-all active:scale-95 shadow-lg shadow-primary/20 flex items-center gap-2"
                >
                  <Loader2Icon v-if="loading" class="h-4 w-4 animate-spin" />
                  {{ doctor ? "Update Doctor" : "Create Doctor" }}
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
  Stethoscope as StethoscopeIcon,
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
  doctor: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["close", "saved"]);

const loading = ref(false);
const titles = ref([]);
const genders = ref([]);
const bloodGroups = ref([]);

const initialForm = {
  title_fk_id: "",
  name: "",
  gender_fk_id: "",
  blood_group_fk_id: "",
  email_id: "",
  dob: "",
  mobile_no: "",
  address: "",
  street: "",
  pincode: "",
  city: "",
};

const form = reactive({ ...initialForm });

const isTitleOpen = ref(false);
const isGenderOpen = ref(false);
const isBloodGroupOpen = ref(false);

const fetchMasters = async () => {
  try {
    const [tRes, gRes, bRes] = await Promise.all([
      axios.get("/api/v1/masters/titles?status=active&nopaginate=1"),
      axios.get("/api/v1/masters/genders?status=active&nopaginate=1"),
      axios.get("/api/v1/masters/blood-groups?status=active&nopaginate=1"),
    ]);
    titles.value = tRes.data.data;
    genders.value = gRes.data.data;
    bloodGroups.value = bRes.data.data;
  } catch (err) {
    console.error("Failed to fetch masters", err);
  }
};

onMounted(fetchMasters);

const age = ref("");

watch(
  () => props.doctor,
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
      age.value = "";
    }
  },
  { immediate: true },
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
  if (isTitleOpen.value || isGenderOpen.value || isBloodGroupOpen.value) return;
  if (!props.isOpen) return;
  emit("close");
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    if (props.doctor) {
      await axios.put(`/api/v1/doctors/${props.doctor.id}`, form);
    } else {
      await axios.post("/api/v1/doctors", form);
    }
    addToast({
      title: "Success",
      description: `Doctor ${props.doctor ? "updated" : "created"} successfully.`,
      variant: "success",
    });
    emit("saved");
    close();
  } catch (err) {
    console.error("Failed to save doctor", err);
    addToast({
      title: "Error",
      description:
        err.response?.data?.message ||
        "Failed to save doctor. Please check your data.",
      variant: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>
