<template>
  <div class="space-y-4 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div class="bg-white rounded-[24px] border border-slate-200 p-6 shadow-soft-xl space-y-4">
      <div class="flex items-center justify-between gap-4">
        <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2">
          <div class="h-1 w-1 rounded-full bg-primary"></div>
          Patient Details
        </h4>

        <!-- Patient Search (Header Right) -->
        <div class="w-72 relative z-50">
          <Combobox v-model="selectedPatient" @update:modelValue="handlePatientSelect">
            <div class="relative">
              <div
                class="relative w-full cursor-default overflow-hidden rounded-xl bg-white text-left border border-slate-200 focus-within:ring-4 focus-within:ring-primary/5 focus-within:border-primary sm:text-sm transition-all shadow-sm">
                <ComboboxInput
                  class="w-full border-none py-2 pl-4 pr-10 text-xs leading-5 text-slate-900 focus:ring-0 outline-none font-medium placeholder:text-slate-400 disabled:bg-slate-50 disabled:cursor-not-allowed"
                  :displayValue="(patient) =>
                    patient?.name
                      ? `${patient.name} (${patient.patient_id})`
                      : ''
                    " :disabled="!canEdit" @change="query = $event.target.value"
                  placeholder="Quick Search Patient..." />
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 gap-2">
                  <button v-if="selectedPatient" type="button" @click.stop="clearPatientData"
                    class="p-0.5 rounded-full hover:bg-slate-100 transition-colors group/clear">
                    <XCircleIcon class="h-4 w-4 text-slate-400 group-hover/clear:text-rose-500 transition-colors"
                      aria-hidden="true" />
                  </button>
                  <ComboboxButton class="flex items-center">
                    <ChevronsUpDownIcon class="h-4 w-4 text-slate-400 hover:text-primary transition-colors"
                      aria-hidden="true" />
                  </ComboboxButton>
                </div>
              </div>
              <TransitionRoot leave="transition ease-in duration-100" leaveFrom="opacity-100" leaveTo="opacity-0"
                @after-leave="query = ''">
                <ComboboxOptions
                  class="absolute mt-2 max-h-80 w-full overflow-auto rounded-xl bg-white py-2 text-base shadow-xl ring-1 ring-black/5 focus:outline-none sm:text-sm z-50 divide-y divide-slate-50">
                  <div v-if="filteredPatients.length === 0 && query !== ''"
                    class="relative cursor-default select-none py-8 px-4 text-center">
                    <div class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-3">
                      <UserIcon class="h-6 w-6 text-slate-300" />
                    </div>
                    <p class="text-sm font-medium text-slate-900 mb-1">
                      No patients found
                    </p>
                    <p class="text-xs text-slate-400 mb-1 px-4">
                      We couldn't find any patient matching "{{ query }}"
                    </p>
                  </div>
                  <ComboboxOption v-for="patient in filteredPatients" as="template" :key="patient.id" :value="patient"
                    v-slot="{ selected, active }">
                    <li class="relative cursor-pointer select-none py-3 pl-4 pr-4 transition-colors" :class="{
                      'bg-primary/5': active,
                      'bg-white': !active,
                    }">
                      <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                          <!-- Name and ID -->
                          <div class="flex items-center gap-2 mb-1">
                            <span class="truncate font-bold text-sm" :class="{
                              'text-primary': selected,
                              'text-slate-900': !selected,
                            }">
                              {{ patient.name }}
                            </span>
                            <span
                              class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                              :class="selected
                                ? 'bg-primary text-white'
                                : 'bg-slate-100 text-slate-500'
                                ">
                              {{ patient.patient_id }}
                            </span>
                          </div>

                          <!-- Mobile and Address -->
                          <div class="flex flex-col gap-1">
                            <div v-if="patient.mobile_no" class="flex items-center gap-1.5 text-xs text-slate-500">
                              <PhoneIcon class="h-3 w-3" />
                              <span>{{ patient.mobile_no }}</span>
                            </div>
                            <div v-if="patient.place" class="flex items-center gap-1.5 text-xs text-slate-400">
                              <MapPinIcon class="h-3 w-3 shrink-0" />
                              <span class="truncate">{{
                                patient.place
                              }}</span>
                            </div>
                          </div>
                        </div>

                        <!-- Selected Check -->
                        <div v-if="selected" class="flex items-center self-center text-primary">
                          <div class="h-6 w-6 rounded-full bg-primary/10 flex items-center justify-center">
                            <CheckIcon class="h-3.5 w-3.5" aria-hidden="true" />
                          </div>
                        </div>
                      </div>
                    </li>
                  </ComboboxOption>
                </ComboboxOptions>
              </TransitionRoot>
            </div>
          </Combobox>
        </div>
      </div>

      <!-- Patient Details Grid -->
      <div class="space-y-4 pt-2 animate-in fade-in slide-in-from-top-2 duration-300">
        <!-- Row 1: Patient Name | Age | Gender -->
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
          <!-- Title & Patient Name -->
          <div class="md:col-span-3 space-y-1.5">
            <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">
              Title & Name <span class="text-rose-500">*</span>
            </label>
            <div class="flex gap-2">
              <Select v-model="form.title_fk_id" :disabled="!canEdit">
                <SelectTrigger
                  class="w-24 rounded-xl py-2 h-[42px] border-slate-200 bg-slate-50 focus:bg-white transition-all text-sm">
                  <SelectValue placeholder="Title" />
                </SelectTrigger>
                <SelectContent class="rounded-xl border-slate-100">
                  <SelectItem v-for="t in titles" :key="t.id" :value="t.id.toString()">
                    {{ t.title_name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <input v-model="form.patient_name" type="text" placeholder="Full Name" :disabled="!canEdit" required
                class="flex-1 rounded-xl py-2.5 px-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium disabled:opacity-70 disabled:cursor-not-allowed" />
            </div>
          </div>

          <!-- Age -->
          <div class="md:col-span-1 space-y-1.5">
            <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">
              Age
            </label>
            <input v-model.number="form.age" type="number" placeholder="Age" :disabled="!canEdit"
              class="w-full rounded-xl py-2.5 px-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium disabled:opacity-70 disabled:cursor-not-allowed" />
          </div>

          <!-- Gender -->
          <div class="md:col-span-2 space-y-1.5">
            <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">
              Gender
            </label>
            <Select v-model="form.gender_fk_id" :disabled="!canEdit">
              <SelectTrigger
                class="w-full rounded-xl py-2 h-[42px] border-slate-200 bg-slate-50 focus:bg-white transition-all text-sm">
                <SelectValue placeholder="Select Gender" />
              </SelectTrigger>
              <SelectContent class="rounded-xl border-slate-100">
                <SelectItem v-for="gender in genders" :key="gender.id" :value="gender.id.toString()">
                  {{ gender.gender_name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <!-- Row 2: Mobile Number | WhatsApp Checkbox | City / Place -->
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
          <!-- Mobile Number & WhatsApp Checkbox -->
          <div class="md:col-span-3 space-y-1.5">
            <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">
              Mobile Number <span class="text-rose-500">*</span>
            </label>
            <div class="flex items-center gap-3">
              <div class="relative flex-1">
                <input v-model="form.whatsapp_no_patient" type="tel" placeholder="Mobile Number" :disabled="!canEdit"
                  required
                  class="w-full rounded-xl py-2.5 px-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium disabled:opacity-70 disabled:cursor-not-allowed" />
              </div>
              <label class="flex items-center gap-2 cursor-pointer group shrink-0" :class="{
                'opacity-50 cursor-not-allowed pointer-events-none': !canEdit,
              }">
                <div class="relative flex items-center justify-center">
                  <input v-model="form.send_whatsapp_patient" type="checkbox" :disabled="!canEdit"
                    class="peer h-5 w-5 rounded border-2 border-slate-200 text-emerald-500 focus:ring-emerald-500/10 transition-all cursor-pointer appearance-none checked:bg-emerald-500 checked:border-emerald-500 disabled:opacity-70" />
                  <CheckIcon
                    class="absolute h-3 w-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none" />
                </div>
                <span
                  class="text-[10px] font-bold text-slate-400 group-hover:text-emerald-600 uppercase tracking-wider transition-colors">WhatsApp</span>
              </label>
            </div>
          </div>

          <!-- City / Place -->
          <div class="md:col-span-3 space-y-1.5">
            <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">
              City / Place
            </label>
            <input v-model="form.patient_place" type="text" placeholder="City" :disabled="!canEdit"
              class="w-full rounded-xl py-2.5 px-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium disabled:opacity-70 disabled:cursor-not-allowed" />
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import {
  Combobox,
  ComboboxInput,
  ComboboxButton,
  ComboboxOptions,
  ComboboxOption,
  TransitionRoot,
} from "@headlessui/vue";
import {
  User as UserIcon,
  Check as CheckIcon,
  ChevronsUpDown as ChevronsUpDownIcon,
  Phone as PhoneIcon,
  MapPin as MapPinIcon,
  Plus as PlusIcon,
  ArrowRight as ArrowRightIcon,
  Loader2 as Loader2Icon,
  XCircle as XCircleIcon,
} from "lucide-vue-next";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../../../components/ui/select";
import axios from "axios";
import { debounce } from "lodash";

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
  patients: {
    type: Array,
    default: () => [],
  },
  canEdit: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["new-patient", "next"]);

const query = ref("");
const selectedPatient = ref(null);

// Initialize selectedPatient if form has value
watch(
  () => props.form.patient_fk_id,
  (newVal) => {
    if (newVal && props.patients.length > 0) {
      const patient = props.patients.find((p) => p.id == newVal);
      if (patient) {
        selectedPatient.value = patient;
      }
    }
  },
  { immediate: true },
);

// Watch for patients prop change (in case it loads after mount)
watch(
  () => props.patients,
  (newVal) => {
    if (props.form.patient_fk_id && newVal.length > 0) {
      const patient = newVal.find((p) => p.id == props.form.patient_fk_id);
      if (patient) {
        selectedPatient.value = patient;
      }
    }
  },
);

const internalPatients = ref([]);
const isSearching = ref(false);

const searchPatients = debounce(async (val) => {
  if (!val || val.length < 2) {
    internalPatients.value = [];
    return;
  }

  isSearching.value = true;
  try {
    const response = await axios.get("/api/v1/patients", {
      params: { search: val, limit: 10 },
    });
    // The endpoint returns paginated data under data.data
    internalPatients.value = response.data.data.data || [];
  } catch (error) {
    console.error("Patient search failed", error);
  } finally {
    isSearching.value = false;
  }
}, 300);

watch(query, (newVal) => {
  searchPatients(newVal);
});

const filteredPatients = computed(() => {
  // Combine internal search results with the currently selected patient (from props.patients)
  // to ensure the selected one is always available in the list
  const combined = [...internalPatients.value];

  props.patients.forEach((p) => {
    if (!combined.find((cp) => cp.id === p.id)) {
      combined.push(p);
    }
  });

  if (query.value === "") return combined;

  const lowerQuery = query.value.toLowerCase().replace(/\s+/g, "");

  return combined.filter((patient) => {
    const nameMatch = patient.name
      ?.toLowerCase()
      .replace(/\s+/g, "")
      .includes(lowerQuery);
    const idMatch = patient.patient_id
      ?.toLowerCase()
      .includes(query.value.toLowerCase());
    const mobileMatch = patient.mobile_no
      ?.toLowerCase()
      .replace(/\s+/g, "")
      .includes(lowerQuery);

    return nameMatch || idMatch || mobileMatch;
  });
});

const isSaving = ref(false);

const titles = ref([]);
const fetchTitles = async () => {
  try {
    const response = await axios.get("/api/v1/masters/titles?status=active&nopaginate=1");
    titles.value = response.data.data;
  } catch (err) {
    console.error("Failed to fetch titles", err);
  }
};

onMounted(fetchTitles);

const genders = [
  { id: 1, gender_name: "MALE" },
  { id: 2, gender_name: "FEMALE" },
  { id: 3, gender_name: "OTHER" }
];

const handleNext = async () => {
  // If a patient is selected, sync any edited details before moving on
  if (props.form.patient_fk_id && props.canEdit) {
    isSaving.value = true;
    try {
      const payload = {};
      if (props.form.patient_name) payload.name = props.form.patient_name;
      if (props.form.title_fk_id) payload.title_fk_id = props.form.title_fk_id;
      if (props.form.patient_place !== undefined)
        payload.place = props.form.patient_place;
      if (props.form.whatsapp_no_patient)
        payload.whatsapp_no = props.form.whatsapp_no_patient;
      if (props.form.gender_fk_id)
        payload.gender_fk_id = props.form.gender_fk_id;
      if (props.form.age)
        payload.age = props.form.age;

      if (Object.keys(payload).length > 0) {
        await axios.put(
          `/api/v1/patients/${props.form.patient_fk_id}`,
          payload,
        );
      }
    } catch (error) {
      console.error("Failed to update patient details", error);
    } finally {
      isSaving.value = false;
    }
  }
  emit("next");
};

const clearPatientData = () => {
  props.form.patient_fk_id = "";
  props.form.title_fk_id = "";
  props.form.patient_name = "";
  props.form.whatsapp_no_patient = "";
  props.form.gender_fk_id = "";
  props.form.age = "";
  props.form.patient_place = "";
  selectedPatient.value = null;
  query.value = "";
};

const handlePatientSelect = (patient) => {
  if (patient) {
    props.form.patient_fk_id = patient.id;
    props.form.title_fk_id = patient.title_fk_id ? String(patient.title_fk_id) : "";
    props.form.patient_name = patient.name || "";
    props.form.patient_place = patient.place || "";

    // Auto-fill gender mapping gender_fk_id
    if (patient.gender_fk_id) {
      props.form.gender_fk_id = String(patient.gender_fk_id);
    } else {
      props.form.gender_fk_id = "";
    }

    // Auto-fill mobile number
    if (patient.mobile_no) {
      props.form.whatsapp_no_patient = patient.mobile_no;
    }

    // Calculate age from DOB
    if (patient.dob) {
      const birthDate = new Date(patient.dob);
      const diff = Date.now() - birthDate.getTime();
      const ageDate = new Date(diff);
      props.form.age = Math.abs(ageDate.getUTCFullYear() - 1970);
    } else {
      props.form.age = "";
    }
  } else {
    clearPatientData();
  }
};
</script>
