<template>
  <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div
      class="bg-white rounded-[32px] border border-slate-200 p-8 shadow-soft-xl space-y-6"
    >
      <div class="flex items-center gap-2 mb-2">
        <h4
          class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2"
        >
          <div class="h-1 w-1 rounded-full bg-primary"></div>
          Patient Details
        </h4>
      </div>

      <!-- Patient Selection -->
      <div class="space-y-4 pt-2">
        <div class="space-y-2">
          <label
            class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
          >
            Patient <span class="text-rose-500">*</span>
          </label>
          <div class="flex gap-3">
            <div class="relative flex-1">
              <Combobox
                v-model="selectedPatient"
                @update:modelValue="handlePatientSelect"
              >
                <div class="relative mt-1">
                  <div
                    class="relative w-full cursor-default overflow-hidden rounded-2xl bg-white text-left border border-slate-200 focus-within:ring-4 focus-within:ring-primary/5 focus-within:border-primary sm:text-sm transition-all shadow-sm"
                  >
                    <ComboboxInput
                      class="w-full border-none py-3.5 pl-4 pr-10 text-sm leading-5 text-slate-900 focus:ring-0 outline-none font-medium placeholder:text-slate-400"
                      :displayValue="
                        (patient) =>
                          patient?.name
                            ? `${patient.name} (${patient.patient_id})`
                            : ''
                      "
                      @change="query = $event.target.value"
                      placeholder="Search by Name, ID or Mobile Number..."
                    />
                    <ComboboxButton
                      class="absolute inset-y-0 right-0 flex items-center pr-3"
                    >
                      <ChevronsUpDownIcon
                        class="h-5 w-5 text-slate-400 hover:text-primary transition-colors"
                        aria-hidden="true"
                      />
                    </ComboboxButton>
                  </div>
                  <TransitionRoot
                    leave="transition ease-in duration-100"
                    leaveFrom="opacity-100"
                    leaveTo="opacity-0"
                    @after-leave="query = ''"
                  >
                    <ComboboxOptions
                      class="absolute mt-2 max-h-80 w-full overflow-auto rounded-2xl bg-white py-2 text-base shadow-xl ring-1 ring-black/5 focus:outline-none sm:text-sm z-50 divide-y divide-slate-50"
                    >
                      <div
                        v-if="filteredPatients.length === 0 && query !== ''"
                        class="relative cursor-default select-none py-8 px-4 text-center"
                      >
                        <div
                          class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-3"
                        >
                          <UserIcon class="h-6 w-6 text-slate-300" />
                        </div>
                        <p class="text-sm font-medium text-slate-900 mb-1">
                          No patients found
                        </p>
                        <p class="text-xs text-slate-400 mb-6 px-4">
                          We couldn't find any patient matching "{{ query }}"
                        </p>
                        <button
                          type="button"
                          @click="$emit('new-patient')"
                          class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white text-xs font-bold shadow-lg shadow-primary/20 hover:opacity-90 transition-all active:scale-95"
                        >
                          <PlusIcon class="h-3.5 w-3.5" />
                          Add New Patient
                        </button>
                      </div>
                      <ComboboxOption
                        v-for="patient in filteredPatients"
                        as="template"
                        :key="patient.id"
                        :value="patient"
                        v-slot="{ selected, active }"
                      >
                        <li
                          class="relative cursor-pointer select-none py-3 pl-4 pr-4 transition-colors"
                          :class="{
                            'bg-primary/5': active,
                            'bg-white': !active,
                          }"
                        >
                          <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                              <!-- Name and ID -->
                              <div class="flex items-center gap-2 mb-1">
                                <span
                                  class="truncate font-bold text-sm"
                                  :class="{
                                    'text-primary': selected,
                                    'text-slate-900': !selected,
                                  }"
                                >
                                  {{ patient.name }}
                                </span>
                                <span
                                  class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                  :class="
                                    selected
                                      ? 'bg-primary text-white'
                                      : 'bg-slate-100 text-slate-500'
                                  "
                                >
                                  {{ patient.patient_id }}
                                </span>
                              </div>

                              <!-- Mobile and Address -->
                              <div class="flex flex-col gap-1">
                                <div
                                  v-if="patient.mobile_no"
                                  class="flex items-center gap-1.5 text-xs text-slate-500"
                                >
                                  <PhoneIcon class="h-3 w-3" />
                                  <span>{{ patient.mobile_no }}</span>
                                </div>
                                <div
                                  v-if="patient.place"
                                  class="flex items-center gap-1.5 text-xs text-slate-400"
                                >
                                  <MapPinIcon class="h-3 w-3 shrink-0" />
                                  <span class="truncate">{{
                                    patient.place
                                  }}</span>
                                </div>
                              </div>
                            </div>

                            <!-- Selected Check -->
                            <div
                              v-if="selected"
                              class="flex items-center self-center text-primary"
                            >
                              <div
                                class="h-6 w-6 rounded-full bg-primary/10 flex items-center justify-center"
                              >
                                <CheckIcon
                                  class="h-3.5 w-3.5"
                                  aria-hidden="true"
                                />
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

            <!-- New Patient Button -->
            <button
              type="button"
              @click="$emit('new-patient')"
              class="mt-1 h-[48px] px-6 rounded-xl bg-primary/5 text-primary border border-primary/10 font-bold text-sm hover:bg-primary hover:text-white hover:border-primary transition-all active:scale-95 flex items-center gap-2 whitespace-nowrap group shadow-sm shadow-primary/5"
            >
              <div
                class="h-6 w-6 rounded-lg bg-primary/10 group-hover:bg-white/20 flex items-center justify-center transition-colors"
              >
                <PlusIcon class="h-4 w-4" />
              </div>
              New Patient
            </button>
          </div>
        </div>

        <!-- Patient Details Grid -->
        <div
          v-if="form.patient_fk_id"
          class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 animate-in fade-in slide-in-from-top-2 duration-300"
        >
          <!-- Patient Name -->
          <div class="space-y-2">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
            >
              Patient Name
            </label>
            <input
              v-model="form.patient_name"
              type="text"
              placeholder="Name"
              class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
            />
          </div>

          <!-- WhatsApp Number -->
          <div class="space-y-2">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
            >
              WhatsApp Number
            </label>
            <div class="flex gap-2">
              <input
                v-model="form.whatsapp_no_patient"
                type="tel"
                placeholder="WhatsApp No"
                class="flex-1 rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
              />
              <label
                class="flex items-center gap-2 px-4 rounded-2xl border border-slate-200 bg-slate-50 cursor-pointer hover:bg-white hover:border-emerald-500/30 transition-all active:scale-95 group shrink-0"
              >
                <div class="relative flex items-center justify-center">
                  <input
                    v-model="form.send_whatsapp_patient"
                    type="checkbox"
                    class="peer h-5 w-5 rounded-lg border-2 border-slate-200 text-emerald-500 focus:ring-emerald-500/10 transition-all cursor-pointer appearance-none checked:bg-emerald-500 checked:border-emerald-500"
                  />
                  <CheckIcon
                    class="absolute h-3 w-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"
                  />
                </div>
                <span
                  class="text-xs font-bold text-slate-500 group-hover:text-emerald-600 transition-colors"
                  >WhatsApp</span
                >
              </label>
            </div>
          </div>

          <!-- Place -->
          <div class="space-y-2 md:col-span-2">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
            >
              Place / City
            </label>
            <input
              v-model="form.patient_place"
              type="text"
              placeholder="City"
              class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
            />
          </div>
        </div>

        <!-- Next Action -->
        <div
          v-if="form.patient_fk_id"
          class="flex justify-end pt-6 border-t border-slate-100 animate-in fade-in slide-in-from-top-2 duration-500"
        >
          <button
            type="button"
            @click="$emit('next')"
            class="group flex items-center gap-2 px-8 py-2.5 bg-primary text-white rounded-xl font-bold text-sm shadow-lg shadow-primary/20 hover:opacity-90 active:scale-95 transition-all"
          >
            Next: Referer Details
            <ArrowRightIcon
              class="h-4 w-4 group-hover:translate-x-1 transition-transform"
            />
          </button>
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
} from "lucide-vue-next";
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
});

defineEmits(["new-patient", "next"]);

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

const handlePatientSelect = (patient) => {
  if (patient) {
    props.form.patient_fk_id = patient.id;
    // Optionally auto-fill whatsapp if available
    if (patient.mobile_no) {
      props.form.whatsapp_no_patient = patient.mobile_no;
    }
  }
};
</script>
