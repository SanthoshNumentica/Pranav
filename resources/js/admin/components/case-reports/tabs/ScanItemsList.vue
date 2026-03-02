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
          <div class="space-y-2 md:col-span-3">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
            >
              Scan Type <span class="text-rose-500">*</span>
            </label>
            <div class="relative group/select">
              <ActivityIcon
                class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 group-hover/select:text-primary transition-colors z-10"
              />
              <Select
                :model-value="item.scan_type_id"
                @update:model-value="(val) => onTypeChange(val, item)"
                required
                :disabled="!canEdit"
              >
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

          <!-- Item ID -->
          <div class="space-y-2 md:col-span-2">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
            >
              Item ID
            </label>
            <div class="relative group/input">
              <HashIcon
                class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 group-focus-within/input:text-primary transition-colors z-10"
              />
              <input
                v-model="item.custom_id"
                type="text"
                placeholder="ID"
                :disabled="!canEdit"
                class="w-full h-12 rounded-2xl py-3 pl-11 pr-4 text-sm border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all outline-none font-medium disabled:opacity-70 disabled:cursor-not-allowed uppercase"
              />
            </div>
          </div>

          <!-- Specific Scan -->
          <div class="space-y-2 md:col-span-4">
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
                v-model="item.scan_id"
                @update:model-value="(val) => onScanSelect(val, item)"
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
                :disabled="true"
                class="w-full h-12 rounded-2xl py-3 pl-8 pr-4 text-sm border border-slate-200 bg-slate-50/50 transition-all outline-none font-medium opacity-70 cursor-not-allowed"
              />
            </div>
          </div>
        </div>

        <!-- Selected Scans Table (Screenshot Layout) -->
        <div v-if="item.selected_scans && item.selected_scans.length > 0" class="mb-6 animate-in fade-in slide-in-from-top-2 duration-300">
          <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                  <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 w-12 text-center">#</th>
                  <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">Description</th>
                  <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 text-right w-40">Amount</th>
                  <th v-if="canEdit" class="py-3 px-4 text-[10px] font-bold uppercase tracking-wider text-slate-400 w-12 text-center"></th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="(scan, sIdx) in item.selected_scans" 
                  :key="sIdx"
                  class="border-b border-slate-50 last:border-0 group/row hover:bg-slate-50/30 transition-colors"
                >
                  <td class="py-3 px-4 text-xs font-bold text-slate-400 text-center">{{ sIdx + 1 }}</td>
                  <td class="py-3 px-4 text-sm font-medium text-slate-600">{{ scan.scan_name }}</td>
                  <td class="py-3 px-4">
                    <div class="relative group/input flex justify-end">
                      <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">₹</div>
                      <input
                        v-model="scan.amount"
                        type="number"
                        min="0"
                        step="0.01"
                        :disabled="!canEdit"
                        class="w-32 h-9 rounded-xl py-1 pl-7 pr-3 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all outline-none font-bold text-right text-slate-700"
                      />
                    </div>
                  </td>
                  <td v-if="canEdit" class="py-3 px-4 text-center">
                    <button
                      type="button"
                      @click="removeScan(item, sIdx)"
                      class="p-1.5 rounded-lg text-slate-300 hover:text-rose-500 hover:bg-rose-50 transition-all opacity-0 group-hover/row:opacity-100"
                    >
                      <Trash2Icon class="h-4 w-4" />
                    </button>
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr class="bg-slate-50/30 font-bold">
                  <td colspan="2" class="py-3 px-4 text-right text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Scans Cost</td>
                  <td class="py-3 px-4 text-right text-sm text-primary">₹{{ calculateItemTotal(item).toFixed(2) }}</td>
                  <td v-if="canEdit"></td>
                </tr>
              </tfoot>
            </table>
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
import axios from "axios";
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
  Hash as HashIcon,
  Trash2 as Trash2Icon,
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

const onTypeChange = async (typeId, item) => {
  item.scan_type_id = typeId;
  item.scan_id = ""; // Reset scan selection
  item.selected_scans = []; // Reset selected scans for new type

  if (typeId) {
    try {
      // Fetch the next global ID for this scan type
      const response = await axios.get(
        `/api/v1/case-report-items/next-id/${typeId}`
      );
      if (response.data.success) {
        let nextId = response.data.next_custom_id;

        // Extract prefix and number
        const prefix = nextId.replace(/\d+$/, "");
        const baseNum = parseInt(nextId.match(/\d+$/)[0]);

        // Check if other unsaved items in the current form already used this or later numbers
        const sameTypeItems = props.form.items.filter(
          (i) =>
            i !== item &&
            i.scan_type_id === typeId &&
            i.custom_id &&
            i.custom_id.startsWith(prefix)
        );

        let finalNum = baseNum;
        if (sameTypeItems.length > 0) {
          const usedNums = sameTypeItems.map((i) =>
            parseInt(i.custom_id.match(/\d+$/)[0])
          );
          const maxUsed = Math.max(...usedNums);
          if (maxUsed >= finalNum) {
            finalNum = maxUsed + 1;
          }
        }

        item.custom_id = `${prefix}${String(finalNum).padStart(4, "0")}`;
      }
    } catch (err) {
      console.error("Failed to fetch next item ID", err);
      // Fallback to local logic if API fails
      const type = props.scanTypes.find(
        (t) => t.id.toString() === typeId.toString()
      );
      if (type) {
        const prefix = type.name
          .substring(0, 2)
          .toUpperCase()
          .replace(/[^A-Z]/g, "IT");
        const count = props.form.items.filter(
          (i) => i.scan_type_id === typeId
        ).length;
        item.custom_id = `${prefix}${String(count).padStart(4, "0")}`;
      }
    }
  }
};

const onScanSelect = (scanId, item) => {
  if (!scanId) return;

  const scans = props.getScans(item.scan_type_id);
  const scan = scans.find((s) => s.id.toString() === scanId.toString());

  if (scan) {
    // Update top row price display
    item.amount = scan.amount || "";

    // Check if duplicate in table
    const exists = (item.selected_scans || []).some(
      (s) => s.scan_id.toString() === scanId.toString()
    );

    if (!exists) {
      if (!item.selected_scans) item.selected_scans = [];
      item.selected_scans.push({
        scan_id: scanId,
        scan_name: scan.name,
        amount:
          scan.amount !== null && scan.amount !== undefined ? scan.amount : "",
      });
    }
  }
};

const removeScan = (item, scanIndex) => {
  if (item.selected_scans) {
    item.selected_scans.splice(scanIndex, 1);
  }
};

const calculateItemTotal = (item) => {
  if (!item.selected_scans) return 0;
  return item.selected_scans.reduce((sum, s) => {
    return sum + (Number(s.amount) || 0);
  }, 0);
};

const totalCost = computed(() => {
  return props.form.items.reduce((sum, item) => {
    return sum + calculateItemTotal(item);
  }, 0);
});
</script>
