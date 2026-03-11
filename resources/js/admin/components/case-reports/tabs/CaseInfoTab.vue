<template>
  <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div class="bg-white rounded-[24px] border border-slate-200 p-6 shadow-soft-xl space-y-4">
      <!-- Section Header with Case Id and Branch -->
      <div class="flex items-center justify-between gap-4 pb-2">
        <div class="flex items-center gap-3">
          <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2">
            <div class="h-1 w-1 rounded-full bg-primary"></div>
            Case Information
          </h4>
          <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 text-[10px] font-bold tracking-wider">
            Case Id: {{ form.case_id || 'AUTO_GENERATED' }}
          </span>
        </div>

        <!-- Branch Selection (Header Right) -->
        <div class="w-64 relative group/select">
          <div
            class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 group-hover/select:text-primary transition-colors z-10">
            <MapPinIcon class="h-3.5 w-3.5" />
          </div>
          <Select v-model="form.branch_id" required :disabled="!loggedInUserIsSuperAdmin || !canEdit">
            <SelectTrigger class="pl-9 h-[38px] rounded-xl border-slate-200 text-xs bg-slate-50/50">
              <SelectValue placeholder="Select Branch" />
            </SelectTrigger>
            <SelectContent class="rounded-xl border-slate-100">
              <SelectItem v-for="branch in filteredBranches" :key="branch.id" :value="branch.id.toString()">
                {{ branch.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      <!-- Main Grid Layout -->
      <div class="space-y-4 animate-in fade-in slide-in-from-top-2 duration-300">
        <!-- Row 1: Scanning Date, Check In, Stat Case -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
          <!-- Scanning Date -->
          <div class="md:col-span-4 space-y-1.5">
            <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">
              Scanning Date
            </label>
            <div class="relative group/input">
              <CalendarIcon
                class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 group-focus-within/input:text-primary transition-colors" />
              <input v-model="form.scanning_date" type="date" :disabled="!canEdit"
                class="w-full rounded-xl py-2.5 pl-10 pr-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all outline-none font-medium disabled:opacity-70 disabled:cursor-not-allowed" />
            </div>
          </div>

          <!-- Check In -->
          <div class="md:col-span-3 space-y-1.5">
            <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">
              Check In
            </label>
            <div class="relative group/input">
              <ClockIcon
                class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 group-focus-within/input:text-primary transition-colors" />
              <input v-model="form.check_in" type="text" placeholder="e.g. 14:00" :disabled="!canEdit"
                class="w-full rounded-xl py-2.5 pl-10 pr-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all outline-none font-medium disabled:opacity-70 disabled:cursor-not-allowed" />
            </div>
          </div>

          <!-- Stat Case (Aligned Right Checkbox) -->
          <div class="md:col-span-5 flex justify-end pb-1">
            <button type="button" @click="canEdit && (form.is_stat_case = !form.is_stat_case)"
              class="px-4 py-2.5 rounded-xl border transition-all flex items-center gap-2.5 text-left group relative overflow-hidden"
              :class="[
                form.is_stat_case
                  ? 'bg-rose-50 border-rose-200 shadow-sm ring-1 ring-rose-200'
                  : 'bg-slate-50 border-slate-200 hover:bg-white hover:border-slate-300',
                { 'cursor-not-allowed opacity-70': !canEdit },
              ]">
              <ZapIcon class="h-4 w-4 transition-all duration-300"
                :class="form.is_stat_case ? 'text-rose-500 scale-110' : 'text-slate-400'" />
              <span class="text-[11px] font-bold uppercase tracking-wider"
                :class="form.is_stat_case ? 'text-rose-600' : 'text-slate-500'">Stat Case</span>
              <div class="h-4 w-4 rounded border flex items-center justify-center transition-all ml-1"
                :class="form.is_stat_case ? 'bg-rose-500 border-rose-500' : 'border-slate-300 bg-white'">
                <CheckIcon v-if="form.is_stat_case" class="h-2.5 w-2.5 text-white stroke-[3px]" />
              </div>
            </button>
          </div>
        </div>

        <!-- Row 2: Description -->
        <div class="space-y-1.5">
          <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">
            Description (Optional)
          </label>
          <textarea v-model="form.description" :disabled="!canEdit" rows="2"
            class="w-full rounded-xl py-2.5 px-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all outline-none font-medium placeholder:text-slate-300 disabled:opacity-70 disabled:cursor-not-allowed resize-none"
            placeholder="Case history or notes..."></textarea>
        </div>
      </div>
    </div>

    <ScanItemsList class="bg-white rounded-[24px] border border-slate-200 p-6 shadow-soft-xl" :form="form"
      :scan-types="scanTypes" :get-scans="getScans" :add-item="addItem" :remove-item="removeItem"
      :handle-drop="handleDrop" :handle-files="handleFiles" :remove-folder="removeFolder" :remove-doc="removeDoc"
      :get-unique-folders="getUniqueFolders" :show-upload="false" :can-edit="canEdit" />
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
  processing: {
    type: Boolean,
    default: false,
  },
  canEdit: {
    type: Boolean,
    default: true,
  },
  isLastTab: {
    type: Boolean,
    default: true,
  },
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
