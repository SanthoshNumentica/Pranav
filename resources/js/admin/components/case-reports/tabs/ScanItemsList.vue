<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center gap-2">
        <h4
          class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2"
        >
          <div class="h-1 w-1 rounded-full bg-primary"></div>
          {{ showUpload ? "Scan Items & DICOM" : "Scan Items" }}
        </h4>
        <span
          v-if="totalCost > 0"
          class="ml-3 px-3 py-1 rounded-full bg-slate-100 text-slate-600 font-bold text-xs border border-slate-200"
        >
          Total: ₹{{ totalCost.toFixed(2) }}
        </span>
      </div>
      <button
        v-if="canEdit"
        type="button"
        @click="addItem"
        class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition-all active:scale-95 shadow-lg shadow-slate-900/10"
      >
        <PlusIcon class="h-4 w-4" />
        Add Scan
      </button>
    </div>

    <div class="space-y-6">
      <div
        v-for="(item, index) in form.items"
        :key="index"
        class="group relative bg-slate-50/50 rounded-[32px] border border-slate-100 p-6 transition-all hover:border-primary/20 hover:bg-white hover:shadow-lg hover:shadow-primary/5 focus-within:ring-2 focus-within:ring-primary/10"
      >
        <!-- Index Badge -->
        <div
          class="absolute -left-3 top-6 h-8 w-8 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold shadow-lg"
        >
          {{ index + 1 }}
        </div>

        <!-- Item Controls -->
        <button
          v-if="form.items.length > 1 && canEdit"
          type="button"
          @click="removeItem(index)"
          class="absolute -right-2 -top-2 h-8 w-8 rounded-full bg-white border border-slate-100 text-slate-400 hover:text-rose-500 hover:border-rose-100 hover:bg-rose-50 transition-all shadow-sm flex items-center justify-center"
        >
          <XIcon class="h-4 w-4" />
        </button>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">
          <!-- Scan Type -->
          <div class="space-y-2 md:col-span-4">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
            >
              Scan Type <span class="text-rose-500">*</span>
            </label>
            <div class="relative group/select">
              <ActivityIcon
                class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 group-hover/select:text-primary transition-colors z-10"
              />
              <Select v-model="item.scan_type_id" required :disabled="!canEdit">
                <SelectTrigger
                  class="pl-11 h-12 rounded-2xl border-slate-200 bg-slate-50/50 focus:bg-white transition-all disabled:opacity-70 disabled:cursor-not-allowed"
                >
                  <SelectValue placeholder="Select Type" />
                </SelectTrigger>
                <SelectContent class="rounded-2xl border-slate-100">
                  <SelectItem
                    v-for="type in scanTypes"
                    :key="type.id"
                    :value="type.id.toString()"
                  >
                    {{ type.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <!-- Specific Scan -->
          <div class="space-y-2 md:col-span-5">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
            >
              Specific Scan <span class="text-rose-500">*</span>
            </label>
            <div class="relative group/select">
              <SearchIcon
                class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 group-hover/select:text-primary transition-colors z-10"
              />
              <Select
                :model-value="item.scan_id"
                @update:model-value="(val) => onScanChange(val, item)"
                required
                :disabled="!item.scan_type_id || !canEdit"
              >
                <SelectTrigger
                  class="pl-11 h-12 rounded-2xl border-slate-200 bg-slate-50/50 focus:bg-white transition-all disabled:opacity-70 disabled:cursor-not-allowed"
                >
                  <SelectValue
                    :placeholder="
                      item.scan_type_id ? 'Select Scan' : 'Select Type First'
                    "
                  />
                </SelectTrigger>
                <SelectContent
                  class="rounded-2xl border-slate-100 max-h-[300px]"
                >
                  <SelectItem
                    v-for="scan in getScans(item.scan_type_id)"
                    :key="scan.id"
                    :value="scan.id.toString()"
                  >
                    {{ scan.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <!-- Amount -->
          <div class="space-y-2 md:col-span-3">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
            >
              Amount
            </label>
            <div class="relative group/input">
              <div
                class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within/input:text-primary transition-colors font-bold text-xs"
              >
                ₹
              </div>
              <input
                v-model="item.amount"
                type="number"
                min="0"
                step="0.01"
                placeholder="0.00"
                :disabled="!canEdit"
                class="w-full h-12 rounded-2xl py-3 pl-8 pr-4 text-sm border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all outline-none font-medium disabled:opacity-70 disabled:cursor-not-allowed"
              />
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <div v-if="showUpload" class="space-y-2">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
            >
              DICOM Study Folder
            </label>
            <div
              class="relative h-[112px] rounded-2xl border-2 border-dashed border-slate-200 bg-white flex flex-col items-center justify-center p-4 transition-all hover:border-primary/50 group/upload overflow-hidden"
              @dragover.prevent
              @drop.prevent="handleDrop($event, index)"
            >
              <input
                v-if="canEdit"
                type="file"
                webkitdirectory
                directory
                multiple
                class="absolute inset-0 opacity-0 cursor-pointer"
                @change="handleFiles($event, index)"
              />
              <div
                class="flex flex-col items-center gap-2 pointer-events-none"
                :class="{ 'opacity-50': !canEdit }"
              >
                <div
                  class="p-2 rounded-xl bg-slate-50 group-hover/upload:bg-primary/10 transition-colors"
                >
                  <component
                    :is="item.processing ? Loader2Icon : UploadIcon"
                    :class="[
                      'h-5 w-5',
                      item.processing
                        ? 'text-primary animate-spin'
                        : 'text-slate-400 group-hover/upload:text-primary',
                    ]"
                  />
                </div>
                <span
                  class="text-[11px] font-bold text-slate-500 group-hover/upload:text-primary"
                >
                  {{
                    item.processing
                      ? "Analyzing files..."
                      : canEdit
                        ? "Drop multiple folders or Click to select"
                        : "Upload Disabled"
                  }}
                </span>
              </div>
            </div>

            <!-- Multi-Folder Display -->
            <div class="mt-2 space-y-2">
              <div
                v-for="folder in getUniqueFolders(item.documents)"
                :key="folder.name"
                class="flex items-center justify-between gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg group/folder"
              >
                <div class="flex items-center gap-2">
                  <FolderIcon class="h-4 w-4 text-primary" />
                  <span class="text-xs font-bold text-slate-700">
                    {{ folder.name }}
                  </span>
                  <span class="text-[10px] text-slate-400 font-medium">
                    ({{ folder.count }} files)
                  </span>
                </div>
                <button
                  v-if="canEdit"
                  type="button"
                  @click="removeFolder(index, folder.name)"
                  class="p-1 rounded-md hover:bg-rose-50 text-slate-400 hover:text-rose-500 transition-colors"
                  title="Remove Folder"
                >
                  <XIcon class="h-4 w-4" />
                </button>
              </div>
            </div>

            <!-- Root Files Preview (if any) -->
            <div
              v-if="getUniqueFolders(item.documents, true).length > 0"
              class="mt-3 flex flex-wrap gap-2"
            >
              <div
                v-for="(doc, dIdx) in getUniqueFolders(item.documents, true)"
                :key="dIdx"
                class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-100 rounded-lg text-[10px] font-bold text-emerald-600 animate-in zoom-in-95 duration-200"
              >
                <CheckCircleIcon class="h-3 w-3" />
                <span class="truncate max-w-[100px]">{{ doc.name }}</span>
                <button
                  v-if="canEdit"
                  type="button"
                  @click="removeDoc(index, dIdx)"
                  class="hover:text-rose-500"
                >
                  <XIcon class="h-3 w-3" />
                </button>
              </div>
            </div>
          </div>

          <!-- Remarks Field -->
          <div class="space-y-2">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
            >
              Remarks
            </label>
            <textarea
              v-model="item.remarks"
              rows="2"
              :disabled="!canEdit"
              class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all placeholder:text-slate-300 resize-none font-medium text-slate-600 disabled:opacity-70 disabled:cursor-not-allowed"
              placeholder="Special instructions or notes for this scan..."
            ></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-if="form.items.length === 0"
      class="py-12 border-2 border-dashed border-slate-100 rounded-3xl flex flex-col items-center justify-center gap-3"
    >
      <div
        class="h-12 w-12 rounded-full bg-slate-50 flex items-center justify-center"
      >
        <AlertCircleIcon class="h-6 w-6 text-slate-300" />
      </div>
      <p class="text-sm font-medium text-slate-400 italic">
        No scan items added yet.
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../../../components/ui/select";
import {
  Loader2 as Loader2Icon,
  X as XIcon,
  Activity as ActivityIcon,
  Plus as PlusIcon,
  ChevronDown as ChevronDownIcon,
  Upload as UploadIcon,
  Folder as FolderIcon,
  CheckCircle as CheckCircleIcon,
  AlertCircle as AlertCircleIcon,
  Search as SearchIcon,
} from "lucide-vue-next";

const props = defineProps({
  form: { type: Object, required: true },
  scanTypes: { type: Array, default: () => [] },
  getScans: { type: Function, required: true },
  addItem: { type: Function, required: true },
  removeItem: { type: Function, required: true },
  handleDrop: { type: Function, required: true },
  handleFiles: { type: Function, required: true },
  removeFolder: { type: Function, required: true },
  removeDoc: { type: Function, required: true },
  getUniqueFolders: { type: Function, required: true },
  showUpload: { type: Boolean, default: true },
  canEdit: { type: Boolean, default: true },
});

const onScanChange = (scanId, item) => {
  item.scan_id = scanId;
  // User request: "make when select specific scan then autofill that amount in that input field"
  if (item.scan_type_id) {
    const scans = props.getScans(item.scan_type_id);
    const scan = scans.find((s) => s.id.toString() === scanId.toString());

    // Auto-fill amount if available, otherwise clear it to avoid stale data
    if (scan) {
      item.amount =
        scan.amount !== null && scan.amount !== undefined ? scan.amount : "";
    }
  }
};

const totalCost = computed(() => {
  return props.form.items.reduce((sum, item) => {
    return sum + (Number(item.amount) || 0);
  }, 0);
});
</script>
