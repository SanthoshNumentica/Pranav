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
          Referer Details
        </h4>
      </div>

      <!-- Referer Selection -->
      <div class="space-y-4 pt-2">
        <div class="space-y-2">
          <label
            class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
          >
            Referring Person/Doctor <span class="text-rose-500">*</span>
          </label>
          <div class="flex gap-3">
            <div class="relative flex-1">
              <Combobox
                v-model="selectedReferer"
                @update:modelValue="handleRefererSelect"
              >
                <div class="relative mt-1">
                  <div
                    class="relative w-full cursor-default overflow-hidden rounded-2xl bg-white text-left border border-slate-200 focus-within:ring-4 focus-within:ring-primary/5 focus-within:border-primary sm:text-sm transition-all shadow-sm"
                  >
                    <ComboboxInput
                      class="w-full border-none py-3.5 pl-4 pr-10 text-sm leading-5 text-slate-900 focus:ring-0 outline-none font-medium placeholder:text-slate-400"
                      :displayValue="(referer) => referer?.name || ''"
                      @change="query = $event.target.value"
                      placeholder="Search by Name, Hospital or Mobile Number..."
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
                        v-if="filteredReferers.length === 0 && query !== ''"
                        class="relative cursor-default select-none py-8 px-4 text-center"
                      >
                        <div
                          class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-3"
                        >
                          <SearchIcon class="h-6 w-6 text-slate-300" />
                        </div>
                        <p class="text-sm font-medium text-slate-900 mb-1">
                          No referers found
                        </p>
                        <p class="text-xs text-slate-400 mb-6 px-4">
                          We couldn't find any referer matching "{{ query }}"
                        </p>
                        <button
                          type="button"
                          @click="$emit('new-referer')"
                          class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white text-xs font-bold shadow-lg shadow-primary/20 hover:opacity-90 transition-all active:scale-95"
                        >
                          <PlusIcon class="h-3.5 w-3.5" />
                          Add New Referer
                        </button>
                      </div>
                      <ComboboxOption
                        v-for="referer in filteredReferers"
                        as="template"
                        :key="referer.id"
                        :value="referer"
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
                              <div class="flex items-center gap-2 mb-1">
                                <span
                                  class="truncate font-bold text-sm"
                                  :class="{
                                    'text-primary': selected,
                                    'text-slate-900': !selected,
                                  }"
                                >
                                  {{ referer.name }}
                                </span>
                                <span
                                  v-if="referer.referer_type?.name"
                                  class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-500"
                                >
                                  {{ referer.referer_type.name }}
                                </span>
                              </div>
                              <div class="flex flex-col gap-1">
                                <div
                                  v-if="referer.mobile_no"
                                  class="flex items-center gap-1.5 text-xs text-slate-500"
                                >
                                  <PhoneIcon class="h-3 w-3" />
                                  <span>{{ referer.mobile_no }}</span>
                                </div>
                                <div
                                  v-if="referer.hospital_name"
                                  class="flex items-center gap-1.5 text-xs text-slate-400"
                                >
                                  <MapPinIcon class="h-3 w-3 shrink-0" />
                                  <span class="truncate">{{
                                    referer.hospital_name
                                  }}</span>
                                </div>
                              </div>
                            </div>
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

            <!-- New Referer Button -->
            <button
              type="button"
              @click="$emit('new-referer')"
              class="mt-1 h-[48px] px-6 rounded-xl bg-primary/5 text-primary border border-primary/10 font-bold text-sm hover:bg-primary hover:text-white hover:border-primary transition-all active:scale-95 flex items-center gap-2 whitespace-nowrap group shadow-sm shadow-primary/5"
            >
              <div
                class="h-6 w-6 rounded-lg bg-primary/10 group-hover:bg-white/20 flex items-center justify-center transition-colors"
              >
                <PlusIcon class="h-4 w-4" />
              </div>
              New Referer
            </button>
          </div>
        </div>

        <!-- Referer Details Grid -->
        <div
          v-if="form.referer_id"
          class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 animate-in fade-in slide-in-from-top-2 duration-300"
        >
          <!-- Referer Name -->
          <div class="space-y-2">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
            >
              Referer Name
            </label>
            <input
              v-model="form.referer_name"
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
                v-model="form.whatsapp_no_referer"
                type="tel"
                placeholder="WhatsApp No"
                class="flex-1 rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
              />
              <label
                class="flex items-center gap-2 px-4 rounded-2xl border border-slate-200 bg-slate-50 cursor-pointer hover:bg-white hover:border-emerald-500/30 transition-all active:scale-95 group shrink-0"
              >
                <div class="relative flex items-center justify-center">
                  <input
                    v-model="form.send_whatsapp_referer"
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

          <!-- Hospital Name -->
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
              class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
            />
          </div>

          <!-- Hospital ID -->
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
              class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
            />
          </div>
        </div>

        <!-- Next Action -->
        <div
          v-if="form.referer_id"
          class="flex justify-end pt-6 border-t border-slate-100 animate-in fade-in slide-in-from-top-2 duration-500"
        >
          <button
            type="button"
            @click="$emit('next')"
            class="group flex items-center gap-2 px-8 py-2.5 bg-primary text-white rounded-xl font-bold text-sm shadow-lg shadow-primary/20 hover:opacity-90 active:scale-95 transition-all"
          >
            Next: Case Information
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
import { ref, computed, watch } from "vue";
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
  ArrowRight as ArrowRightIcon,
} from "lucide-vue-next";
import axios from "axios";
import { debounce } from "lodash";

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
  referers: {
    type: Array,
    default: () => [],
  },
});

defineEmits(["new-referer", "next"]);

const query = ref("");
const selectedReferer = ref(null);

// Initialize selectedReferer if form has value
watch(
  () => props.form.referer_id,
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
    if (props.form.referer_id && newVal.length > 0) {
      const referer = newVal.find(
        (d) => String(d.id) === String(props.form.referer_id),
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

const handleRefererSelect = (referer) => {
  if (referer) {
    props.form.referer_id = referer.id.toString();
    // Watcher in useCaseReportForm handles the rest, but we can set it here too if needed.
    // The watcher is more robust for master data refreshes.
  }
};
</script>
