<template>
  <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div class="bg-white rounded-[24px] border border-slate-200 p-6 shadow-soft-xl space-y-4">
      <div class="flex items-center justify-between gap-4">
        <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2">
          <div class="h-1 w-1 rounded-full bg-primary"></div>
          Referer Details
        </h4>

        <!-- Referer Search (Header Right) -->
        <div class="w-72 relative z-50">
          <Combobox v-model="selectedReferer" @update:modelValue="handleRefererSelect">
            <div class="relative">
              <div
                class="relative w-full cursor-default overflow-hidden rounded-xl bg-white text-left border border-slate-200 focus-within:ring-4 focus-within:ring-primary/5 focus-within:border-primary sm:text-sm transition-all shadow-sm">
                <ComboboxInput
                  class="w-full border-none py-2 pl-4 pr-10 text-xs leading-5 text-slate-900 focus:ring-0 outline-none font-medium placeholder:text-slate-400 disabled:bg-slate-50 disabled:cursor-not-allowed"
                  :displayValue="(referer) => referer?.name || ''" :disabled="!canEdit"
                  @change="query = $event.target.value" placeholder="Quick Search Referer..." />
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 gap-2">
                  <button v-if="selectedReferer" type="button" @click.stop="clearRefererData"
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
                  <div v-if="filteredReferers.length === 0 && query !== ''"
                    class="relative cursor-default select-none py-8 px-4 text-center">
                    <div class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-3">
                      <SearchIcon class="h-6 w-6 text-slate-300" />
                    </div>
                    <p class="text-sm font-medium text-slate-900 mb-1">
                      No referers found
                    </p>
                    <p class="text-xs text-slate-400 mb-1 px-4">
                      We couldn't find any referer matching "{{ query }}"
                    </p>
                  </div>
                  <ComboboxOption v-for="referer in filteredReferers" as="template" :key="referer.id" :value="referer"
                    v-slot="{ selected, active }">
                    <li class="relative cursor-pointer select-none py-3 pl-4 pr-4 transition-colors" :class="{
                      'bg-primary/5': active,
                      'bg-white': !active,
                    }">
                      <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                          <div class="flex items-center gap-2 mb-1">
                            <span class="truncate font-bold text-sm" :class="{
                              'text-primary': selected,
                              'text-slate-900': !selected,
                            }">
                              {{ referer.name }}
                            </span>
                            <span v-if="referer.referer_type?.name"
                              class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-500">
                              {{ referer.referer_type.name }}
                            </span>
                          </div>
                          <div class="flex flex-col gap-1">
                            <div v-if="referer.mobile_no" class="flex items-center gap-1.5 text-xs text-slate-500">
                              <PhoneIcon class="h-3 w-3" />
                              <span>{{ referer.mobile_no }}</span>
                            </div>
                            <div v-if="referer.hospital_name" class="flex items-center gap-1.5 text-xs text-slate-400">
                              <MapPinIcon class="h-3 w-3 shrink-0" />
                              <span class="truncate">{{
                                referer.hospital_name
                              }}</span>
                            </div>
                          </div>
                        </div>
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

      <!-- Referer Details Grid -->
      <div class="space-y-4 pt-2 animate-in fade-in slide-in-from-top-2 duration-300">
        <!-- Row 1: Title & Name | Referer Type -->
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
          <!-- Title & Referer Name -->
          <div class="md:col-span-3 space-y-1.5">
            <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">
              Title & Name <span class="text-rose-500">*</span>
            </label>
            <div class="flex gap-2">
              <Select v-model="form.title_id" :disabled="!canEdit">
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
              <input v-model="form.referer_name" type="text" placeholder="Referer Name" :disabled="!canEdit" required
                class="flex-1 rounded-xl py-2.5 px-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium disabled:opacity-70 disabled:cursor-not-allowed" />
            </div>
          </div>

          <!-- Referer Type -->
          <div class="md:col-span-3 space-y-1.5">
            <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">
              Referer Type <span class="text-rose-500">*</span>
            </label>
            <Select v-model="form.referer_type_id" :disabled="!canEdit">
              <SelectTrigger
                class="w-full rounded-xl py-2 h-[42px] border-slate-200 bg-slate-50 focus:bg-white transition-all text-sm">
                <SelectValue placeholder="Select Type" />
              </SelectTrigger>
              <SelectContent class="rounded-xl border-slate-100">
                <SelectItem v-for="t in refererTypes" :key="t.id" :value="t.id.toString()">
                  {{ t.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <!-- Row 2: Mobile Number | WhatsApp Checkbox | Hospital Name -->
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
          <!-- Mobile Number & WhatsApp Checkbox -->
          <div class="md:col-span-3 space-y-1.5">
            <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">
              Mobile Number <span class="text-rose-500">*</span>
            </label>
            <div class="flex items-center gap-3">
              <div class="relative flex-1">
                <input v-model="form.whatsapp_no_referer" type="tel" placeholder="Mobile Number" :disabled="!canEdit"
                  required
                  class="w-full rounded-xl py-2.5 px-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium disabled:opacity-70 disabled:cursor-not-allowed" />
              </div>
              <label class="flex items-center gap-2 cursor-pointer group shrink-0" :class="{
                'opacity-50 cursor-not-allowed pointer-events-none': !canEdit,
              }">
                <div class="relative flex items-center justify-center">
                  <input v-model="form.send_whatsapp_referer" type="checkbox" :disabled="!canEdit"
                    class="peer h-5 w-5 rounded border-2 border-slate-200 text-emerald-500 focus:ring-emerald-500/10 transition-all cursor-pointer appearance-none checked:bg-emerald-500 checked:border-emerald-500 disabled:opacity-70" />
                  <CheckIcon
                    class="absolute h-3 w-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none" />
                </div>
                <span
                  class="text-[10px] font-bold text-slate-400 group-hover:text-emerald-600 uppercase tracking-wider transition-colors">WhatsApp</span>
              </label>
            </div>
          </div>

          <!-- Hospital Name -->
          <div class="md:col-span-3 space-y-1.5">
            <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">
              Hospital Name
            </label>
            <input v-model="form.hospital_name" type="text" placeholder="Hospital Name" :disabled="!canEdit"
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
  Search as SearchIcon,
  Check as CheckIcon,
  ChevronsUpDown as ChevronsUpDownIcon,
  Plus as PlusIcon,
  Phone as PhoneIcon,
  MapPin as MapPinIcon,
  Loader2 as Loader2Icon,
  XCircle as XCircleIcon,
} from "lucide-vue-next";
import axios from "axios";
import { debounce } from "lodash";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../../../components/ui/select";

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
  referers: {
    type: Array,
    default: () => [],
  },
  canEdit: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["new-referer"]);

