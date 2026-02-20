<template>
  <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <!-- General Documents (JPG, PNG, PDF) -->
    <div
      class="bg-white rounded-[32px] border border-slate-200 p-8 shadow-soft-xl space-y-6"
    >
      <div class="flex items-center gap-3 text-slate-900 mb-2">
        <div
          class="h-10 w-10 rounded-2xl bg-primary/10 flex items-center justify-center"
        >
          <FileTextIcon class="h-5 w-5 text-primary" />
        </div>
        <h3 class="font-bold text-lg">General Documents</h3>
      </div>

      <div class="space-y-2">
        <label
          class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
        >
          Case Documents (PDF, WORD, EXCEL) <span class="text-rose-500">*</span>
        </label>
        <div class="flex items-center gap-3">
          <label
            class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-primary/30 transition-all cursor-pointer group relative overflow-hidden"
          >
            <input
              type="file"
              multiple
              accept="application/pdf,.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,.xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
              class="hidden"
              @change="handleGeneralFiles"
              :disabled="processingGeneral"
            />
            <template v-if="!processingGeneral">
              <PaperclipIcon
                class="h-4 w-4 text-slate-400 group-hover:text-primary transition-colors"
              />
              <span
                class="text-xs font-bold text-slate-500 group-hover:text-primary transition-colors"
              >
                Attach Files
              </span>
            </template>
            <template v-else>
              <Loader2Icon class="h-4 w-4 text-primary animate-spin" />
              <span class="text-xs font-bold text-primary">Processing...</span>
            </template>
          </label>
        </div>

        <!-- General File List -->
        <div v-if="form.documents.length > 0" class="flex flex-wrap gap-2 mt-2">
          <div
            v-for="(doc, dIdx) in form.documents"
            :key="dIdx"
            class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 border border-slate-200 rounded-xl text-[10px] font-bold text-slate-600 animate-in zoom-in-95 duration-200"
          >
            <div
              class="h-4 w-4 rounded-md bg-white border border-slate-200 flex items-center justify-center"
            >
              <CheckIcon class="h-2.5 w-2.5 text-emerald-500" />
            </div>
            <span class="truncate max-w-[80px]">{{ doc.name }}</span>
            <button
              type="button"
              @click="removeGeneralDoc(dIdx)"
              class="hover:text-rose-500 transition-colors"
            >
              <XIcon class="h-3 w-3" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Scan Items -->
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
    />
  </div>
</template>

<script setup>
import {
  FileText as FileTextIcon,
  Paperclip as PaperclipIcon,
  Loader2 as Loader2Icon,
  Check as CheckIcon,
  X as XIcon,
} from "lucide-vue-next";
import ScanItemsList from "./ScanItemsList.vue";

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
  scanTypes: {
    type: Array,
    default: () => [],
  },
  processingGeneral: {
    type: Boolean,
    default: false,
  },
  getScans: {
    type: Function,
    required: true,
  },
  getUniqueFolders: {
    type: Function,
    required: true,
  },
  handleGeneralFiles: {
    type: Function,
    required: true,
  },
  removeGeneralDoc: {
    type: Function,
    required: true,
  },
  addItem: {
    type: Function,
    required: true,
  },
  removeItem: {
    type: Function,
    required: true,
  },
  handleDrop: {
    type: Function,
    required: true,
  },
  handleFiles: {
    type: Function,
    required: true,
  },
  removeFolder: {
    type: Function,
    required: true,
  },
  removeDoc: {
    type: Function,
    required: true,
  },
});
</script>
