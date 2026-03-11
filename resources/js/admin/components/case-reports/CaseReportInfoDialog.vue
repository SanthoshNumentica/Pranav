<template>
  <TransitionRoot as="template" :show="isOpen">
    <Dialog as="div" class="relative z-50" @close="() => { }">
      <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild as="template" enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <DialogPanel
              class="relative transform overflow-hidden rounded-[32px] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border border-slate-200 flex flex-col h-[90vh] sm:h-[85vh]">
              <!-- Header/Banner - Fixed -->
              <div class="relative bg-primary px-6 py-8 sm:px-10 text-white overflow-hidden shrink-0">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>

                <div class="relative flex items-center justify-between">
                  <div class="flex items-center gap-4">
                    <div
                      class="h-12 w-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30">
                      <FileTextIcon class="h-6 w-6 text-white" />
                    </div>
                    <div>
                      <h3 class="text-xl font-bold tracking-tight">
                        Case Report Details
                      </h3>
                      <div class="flex items-center gap-2 mt-1 opacity-90">
                        <span class="text-xs font-bold uppercase tracking-wider opacity-60">Case Id:</span>
                        <span class="text-sm font-bold">{{
                          report?.case_id
                        }}</span>
                        <span class="h-1 w-1 rounded-full bg-white/50"></span>
                        <span
                          class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full border border-white/20">
                          {{ report?.status }}
                        </span>
                        <template v-if="report?.expires_at">
                          <span class="h-1 w-1 rounded-full bg-white/50"></span>
                          <span
                            class="text-[10px] font-bold uppercase tracking-wider bg-rose-500/40 px-2 py-0.5 rounded-full border border-white/20">
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
                    <button @click.stop="close" class="p-2 rounded-xl hover:bg-white/10 transition-colors">
                      <XIcon class="h-5 w-5" />
                    </button>
                  </div>
                </div>
              </div>

              <!-- Content - Scrollable -->
              <div class="p-6 sm:p-10 space-y-8 overflow-y-auto flex-grow custom-scrollbar">
                <!-- Patient & Doctor Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                  <!-- Patient Section -->
                  <div class="space-y-4 flex flex-col">
                    <div class="flex items-center gap-3 text-slate-900">
                      <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center">
                        <UserIcon class="h-4 w-4 text-primary" />
                      </div>
                      <h4 class="text-sm font-bold uppercase tracking-widest text-slate-400">
                        Patient Details
                      </h4>
                    </div>

                    <div class="space-y-3 bg-slate-50/50 p-6 rounded-[32px] border border-slate-100 flex-grow">
                      <div class="flex justify-between items-start">
                        <span class="text-xs font-semibold text-slate-500">Name</span>
                        <span class="text-sm font-bold text-slate-900">{{
                          report?.patient?.name || "N/A"
                        }}</span>
                      </div>
                      <div class="flex justify-between items-start">
                        <span class="text-xs font-semibold text-slate-500">Patient ID</span>
                        <span class="text-sm font-medium text-primary">{{
                          report?.patient?.patient_id || "N/A"
                        }}</span>
                      </div>
                      <div class="flex justify-between items-start">
                        <span class="text-xs font-semibold text-slate-500">Mobile</span>
                        <span class="text-sm font-medium text-slate-700">{{
                          report?.patient?.mobile_no || "N/A"
                        }}</span>
                      </div>
                      <div class="flex justify-between items-start">
                        <span class="text-xs font-semibold text-slate-500">Gender</span>
                        <span class="text-sm font-medium text-slate-700">{{
                          report?.patient?.gender?.gender_name || report?.patient?.gender_name || "N/A"
                        }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Doctor Section -->
                  <div class="space-y-4 flex flex-col">
                    <div class="flex items-center gap-3 text-slate-900">
                      <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center">
                        <StethoscopeIcon class="h-4 w-4 text-primary" />
                      </div>
                      <h4 class="text-sm font-bold uppercase tracking-widest text-slate-400">
                        Referer Details
                      </h4>
                    </div>

                    <div class="space-y-3 bg-slate-50/50 p-6 rounded-[32px] border border-slate-100 flex-grow">
                      <div class="flex justify-between items-start">
                        <span class="text-xs font-semibold text-slate-500">Name</span>
                        <span class="text-sm font-bold text-slate-900">{{
                          report?.referer?.name || "N/A"
                        }}</span>
                      </div>
                      <div class="flex justify-between items-start">
                        <span class="text-xs font-semibold text-slate-500">Referer ID</span>
                        <span class="text-sm font-medium text-slate-700">{{
                          report?.referer?.referer_id || "N/A"
                        }}</span>
                      </div>
                      <div class="flex justify-between items-start">
                        <span class="text-xs font-semibold text-slate-500">Type</span>
                        <span class="text-sm font-medium text-primary">{{
                          report?.referer?.referer_type?.name || "N/A"
                        }}</span>
                      </div>
                      <div class="flex justify-between items-start">
                        <span class="text-xs font-semibold text-slate-500">Mobile</span>
                        <span class="text-sm font-medium text-slate-700">{{
                          report?.referer?.mobile_no || "N/A"
                        }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Case Metadata Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Scanning Info -->
                  <div
                    class="bg-slate-50/50 p-5 rounded-3xl border border-slate-100 flex flex-col justify-center space-y-3">
                    <!-- Scanning Date -->
                    <div class="flex justify-between items-center text-sm">
                      <div class="flex items-center gap-2 text-slate-500">
                        <CalendarIcon class="h-4 w-4" />
                        <span class="font-medium">Scanning Date</span>
                      </div>
                      <span class="font-bold text-slate-900">{{
                        report?.scanning_date ? formatDate(report.scanning_date) : "N/A"
                      }}</span>
                    </div>

                    <div class="w-full h-px bg-slate-200/60 my-1"></div>

                    <!-- Timings -->
                    <div class="grid grid-cols-2 gap-4">
                      <div class="space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Check In</span>
                        <div class="flex items-center gap-1.5 text-slate-700 font-medium">
                          <ClockIcon class="h-3.5 w-3.5" />
                          <span>{{ report?.check_in || "---" }}</span>
                        </div>
                      </div>
                      <div class="space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Check Out</span>
                        <div class="flex items-center gap-1.5 text-slate-700 font-medium">
                          <ClockIcon class="h-3.5 w-3.5" />
                          <span>{{ report?.check_out || "---" }}</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Status Badges -->
                  <div class="bg-slate-50/50 p-5 rounded-3xl border border-slate-100 flex items-center gap-4">
                    <!-- STAT Badge -->
                    <div v-if="report?.is_stat_case" class="flex flex-col gap-1">
                      <span class="text-[10px] font-bold uppercase tracking-wider text-rose-400">Priority</span>
                      <div
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 text-rose-600 border border-rose-100">
                        <ZapIcon class="h-3 w-3 fill-rose-600" />
                        <span class="text-[10px] font-black uppercase">STAT</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Case History and Documents Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                  <!-- Case History -->
                  <div v-if="report?.description" class="space-y-4 flex flex-col">
                    <div class="flex items-center gap-3 text-slate-900">
                      <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center">
                        <MessageSquareIcon class="h-4 w-4 text-primary" />
                      </div>
                      <h4 class="text-sm font-bold uppercase tracking-widest text-slate-400">
                        Case History
                      </h4>
                    </div>
                    <div
                      class="p-6 bg-slate-50/50 rounded-[32px] border border-slate-100 text-sm text-slate-600 leading-relaxed flex-grow min-h-[100px]">
                      {{ report.description }}
                    </div>
                  </div>

                  <!-- Case Documents Preview -->
                  <div v-if="report?.documents?.length" class="space-y-4 flex flex-col">
                    <div class="flex items-center gap-3 text-slate-900">
                      <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center">
                        <PaperclipIcon class="h-4 w-4 text-primary" />
                      </div>
                      <h4 class="text-sm font-bold uppercase tracking-widest text-slate-400">
                        Documents
                      </h4>
                    </div>

                    <div
                      class="grid grid-cols-2 sm:grid-cols-3 gap-4 p-4 bg-slate-50/50 rounded-[32px] border border-slate-100 flex-grow">
                      <div v-for="(doc, idx) in report.documents" :key="idx"
                        class="group relative aspect-square rounded-[24px] bg-white border border-slate-200 overflow-hidden hover:border-primary/50 transition-all cursor-pointer shadow-sm"
                        @click="viewFile(doc)">
                        <img v-if="isImage(doc)" :src="'/' + doc"
                          class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                          alt="Preview" />
                        <div v-else class="w-full h-full flex flex-col items-center justify-center gap-1 p-2">
                          <FileTextIcon class="h-8 w-8 text-slate-300 group-hover:text-primary transition-colors" />
                          <span class="text-[10px] font-bold text-slate-500 truncate w-full text-center px-1">
                            {{ doc.split("/").pop() }}
                          </span>
                        </div>
                        <div
                          class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                          <button @click.stop="viewFile(doc)"
                            class="h-9 w-9 rounded-xl bg-white text-primary flex items-center justify-center shadow-lg hover:scale-110 transition-transform"
                            title="View">
                            <EyeIcon class="h-4.5 w-4.5" />
                          </button>
                          <a :href="'/' + doc" download @click.stop
                            class="h-9 w-9 rounded-xl bg-white text-emerald-600 flex items-center justify-center shadow-lg hover:scale-110 transition-transform"
                            title="Download">
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
                    <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center">
                      <ActivityIcon class="h-4 w-4 text-primary" />
                    </div>
                    <h4 class="text-sm font-bold uppercase tracking-widest text-slate-400">
                      Scan Items / Services
                    </h4>
                  </div>

                  <div class="overflow-x-auto custom-scrollbar rounded-3xl border border-slate-200">
                    <table class="w-full text-left">
                      <thead class="bg-slate-50/50">
                        <tr>
                          <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                            Scan Type
                          </th>
                          <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                            Specific Scan
                          </th>
                          <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                            Remarks
                          </th>
                          <th
                            class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 text-center">
                            Files
                          </th>
                          <th
                            class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 text-right">
                            Actions
                          </th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100">
                        <tr v-for="item in report?.items" :key="item.id">
                          <td class="px-4 py-3 text-sm font-bold text-slate-900">
                            <div class="flex items-center gap-2">
                              <span class="w-1.5 h-1.5 rounded-full bg-primary/40"></span>
                              {{ item.scan_type?.name || "N/A" }}
                            </div>
                          </td>
                          <td class="px-4 py-3 text-sm text-slate-600">
                            <div class="flex flex-wrap gap-1.5">
                              <span v-for="scan in extractScanNames(item)" :key="scan.id"
                                class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100/50 text-slate-700 text-[10px] font-bold border border-slate-200/60 shadow-sm hover:shadow-md hover:bg-white transition-all duration-300 transform hover:-translate-y-0.5 cursor-default">
                                {{ scan.name }}
                              </span>
                              <span v-if="!extractScanNames(item).length" class="text-slate-400 italic text-[10px]">No
                                specific scans
                                listed</span>
                            </div>
                          </td>
                          <td class="px-4 py-3 text-sm text-slate-500/80 italic">
                            {{ item.remarks || "---" }}
                          </td>
                          <td class="px-4 py-3 text-sm text-center">
                            <span
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-primary border border-blue-100">
                              {{ item.documents?.length || 0 }}
                            </span>
                          </td>
                          <td class="px-4 py-3 text-sm text-right">
                            <button v-if="item.documents?.length" @click="viewFile(item.documents)"
                              class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-primary/10 text-primary text-[10px] font-bold hover:bg-primary/20 transition-all active:scale-95">
                              <FileSearchIcon class="h-3 w-3" />
                              View DICOM
                            </button>
                            <span v-else class="text-[10px] font-bold text-slate-400">No files</span>
                          </td>
                        </tr>
                        <tr v-if="!report?.items?.length">
                          <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-400">
                            No scan items listed.
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Invoice & Payment Details -->
                <div v-if="report?.invoice" class="space-y-4 pt-4 border-t border-slate-100">
                  <div class="flex items-center gap-3 text-slate-900">
                    <div class="h-8 w-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                      <ReceiptIcon class="h-4 w-4 text-emerald-600" />
                    </div>
                    <h4 class="text-sm font-bold uppercase tracking-widest text-slate-400">
                      Invoice & Payment
                    </h4>
                  </div>

                  <!-- Invoice Summary Grid -->
                  <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="bg-slate-50/50 p-4 rounded-3xl border border-slate-100">
                      <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 block mb-1">Total Amount</span>
                      <span class="text-lg font-bold text-slate-700">₹{{ Number(report.invoice.total_amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                    </div>
                    <div class="bg-emerald-50/50 p-4 rounded-3xl border border-emerald-100/50">
                      <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-500/80 block mb-1">Total Paid</span>
                      <span class="text-lg font-bold text-emerald-600">₹{{ Number(report.invoice.paid_amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                    </div>
                    <div class="bg-rose-50 p-4 rounded-3xl border border-rose-100/50">
                      <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-rose-500/80 block mb-1">Due Amount</span>
                      <span class="text-lg font-black text-rose-600">₹{{ Math.max(0, Number(report.invoice.total_amount || 0) - Number(report.invoice.paid_amount || 0)).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                    </div>
                  </div>

                  <!-- Payment History Table -->
                  <div class="overflow-x-auto custom-scrollbar rounded-3xl border border-slate-200">
                    <table class="w-full text-left">
                      <thead class="bg-slate-50/50">
                        <tr>
                          <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">Date</th>
                          <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">Method</th>
                          <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">Notes</th>
                          <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 text-right">Amount</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100">
                        <template v-if="report.invoice.payments && report.invoice.payments.length > 0">
                          <template v-for="payment in report.invoice.payments" :key="payment.id">
                            <!-- Handle compound payments via payment_details JSON -->
                            <template v-if="getParsedPaymentDetails(payment) && getParsedPaymentDetails(payment).payments">
                              <tr v-for="(subPayment, idx) in getParsedPaymentDetails(payment).payments" :key="payment.id + '-' + idx">
                                <td class="px-4 py-3 text-sm font-medium text-slate-700">
                                  {{ formatDate(payment.payment_date || subPayment.payment_date) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600 font-medium">
                                  {{ subPayment.method_name || 'Payment' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-500 italic max-w-[200px] truncate" :title="subPayment.notes || getParsedPaymentDetails(payment).notes">
                                  {{ subPayment.notes || getParsedPaymentDetails(payment).notes || "---" }}
                                </td>
                                <td class="px-4 py-3 text-sm font-bold text-primary text-right">
                                  ₹{{ Number(subPayment.amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 }) }}
                                </td>
                              </tr>
                            </template>
                            <!-- Handle single payment legacy format -->
                            <template v-else>
                              <tr>
                                <td class="px-4 py-3 text-sm font-medium text-slate-700">
                                  {{ formatDate(payment.payment_date) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600 font-medium">
                                  {{ payment.payment_method?.name || 'Payment' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-500 italic max-w-[200px] truncate" :title="payment.notes">
                                  {{ payment.notes || "---" }}
                                </td>
                                <td class="px-4 py-3 text-sm font-bold text-primary text-right">
                                  ₹{{ Number(payment.amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 }) }}
                                </td>
                              </tr>
                            </template>
                          </template>
                        </template>
                        <tr v-else>
                          <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-400">
                            No payment history available.
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Footer - Fixed -->
              <div
                class="bg-slate-50 px-6 py-4 sm:px-10 flex flex-col sm:flex-row justify-between items-center gap-4 shrink-0 border-t border-slate-200 rounded-b-[32px]">
                <div class="flex flex-col items-center sm:items-start text-center sm:text-left">
                  <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Created on {{ formatDate(report?.created_at) }}
                    <template v-if="report?.added_by_user">
                      by {{ report.added_by_user.name }}
                    </template>
                  </span>
                  <span v-if="
                    report?.modified_by_user &&
                    report?.modified_by !== report?.added_by
                  " class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Last modified by {{ report.modified_by_user.name }}
                  </span>
                  <span v-if="report?.expires_at" class="text-[10px] font-bold text-rose-400 uppercase tracking-widest">
                    Files Expire on
                    {{ formatDate(report?.expires_at) }}
                  </span>
                </div>
                <button type="button"
                  class="inline-flex w-full justify-center rounded-xl bg-white px-6 py-2.5 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:w-auto transition-all active:scale-95"
                  @click="close">
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
  <ConfirmationModal :is-open="isExpiredModalOpen" title="Case Files Expired"
    description="This case report has expired and the files are no longer accessible directly. Would you like to go to the Edit page to re-upload or update scan items?"
    confirm-label="Go to Edit Page" :icon="ClockIcon" @close="isExpiredModalOpen = false" @confirm="handleReupload" />
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
  Clock as ClockIcon,
  MapPin as MapPinIcon,
  Hash as HashIcon,
  Calendar as CalendarIcon,
  Building2 as HospitalIcon,
  Receipt as ReceiptIcon,
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

const extractScanNames = (item) => {
  if (!item || !item.scan_details) return [];
  const details = typeof item.scan_details === 'string' ? JSON.parse(item.scan_details) : item.scan_details;

  // Handle both array of objects and single object format
  const scansArray = Array.isArray(details) ? details : [details];

  return scansArray
    .filter(s => s && s.scan_fk_id)
    .map((s, index) => ({
      id: s.scan_fk_id || index,
      name: s.scan_name || 'Scan ' + s.scan_fk_id
    }));
};
const getParsedPaymentDetails = (payment) => {
  if (!payment || !payment.payment_details) return null;
  resetParsedMethodNames();
  try {
    const details = typeof payment.payment_details === "string" ? JSON.parse(payment.payment_details) : payment.payment_details;
    // Map with payment method names if available via relations (since it's a JSON array it doesn't have the method object)
    if (details.payments) {
       details.payments = details.payments.map((p) => {
           // Provide a fallback name from the JSON if available, otherwise "Payment"
           return {
               ...p,
               method_name: p.method_name || 'Payment'
           };
       });
    }
    return details;
  } catch (e) {
    return null;
  }
};

const resetParsedMethodNames = () => {
    // A trick to make sure we don't leak anything, not needed mostly
};
</script>