const query = ref("");
const selectedReferer = ref(null);

// Initialize selectedReferer if form has value
watch(
  () => props.form.referer_fk_id,
  (newVal) => {
    if (newVal && props.referers.length > 0) {
      const referer = props.referers.find(
        (d) => String(d.id) === String(newVal),
      );
      if (referer) {
        selectedReferer.value = referer;
      }
    }
  },
  { immediate: true },
);

// Watch for referers prop change
watch(
  () => props.referers,
  (newVal) => {
    if (props.form.referer_fk_id && newVal.length > 0) {
      const referer = newVal.find(
        (d) => String(d.id) === String(props.form.referer_fk_id),
      );
      if (referer) {
        selectedReferer.value = referer;
      }
    }
  },
);

const internalReferers = ref([]);
const isSearching = ref(false);

const searchReferers = debounce(async (val) => {
  if (!val || val.length < 2) {
    internalReferers.value = [];
    return;
  }

  isSearching.value = true;
  try {
    const response = await axios.get("/api/v1/referers", {
      params: { search: val, limit: 10 },
    });
    // The endpoint returns paginated data under data.data
    internalReferers.value = response.data.data.data || [];
  } catch (error) {
    console.error("Referer search failed", error);
  } finally {
    isSearching.value = false;
  }
}, 300);

watch(query, (newVal) => {
  searchReferers(newVal);
});

const filteredReferers = computed(() => {
  // Combine internal search results with the currently selected referer (from props.referers)
  const combined = [...internalReferers.value];

  props.referers.forEach((r) => {
    if (!combined.find((cr) => cr.id === r.id)) {
      combined.push(r);
    }
  });

  if (query.value === "") return combined;

  const lowerQuery = query.value.toLowerCase().replace(/\s+/g, "");

  return combined.filter((referer) => {
    const nameMatch = referer.name
      ?.toLowerCase()
      .replace(/\s+/g, "")
      .includes(lowerQuery);
    const mobileMatch = referer.mobile_no
      ?.toLowerCase()
      .replace(/\s+/g, "")
      .includes(lowerQuery);
    const hospitalMatch = referer.hospital_name
      ?.toLowerCase()
      .includes(query.value.toLowerCase());

    return nameMatch || mobileMatch || hospitalMatch;
  });
});

const isSaving = ref(false);

const refererTypes = ref([]);
const titles = ref([]);

const fetchMasters = async () => {
  try {
    const [typeRes, titleRes] = await Promise.all([
      axios.get("/api/v1/masters/referer-types?status=active&nopaginate=1"),
      axios.get("/api/v1/masters/titles?status=active&nopaginate=1"),
    ]);
    refererTypes.value = typeRes.data.data;
    titles.value = titleRes.data.data;
  } catch (err) {
    console.error("Failed to fetch referer masters", err);
  }
};

onMounted(fetchMasters);

const clearRefererData = () => {
  props.form.referer_fk_id = "";
  props.form.title_id = "";
  props.form.referer_type_id = "";
  props.form.referer_name = "";
  props.form.whatsapp_no_referer = "";
  props.form.hospital_name = "";
  props.form.hospital_id = "";
  selectedReferer.value = null;
  query.value = "";
};

const handleRefererSelect = (referer) => {
  if (referer) {
    props.form.referer_fk_id = referer.id.toString();
    props.form.title_id = referer.title_id ? String(referer.title_id) : "";
    props.form.referer_type_id = referer.referer_type_id ? String(referer.referer_type_id) : "";
    props.form.referer_name = referer.name || "";
    props.form.whatsapp_no_referer = referer.mobile_no || "";
    props.form.hospital_name = referer.hospital_name || "";
    props.form.hospital_id = referer.hospital_id || "";
  } else {
    clearRefererData();
  }
};
</script>
