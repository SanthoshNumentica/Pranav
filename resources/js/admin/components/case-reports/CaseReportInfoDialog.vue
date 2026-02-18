<template>
  <TransitionRoot as="template" :show="isOpen">
    <Dialog as="div" class="relative z-50" @close="() => {}">
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
              class="relative transform overflow-hidden rounded-[32px] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border border-slate-200 flex flex-col h-[90vh] sm:h-[85vh]"
            >
              <!-- Header/Banner - Fixed -->
              <div
                class="relative bg-primary px-6 py-8 sm:px-10 text-white overflow-hidden shrink-0"
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
                        <template v-if="report?.expires_at">
                          <span class="h-1 w-1 rounded-full bg-white/50"></span>
                          <span
                            class="text-[10px] font-bold uppercase tracking-wider bg-rose-500/40 px-2 py-0.5 rounded-full border border-white/20"
                          >
                            Expires:
                            {{ formatDate(report.expires_at) }}
                          </span>
                        </template>
                        <span class="h-1 w-1 rounded-full bg-white/50"></span>
                        <div class="flex items-center gap-1.5 opacity-80">
                          <MapPinIcon class="h-3 w-3" />
                          <span class="text-xs font-semibold">{{
                            report?.branch?.name || "N/A"
                          }}</span>
                        </div>
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
                      @click.stop="close"
                      class="p-2 rounded-xl hover:bg-white/10 transition-colors"
                    >
                      <XIcon class="h-5 w-5" />
                    </button>
                  </div>
                </div>
              </div>

              <!-- Content - Scrollable -->
              <div
                class="p-6 sm:p-10 space-y-8 overflow-y-auto flex-grow custom-scrollbar"
              >
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
                        <span class="text-xs font-semibold text-slate-500"
                          >Name</span
                        >
                        <span class="text-sm font-bold text-slate-900">{{
                          report?.patient?.name || "N/A"
                        }}</span>
                      </div>
                      <div class="flex justify-between items-start">
                        <span class="text-xs font-semibold text-slate-500"
                          >Patient ID</span
                        >
                        <span class="text-sm font-medium text-primary">{{
                          report?.patient?.patient_id || "N/A"
                        }}</span>
                      </div>
                      <div class="flex justify-between items-start">
                        <span class="text-xs font-semibold text-slate-500"
                          >Mobile</span
                        >
                        <span class="text-sm font-medium text-slate-700">{{
                          report?.patient?.mobile_no || "N/A"
                        }}</span>
                      </div>
                      <div class="flex justify-between items-start">
                        <span class="text-xs font-semibold text-slate-500"
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
                        <span class="text-xs font-semibold text-slate-500"
                          >Name</span
                        >
                        <span class="text-sm font-bold text-slate-900">{{
                          report?.doctor?.name || "N/A"
                        }}</span>
                      </div>
                      <div class="flex justify-between items-start">
                        <span class="text-xs font-semibold text-slate-500"
                          >Doctor ID</span
                        >
                        <span class="text-sm font-medium text-primary">{{
                          report?.doctor?.doctor_id || "N/A"
                        }}</span>
                      </div>
                      <div class="flex justify-between items-start">
                        <span class="text-xs font-semibold text-slate-500"
                          >Mobile</span
                        >
                        <span class="text-sm font-medium text-slate-700">{{
                          report?.doctor?.mobile_no || "N/A"
                        }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Case History and Documents Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                  <!-- Case History -->
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
                      class="p-6 bg-slate-50/50 rounded-[32px] border border-slate-100 text-sm text-slate-600 leading-relaxed flex-grow min-h-[100px]"
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
                      class="grid grid-cols-2 sm:grid-cols-3 gap-4 p-4 bg-slate-50/50 rounded-[32px] border border-slate-100 flex-grow"
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
                    class="overflow-x-auto custom-scrollbar rounded-3xl border border-slate-200"
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
                            class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500"
                          >
                            Remarks
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
                      <tbody class="divide-y divide-slate-100">
                        <tr v-for="item in report?.items" :key="item.id">
                          <td
                            class="px-4 py-3 text-sm font-medium text-slate-900"
                          >
                            {{ item.scan_type?.name || "N/A" }}
                          </td>
                          <td class="px-4 py-3 text-sm text-slate-600">
                            {{ item.scan?.name || "N/A" }}
                          </td>
                          <td
                            class="px-4 py-3 text-sm text-slate-500/80 italic"
                          >
                            {{ item.remarks || "---" }}
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
                              @click="viewFile(item.documents)"
                              class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-primary/10 text-primary text-[10px] font-bold hover:bg-primary/20 transition-all active:scale-95"
                            >
                              <FileSearchIcon class="h-3 w-3" />
                              View DICOM
                            </button>
                            <span
                              v-else
                              class="text-[10px] font-bold text-slate-400"
                              >No files</span
                            >
                          </td>
                        </tr>
                        <tr v-if="!report?.items?.length">
                          <td
                            colspan="5"
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

              <!-- Footer - Fixed -->
              <div
                class="bg-slate-50 px-6 py-4 sm:px-10 flex flex-col sm:flex-row justify-between items-center gap-4 shrink-0 border-t border-slate-200 rounded-b-[32px]"
              >
                <div
                  class="flex flex-col items-center sm:items-start text-center sm:text-left"
                >
                  <span
                    class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"
                  >
                    Created on {{ formatDate(report?.created_at) }}
                    <template v-if="report?.added_by_user">
                      by {{ report.added_by_user.name }}
                    </template>
                  </span>
                  <span
                    v-if="
                      report?.modified_by_user &&
                      report?.modified_by !== report?.added_by
                    "
                    class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"
                  >
                    Last modified by {{ report.modified_by_user.name }}
                  </span>
                  <span
                    v-if="report?.expires_at"
                    class="text-[10px] font-bold text-rose-400 uppercase tracking-widest"
                  >
                    Files Expire on
                    {{ formatDate(report?.expires_at) }}
                  </span>
                </div>
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

  <!-- Expired Case Modal -->
  <ConfirmationModal
    :is-open="isExpiredModalOpen"
    title="Case Files Expired"
    description="This case report has expired and the files are no longer accessible directly. Would you like to go to the Edit page to re-upload or update scan items?"
    confirm-label="Go to Edit Page"
    :icon="ClockIcon"
    @close="isExpiredModalOpen = false"
    @confirm="handleReupload"
  />
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
  Clock as ClockIcon,
  MapPin as MapPinIcon,
} from "lucide-vue-next";
import { useRouter } from "vue-router";
import { formatDate } from "../../utils/format";
import ConfirmationModal from "../ui/ConfirmationModal.vue";

