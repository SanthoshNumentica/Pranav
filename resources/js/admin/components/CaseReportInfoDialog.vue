<template>
  <TransitionRoot as="template" :show="isOpen">
    <Dialog as="div" class="relative z-50" @close="close">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div
          class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
        />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div
          class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
        >
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel
              class="relative transform overflow-hidden rounded-[32px] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200"
            >
              <!-- Header/Banner -->
              <div
                class="relative bg-primary px-6 py-8 sm:px-10 text-white overflow-hidden"
              >
                <div
                  class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"
                ></div>
                <div
                  class="absolute -bottom-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"
                ></div>

                <div class="relative flex items-center justify-between">
                  <div class="flex items-center gap-4">
                    <div
                      class="h-12 w-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30"
                    >
                      <FileTextIcon class="h-6 w-6 text-white" />
                    </div>
                    <div>
                      <h3 class="text-xl font-bold tracking-tight">
                        Case Report Details
                      </h3>
                      <div class="flex items-center gap-2 mt-1 opacity-90">
                        <span class="text-sm font-medium">{{
                          report?.case_id
                        }}</span>
                        <span class="h-1 w-1 rounded-full bg-white/50"></span>
                        <span
                          class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full border border-white/20"
                        >
                          {{ report?.status }}
                        </span>
                      </div>
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <button
                      @click="$router.push(`/case-reports/${report.id}/edit`)"
                      class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/20 hover:bg-white/30 text-white text-xs font-bold transition-all active:scale-95"
                    >
                      <EditIcon class="h-4 w-4" />
                      Edit Case
                    </button>
                    <button
                      @click="$emit('close')"
                      class="p-2 rounded-xl hover:bg-white/10 transition-colors"
                    >
                      <XIcon class="h-5 w-5" />
                    </button>
                  </div>
                </div>
              </div>

              <div class="p-6 sm:p-10 space-y-8">
                <!-- Patient & Doctor Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                  <!-- Patient Section -->
                  <div class="space-y-4 flex flex-col">
                    <div class="flex items-center gap-3 text-slate-900">
                      <div
                        class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center"
                      >
                        <UserIcon class="h-4 w-4 text-primary" />
                      </div>
                      <h4
                        class="text-sm font-bold uppercase tracking-widest text-slate-400"
                      >
                        Patient Details
                      </h4>
                    </div>

                    <div
                      class="space-y-3 bg-slate-50/50 p-6 rounded-[32px] border border-slate-100 flex-grow"
                    >
                      <div class="flex justify-between items-start">
                        <span
                          class="text-xs font-semibold text-slate-500 italic"
                          >Name</span
                        >
                        <span class="text-sm font-bold text-slate-900">{{
                          report?.patient?.name || "N/A"
                        }}</span>
                      </div>
                      <div class="flex justify-between items-start">
                        <span
                          class="text-xs font-semibold text-slate-500 italic"
                          >Patient ID</span
                        >
                        <span class="text-sm font-medium text-primary">{{
                          report?.patient?.patient_id || "N/A"
                        }}</span>
                      </div>
                      <div class="flex justify-between items-start">
                        <span
                          class="text-xs font-semibold text-slate-500 italic"
                          >Mobile</span
                        >
                        <span class="text-sm font-medium text-slate-700">{{
                          report?.patient?.mobile_no || "N/A"
                        }}</span>
                      </div>
                      <div class="flex justify-between items-start">
                        <span
                          class="text-xs font-semibold text-slate-500 italic"
                          >Gender</span
                        >
                        <span class="text-sm font-medium text-slate-700">{{
                          report?.patient?.gender?.gender_name || "N/A"
                        }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Doctor Section -->
                  <div class="space-y-4 flex flex-col">
                    <div class="flex items-center gap-3 text-slate-900">
                      <div
                        class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center"
                      >
                        <StethoscopeIcon class="h-4 w-4 text-primary" />
                      </div>
                      <h4
                        class="text-sm font-bold uppercase tracking-widest text-slate-400"
                      >
                        Doctor Details
                      </h4>
                    </div>

                    <div
                      class="space-y-3 bg-slate-50/50 p-6 rounded-[32px] border border-slate-100 flex-grow"
                    >
                      <div class="flex justify-between items-start">
                        <span
                          class="text-xs font-semibold text-slate-500 italic"
                          >Name</span
                        >
                        <span class="text-sm font-bold text-slate-900">{{
                          report?.doctor?.name || "N/A"
                        }}</span>
                      </div>
                      <div class="flex justify-between items-start">
                        <span
                          class="text-xs font-semibold text-slate-500 italic"
                          >Doctor ID</span
                        >
                        <span class="text-sm font-medium text-primary">{{
                          report?.doctor?.doctor_id || "N/A"
                        }}</span>
                      </div>
                      <div class="flex justify-between items-start">
                        <span
                          class="text-xs font-semibold text-slate-500 italic"
                          >Mobile</span
                        >
                        <span class="text-sm font-medium text-slate-700">{{
                          report?.doctor?.mobile_no || "N/A"
                        }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Case Details & Documents Grid -->
                <div
                  v-if="report?.description || report?.documents?.length"
                  class="grid grid-cols-1 md:grid-cols-2 gap-8"
                >
                  <!-- Case Description -->
                  <div
                    v-if="report?.description"
                    class="space-y-4 flex flex-col"
                  >
                    <div class="flex items-center gap-3 text-slate-900">
                      <div
                        class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center"
                      >
                        <MessageSquareIcon class="h-4 w-4 text-primary" />
                      </div>
                      <h4
                        class="text-sm font-bold uppercase tracking-widest text-slate-400"
                      >
                        Case History
                      </h4>
                    </div>
                    <div
                      class="p-6 bg-slate-50/50 rounded-[32px] border border-slate-100 text-sm text-slate-600 leading-relaxed italic flex-grow min-h-[140px]"
                    >
                      {{ report.description }}
                    </div>
                  </div>

                  <!-- Case Documents Preview -->
                  <div
                    v-if="report?.documents?.length"
                    class="space-y-4 flex flex-col"
                  >
                    <div class="flex items-center gap-3 text-slate-900">
                      <div
                        class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center"
                      >
                        <PaperclipIcon class="h-4 w-4 text-primary" />
                      </div>
                      <h4
                        class="text-sm font-bold uppercase tracking-widest text-slate-400"
                      >
                        Documents
                      </h4>
                    </div>

                    <div
                      class="grid grid-cols-2 gap-4 p-4 bg-slate-50/50 rounded-[32px] border border-slate-100 flex-grow"
                    >
                      <div
                        v-for="(doc, idx) in report.documents"
                        :key="idx"
                        class="group relative aspect-square rounded-[24px] bg-white border border-slate-200 overflow-hidden hover:border-primary/50 transition-all cursor-pointer shadow-sm"
                        @click="viewFile(doc)"
                      >
                        <img
                          v-if="isImage(doc)"
                          :src="'/' + doc"
                          class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                          alt="Preview"
                        />
                        <div
                          v-else
                          class="w-full h-full flex flex-col items-center justify-center gap-1 p-2"
                        >
                          <FileTextIcon
                            class="h-8 w-8 text-slate-300 group-hover:text-primary transition-colors"
                          />
                          <span
                            class="text-[10px] font-bold text-slate-500 truncate w-full text-center px-1"
                          >
                            {{ doc.split("/").pop() }}
                          </span>
                        </div>
                        <div
                          class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2"
                        >
                          <button
                            @click.stop="viewFile(doc)"
                            class="h-9 w-9 rounded-xl bg-white text-primary flex items-center justify-center shadow-lg hover:scale-110 transition-transform"
                            title="View"
                          >
                            <EyeIcon class="h-4.5 w-4.5" />
                          </button>
                          <a
                            :href="'/' + doc"
                            download
                            @click.stop
                            class="h-9 w-9 rounded-xl bg-white text-emerald-600 flex items-center justify-center shadow-lg hover:scale-110 transition-transform"
                            title="Download"
                          >
                            <DownloadIcon class="h-4.5 w-4.5" />
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Scan Items -->
                <div class="space-y-4">
                  <div class="flex items-center gap-3 text-slate-900">
                    <div
                      class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center"
                    >
                      <ActivityIcon class="h-4 w-4 text-primary" />
                    </div>
                    <h4
                      class="text-sm font-bold uppercase tracking-widest text-slate-400"
                    >
                      Scan Items / Services
                    </h4>
                  </div>

                  <div
                    class="overflow-hidden rounded-3xl border border-slate-200"
                  >
                    <table class="w-full text-left">
                      <thead class="bg-slate-50/50">
                        <tr>
                          <th
                            class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500"
                          >
                            Scan Type
                          </th>
                          <th
                            class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500"
                          >
                            Specific Scan
                          </th>
                          <th
                            class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 text-center"
                          >
                            Files
                          </th>
                          <th
                            class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 text-right"
                          >
                            Actions
                          </th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 italic">
                        <tr v-for="item in report?.items" :key="item.id">
                          <td
                            class="px-4 py-3 text-sm font-medium text-slate-900"
                          >
                            {{ item.scan_type?.name || "N/A" }}
                          </td>
                          <td class="px-4 py-3 text-sm text-slate-600">
                            {{ item.scan?.name || "N/A" }}
                          </td>
                          <td class="px-4 py-3 text-sm text-center">
                            <span
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-primary border border-blue-100"
                            >
                              {{ item.documents?.length || 0 }}
                            </span>
                          </td>
                          <td class="px-4 py-3 text-sm text-right">
                            <button
                              v-if="item.documents?.length"
                              @click="viewFile(item.documents[0])"
                              class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-primary/10 text-primary text-[10px] font-bold hover:bg-primary/20 transition-all active:scale-95"
                            >
                              <FileSearchIcon class="h-3 w-3" />
                              View DICOM
                            </button>
                            <span
                              v-else
                              class="text-[10px] font-bold text-slate-400 italic"
                              >No files</span
                            >
                          </td>
                        </tr>
                        <tr v-if="!report?.items?.length">
                          <td
                            colspan="4"
                            class="px-4 py-10 text-center text-sm text-slate-400"
                          >
                            No scan items listed.
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div
                class="bg-slate-50 px-6 py-4 sm:px-10 flex flex-col sm:flex-row justify-between items-center gap-4"
              >
                <span
                  class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic"
                  >Created on
                  {{
                    report?.created_at
                      ? new Date(report.created_at).toLocaleString()
                      : "N/A"
                  }}</span
                >
                <button
                  type="button"
                  class="inline-flex w-full justify-center rounded-xl bg-white px-6 py-2.5 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:w-auto transition-all active:scale-95"
                  @click="close"
                >
                  Close Detail
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch, reactive } from "vue";
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import {
  X as XIcon,
  FileText as FileTextIcon,
  User as UserIcon,
  Stethoscope as StethoscopeIcon,
  Activity as ActivityIcon,
  Paperclip as PaperclipIcon,
  Eye as EyeIcon,
  FileSearch as FileSearchIcon,
  Download as DownloadIcon,
  MessageSquare as MessageSquareIcon,
  Edit as EditIcon,
} from "lucide-vue-next";
import { useRouter } from "vue-router";

defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  report: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["close"]);

const router = useRouter();

const close = () => {
  emit("close");
};

const viewFile = (path) => {
  if (!path) return;

  const ext = path.split(".").pop().toLowerCase();
  if (ext === "dcm") {
    router.push({
      name: "DicomView",
      query: { path: path },
    });
  } else {
    window.open("/" + path, "_blank");
  }
};

const isImage = (path) => {
  if (!path) return false;
  const ext = path.split(".").pop().toLowerCase();
  return ["jpg", "jpeg", "png", "webp", "gif"].includes(ext);
};
</script>
