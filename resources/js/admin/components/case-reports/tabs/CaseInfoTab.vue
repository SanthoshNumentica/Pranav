<template>
  <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div
      class="bg-white rounded-[32px] border border-slate-200 p-8 shadow-soft-xl space-y-8"
    >
      <div class="flex items-center gap-2 mb-2">
        <h4
          class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2"
        >
          <div class="h-1 w-1 rounded-full bg-primary"></div>
          Case Information
        </h4>
      </div>

      <!-- Main Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- SRF No -->
        <div class="col-span-2 md:col-span-1 space-y-2">
          <label
            class="text-xs font-bold text-slate-500 uppercase tracking-wider"
          >
            SRF No <span class="text-rose-500">*</span>
          </label>
          <div class="relative group">
            <div
              class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
            >
              <HashIcon
                class="h-5 w-5 text-slate-400 group-focus-within:text-primary transition-colors"
              />
            </div>
            <input
              v-model="form.case_id"
              type="text"
              readonly
              class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 font-medium focus:outline-none cursor-not-allowed"
              :placeholder="fetching ? 'Generating ID...' : 'Auto-generated'"
            />
            <!-- Loader -->
            <div
              v-if="fetching"
              class="absolute inset-y-0 right-4 flex items-center pointer-events-none"
            >
              <Loader2Icon class="h-4 w-4 text-primary animate-spin" />
            </div>
          </div>
        </div>

        <!-- RCT Date -->
        <div class="space-y-2">
          <label
            class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
          >
            RCT Date
          </label>
          <div class="relative group/input">
            <CalendarIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 group-focus-within/input:text-primary transition-colors"
            />
            <input
              v-model="form.rct_date"
              type="date"
              class="w-full rounded-2xl py-3 pl-10 pr-4 text-sm border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all outline-none font-medium"
            />
          </div>
        </div>

        <!-- RCT Hour -->
        <div class="space-y-2">
          <label
            class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
          >
            RCT Hour
          </label>
          <div class="relative group/input">
            <ClockIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 group-focus-within/input:text-primary transition-colors"
            />
            <input
              v-model="form.rct_hour"
              type="text"
              placeholder="e.g. 14:00"
              class="w-full rounded-2xl py-3 pl-10 pr-4 text-sm border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all outline-none font-medium"
            />
          </div>
        </div>
      </div>

      <!-- Case Status & Type (Toggles & Selections) -->
      <div class="space-y-4 pt-4">
        <label
          class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
        >
          Case Status & Type
        </label>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- STAT Case Toggle -->
          <button
            type="button"
            @click="form.is_stat = !form.is_stat"
            class="p-5 rounded-2xl border transition-all flex items-start gap-4 text-left group relative overflow-hidden"
            :class="
              form.is_stat
                ? 'bg-rose-50/50 border-rose-200 shadow-sm ring-1 ring-rose-200'
                : 'bg-slate-50/50 border-slate-200 hover:bg-white hover:border-slate-300'
            "
          >
            <div
              class="h-10 w-10 rounded-2xl flex items-center justify-center shrink-0 transition-all duration-300"
              :class="
                form.is_stat
                  ? 'bg-rose-500 text-white scale-110'
                  : 'bg-slate-200/50 text-slate-400 group-hover:bg-slate-200'
              "
            >
              <ZapIcon
                class="h-5 w-5"
                :class="{ 'animate-pulse': form.is_stat }"
              />
            </div>
            <div class="space-y-1">
              <span
                class="text-xs font-black uppercase tracking-wider block"
                :class="form.is_stat ? 'text-rose-600' : 'text-slate-600'"
              >
                STAT Case
              </span>
              <p class="text-[10px] text-slate-400 leading-relaxed font-medium">
                Mark this as an urgent priority case.
              </p>
            </div>
            <!-- Checkmark Indicator -->
            <div
              class="absolute top-4 right-4 h-5 w-5 rounded-full border-2 flex items-center justify-center transition-all duration-300"
              :class="
                form.is_stat
                  ? 'bg-rose-500 border-rose-500 scale-100 opacity-100'
                  : 'border-slate-200 scale-50 opacity-0'
              "
            >
              <CheckIcon class="h-3 w-3 text-white" />
            </div>
          </button>

          <!-- Out Patient Radio -->
          <button
            type="button"
            @click="form.patient_type = 'out_patient'"
            class="p-5 rounded-2xl border transition-all flex items-start gap-4 text-left group relative overflow-hidden"
            :class="
              form.patient_type === 'out_patient'
                ? 'bg-primary/5 border-primary shadow-sm ring-1 ring-primary'
                : 'bg-slate-50/50 border-slate-200 hover:bg-white hover:border-slate-300'
            "
          >
            <div
              class="h-10 w-10 rounded-2xl flex items-center justify-center shrink-0 transition-all duration-300"
              :class="
                form.patient_type === 'out_patient'
                  ? 'bg-primary text-white scale-110'
                  : 'bg-slate-200/50 text-slate-400 group-hover:bg-slate-200'
              "
            >
              <UserIcon class="h-5 w-5" />
            </div>
            <div class="space-y-1">
              <span
                class="text-xs font-black uppercase tracking-wider block"
                :class="
                  form.patient_type === 'out_patient'
                    ? 'text-primary'
                    : 'text-slate-600'
                "
              >
                Out Patient
              </span>
              <p class="text-[10px] text-slate-400 leading-relaxed font-medium">
                Not admitted, visiting only for consultation or scan.
              </p>
            </div>
            <!-- Checkmark Indicator -->
            <div
              class="absolute top-4 right-4 h-5 w-5 rounded-full border-2 flex items-center justify-center transition-all duration-300"
              :class="
                form.patient_type === 'out_patient'
                  ? 'bg-primary border-primary scale-100 opacity-100'
                  : 'border-slate-200 scale-50 opacity-0'
              "
            >
              <CheckIcon class="h-3 w-3 text-white" />
            </div>
          </button>

          <!-- In Patient Radio -->
          <button
            type="button"
            @click="form.patient_type = 'in_patient'"
            class="p-5 rounded-2xl border transition-all flex items-start gap-4 text-left group relative overflow-hidden"
            :class="
              form.patient_type === 'in_patient'
                ? 'bg-primary/5 border-primary shadow-sm ring-1 ring-primary'
                : 'bg-slate-50/50 border-slate-200 hover:bg-white hover:border-slate-300'
            "
          >
            <div
              class="h-10 w-10 rounded-2xl flex items-center justify-center shrink-0 transition-all duration-300"
              :class="
                form.patient_type === 'in_patient'
                  ? 'bg-primary text-white scale-110'
                  : 'bg-slate-200/50 text-slate-400 group-hover:bg-slate-200'
              "
            >
              <HospitalIcon class="h-5 w-5" />
            </div>
            <div class="space-y-1">
              <span
                class="text-xs font-black uppercase tracking-wider block"
                :class="
                  form.patient_type === 'in_patient'
                    ? 'text-primary'
                    : 'text-slate-600'
                "
              >
                In Patient
              </span>
              <p class="text-[10px] text-slate-400 leading-relaxed font-medium">
                The patient is currently admitted to the hospital.
              </p>
            </div>
            <!-- Checkmark Indicator -->
            <div
              class="absolute top-4 right-4 h-5 w-5 rounded-full border-2 flex items-center justify-center transition-all duration-300"
              :class="
                form.patient_type === 'in_patient'
                  ? 'bg-primary border-primary scale-100 opacity-100'
                  : 'border-slate-200 scale-50 opacity-0'
              "
            >
              <CheckIcon class="h-3 w-3 text-white" />
            </div>
          </button>
        </div>
      </div>

      <!-- Description -->
      <div class="space-y-2 pt-2">
        <label
          class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
        >
          Description (Optional)
        </label>
        <textarea
          v-model="form.description"
          rows="4"
          class="w-full rounded-[24px] py-4 px-5 text-sm border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all outline-none resize-none font-medium placeholder:text-slate-300 shadow-inner-soft"
          placeholder="Case history, clinical notes or specific instructions..."
        ></textarea>
      </div>

      <!-- Branch Selection -->
      <div class="space-y-3 pt-6 border-t border-slate-100">
        <label
          class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
        >
          Assigned Branch <span class="text-rose-500">*</span>
        </label>
        <div class="relative group/select">
          <div
            class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 group-hover/select:text-primary transition-colors z-10"
          >
            <MapPinIcon class="h-4 w-4" />
          </div>
          <Select
            v-model="form.branch_id"
            required
            :disabled="!loggedInUserIsSuperAdmin"
          >
            <SelectTrigger class="pl-11 h-12 rounded-2xl border-slate-200">
              <SelectValue placeholder="Select Branch" />
            </SelectTrigger>
            <SelectContent class="rounded-2xl border-slate-100">
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

    <ScanItemsList
      class="bg-white rounded-[32px] border border-slate-200 p-8 shadow-soft-xl"
      :form="form"
      :scan-types="scanTypes"
      :get-scans="getScans"
      :add-item="addItem"
      :remove-item="removeItem"
      :handle-drop="handleDrop"
      :handle-files="handleFiles"
      :remove-folder="removeFolder"
      :remove-doc="removeDoc"
      :get-unique-folders="getUniqueFolders"
      :show-upload="false"
    />

    <!-- Actions -->
    <div
      class="flex items-center justify-between pt-6 animate-in fade-in slide-in-from-top-2 duration-500"
    >
      <button
        type="button"
        @click="$emit('back')"
        class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition-all active:scale-95"
      >
        Back
      </button>

      <button
        type="submit"
        :disabled="form.processing"
        class="group flex items-center gap-2 px-8 py-2.5 bg-primary text-white rounded-xl font-bold text-sm shadow-lg shadow-primary/20 hover:opacity-90 active:scale-95 transition-all disabled:opacity-70 disabled:cursor-not-allowed"
      >
        <Loader2Icon v-if="form.processing" class="h-4 w-4 animate-spin" />
        {{ isEditMode ? "Update Case Report" : "Create Case Report" }}
        <ArrowRightIcon
          v-if="!form.processing"
          class="h-4 w-4 group-hover:translate-x-1 transition-transform"
        />
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../../../components/ui/select";
import {
  Info as InfoIcon,
  MapPin as MapPinIcon,
  Hash as HashIcon,
  Calendar as CalendarIcon,
  Clock as ClockIcon,
  Zap as ZapIcon,
  User as UserIcon,
  Building2 as HospitalIcon,
  Check as CheckIcon,
  ArrowRight as ArrowRightIcon,
  Loader2 as Loader2Icon,
} from "lucide-vue-next";
import ScanItemsList from "./ScanItemsList.vue";

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
  loggedInUserIsSuperAdmin: {
    type: Boolean,
    default: false,
  },
  fetching: Boolean,
  filteredBranches: {
    type: Array,
    default: () => [],
  },
  // Scan Items Props
  scanTypes: { type: Array, default: () => [] },
  getScans: { type: Function, required: true },
  addItem: { type: Function, required: true },
  removeItem: { type: Function, required: true },
  handleDrop: { type: Function, required: true },
  handleFiles: { type: Function, required: true },
  removeFolder: { type: Function, required: true },
  removeDoc: { type: Function, required: true },
  getUniqueFolders: { type: Function, required: true },
});

defineEmits(["next", "back"]);

const route = useRoute();
const isEditMode = computed(() => route.path.includes("/edit"));
</script>

<style scoped>
.shadow-inner-soft {
  box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.02);
}
</style>
