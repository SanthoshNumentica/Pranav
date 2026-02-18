<template>
  <div class="h-full flex flex-col">
    <!-- Form Body -->
    <div class="flex-1 overflow-y-auto p-6 sm:p-8 custom-scrollbar">
      <form id="patientForm" @submit.prevent="handleSubmit" class="space-y-8">
        <!-- ID Display (ReadOnly) -->
        <div v-if="patient" class="grid grid-cols-1 gap-6 pb-4">
          <div class="space-y-2">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
              >Patient ID</label
            >
            <input
              :value="patient.patient_id"
              readonly
              class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-100 bg-slate-50 text-slate-500 font-bold outline-none cursor-not-allowed"
            />
          </div>
        </div>

        <!-- Personal Info -->
        <div class="space-y-6">
          <h4
            class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2"
          >
            <div class="h-1 w-1 rounded-full bg-primary"></div>
            Personal Information
          </h4>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2 space-y-2">
              <label
                class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                >Title & Name <span class="text-rose-500">*</span></label
              >
              <div class="flex gap-2">
                <Select v-model="form.title_fk_id" v-model:open="isTitleOpen">
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
                  placeholder="Full Name"
                  class="flex-1 rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                />
              </div>
            </div>

            <div class="space-y-2">
              <label
                class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                >Guardian / Father Name</label
              >
              <input
                v-model="form.father_name"
                type="text"
                placeholder="Guardian Name"
                class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
              />
            </div>

            <div class="space-y-2">
              <label
                class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                >Age <span class="text-rose-500">*</span></label
              >
              <input
                v-model="age"
                type="number"
                placeholder="Age"
                min="0"
                max="120"
                required
                @input="handleAgeInput"
                class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
              />
            </div>

            <div class="space-y-2">
              <label
                class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                >Date of Birth <span class="text-rose-500">*</span></label
              >
              <input
                v-model="form.dob"
                type="date"
                required
                @change="handleDobChange"
                class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
              />
            </div>

            <div class="space-y-2">
              <label
                class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                >Gender <span class="text-rose-500">*</span></label
              >
              <Select
                v-model="form.gender_fk_id"
                v-model:open="isGenderOpen"
                required
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
                >WhatsApp No</label
              >
              <input
                v-model="form.whatsapp_no"
                type="tel"
                placeholder="WhatsApp (Optional)"
                class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
              />
            </div>

            <div class="md:col-span-2 space-y-2">
              <label
                class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                >Email Address</label
              >
              <input
                v-model="form.email_id"
                type="email"
                placeholder="email@example.com"
                class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
              />
            </div>
          </div>
        </div>

        <!-- Address Info -->
        <div class="space-y-6 pt-4">
          <h4
            class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2"
          >
            <div class="h-1 w-1 rounded-full bg-primary"></div>
            Address Details
          </h4>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2 space-y-2">
              <label
                class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                >Address Line <span class="text-rose-500">*</span></label
              >
              <input
                v-model="form.address"
                type="text"
                required
                placeholder="Door No, Building, Area"
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
                placeholder="6-digit Pincode"
                class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
              />
            </div>
          </div>
        </div>

        <div class="space-y-6 pt-4">
          <h4
            class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2"
          >
            <div class="h-1 w-1 rounded-full bg-primary"></div>
            Additional Info
          </h4>

          <div class="space-y-2">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
              >Remarks</label
            >
            <textarea
              v-model="form.remarks"
              rows="2"
              placeholder="Any additional notes..."
              class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all resize-none font-medium"
            ></textarea>
          </div>
        </div>

        <!-- Branch Selection (Conditional for Super Admins) -->
        <div class="space-y-6 pt-4">
          <h4
            class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2"
          >
            <div class="h-1 w-1 rounded-full bg-primary"></div>
            Branch Assignment
          </h4>

          <div class="grid grid-cols-1 gap-6">
            <div class="space-y-2">
              <label
                class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                >Assigned Branch <span class="text-rose-500">*</span></label
              >
              <Select
                v-model="form.branch_id"
                v-model:open="isBranchOpen"
                :disabled="!loggedInUserIsSuperAdmin"
                required
              >
                <SelectTrigger>
                  <SelectValue
                    :placeholder="
                      !loggedInUserIsSuperAdmin
                        ? authUser?.branch?.name
                        : 'Select Branch'
                    "
                  />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="branch in filteredBranches"
                    :key="branch.id"
                    :value="branch.id.toString()"
                  >
                    {{ branch.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
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
        @click="cancel"
        type="button"
        class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-50 transition-all active:scale-95"
      >
        Cancel
      </button>
      <button
        form="patientForm"
        type="submit"
        :disabled="loading"
        class="px-8 py-2.5 rounded-xl bg-primary text-white text-sm font-bold hover:opacity-90 transition-all active:scale-95 shadow-lg shadow-primary/20 flex items-center gap-2"
      >
        <Loader2Icon v-if="loading" class="h-4 w-4 animate-spin" />
        {{ patient ? "Update Patient" : "Create Patient" }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, computed } from "vue";
import { Loader2 as Loader2Icon } from "lucide-vue-next";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../ui/select";
import axios from "axios";
import { useAuth } from "../../composables/useAuth";

const { user: authUser } = useAuth();

const props = defineProps({
  patient: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["submit", "cancel"]);

const titles = ref([]);
const genders = ref([]);
const bloodGroups = ref([]);
const doctors = ref([]);

const initialForm = {
  title_fk_id: "",
  name: "",
  father_name: "",
  email_id: "",
  dob: "",
  mobile_no: "",
  whatsapp_no: "",
  branch_id: "",
  blood_group_fk_id: "",
  gender_fk_id: "",
  address: "",
  street: "",
  pincode: "",
  city: "",
  remarks: "",
  doctor_id: "",
};

const form = reactive({ ...initialForm });

const isTitleOpen = ref(false);
const isGenderOpen = ref(false);
const isBloodGroupOpen = ref(false);
const isDoctorOpen = ref(false);
const isBranchOpen = ref(false);
const branches = ref([]);

const loggedInUserIsSuperAdmin = computed(
  () => authUser.value?.role?.name.toLowerCase() === "super-admin",
);

const filteredBranches = computed(() => {
  if (loggedInUserIsSuperAdmin.value) return branches.value;
  if (!authUser.value?.branch_id) return [];
  return branches.value.filter(
    (b) => b.id.toString() === authUser.value.branch_id.toString(),
  );
});

const fetchMasters = async () => {
  try {
    const [tRes, gRes, bRes, brRes] = await Promise.all([
      axios.get("/api/v1/masters/titles?status=active&nopaginate=1"),
      axios.get("/api/v1/masters/genders?status=active&nopaginate=1"),
      axios.get("/api/v1/masters/blood-groups?status=active&nopaginate=1"),
      axios.get("/api/v1/masters/branches?status=active&nopaginate=1"),
    ]);
    titles.value = tRes.data.data;
    genders.value = gRes.data.data;
    bloodGroups.value = bRes.data.data;
    branches.value = Array.isArray(brRes.data.data)
      ? brRes.data.data
      : brRes.data.data?.data || [];
    const dRes = await axios.get("/api/v1/doctors?status=active&nopaginate=1");
    doctors.value = dRes.data.data;
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
      if (!loggedInUserIsSuperAdmin.value && authUser.value?.branch_id) {
        form.branch_id = authUser.value.branch_id.toString();
      }
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

const cancel = () => {
  emit("cancel");
};

const handleSubmit = () => {
  emit("submit", form);
};
</script>