const props = defineProps({
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
  if (!props.isOpen) return;
  emit("close");
};

const isExpiredModalOpen = ref(false);

const handleReupload = () => {
  isExpiredModalOpen.value = false;
  router.push(`/case-reports/${props.report.id}/edit`);
};

const viewFile = (pathOrPaths) => {
  if (!pathOrPaths) return;

  // Normalize to array of strings
  const paths = (Array.isArray(pathOrPaths) ? pathOrPaths : [pathOrPaths]).map(
    (p) => (typeof p === "object" ? p.path : p),
  );

  const firstPath = paths[0];
  if (!firstPath) return;

  const ext = firstPath.split(".").pop().toLowerCase();
  const isDicomFolder = firstPath.includes("app/case-reports");

  if (ext === "dcm" || isDicomFolder) {
    // Check if expired status
    if (props.report?.status === "expired") {
      isExpiredModalOpen.value = true;
      return;
    }

    router.push({
      name: "DicomView",
      query: {
        paths: paths.join(","),
        token: props.report.sharing_token,
      },
    });
  } else {
    window.open("/" + firstPath, "_blank");
  }
};

const isImage = (path) => {
  if (!path) return false;
  const ext = path.split(".").pop().toLowerCase();
  return ["jpg", "jpeg", "png", "webp", "gif"].includes(ext);
};
</script>
