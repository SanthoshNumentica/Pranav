<template>
  <div class="max-w-5xl mx-auto space-y-8 pb-20">
    <!-- Page Header -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
          Edit Case Report
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Update diagnostic case details and management.
        </p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="$router.push('/case-reports')"
          class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-50 transition-all active:scale-95 flex items-center gap-2">
          Cancel
        </button>
      </div>
    </div>

    <!-- Skeleton Loading State -->
    <SkeletonCaseReportLoader v-if="isInitialLoading" />

    <!-- Main Form Tabs -->
    <form v-else @submit.prevent="handleSubmit">
      <TabGroup :selectedIndex="selectedTab" @change="changeTab">
        <TabList class="flex space-x-1 rounded-xl bg-slate-100 p-1 mb-6 overflow-x-auto">
          <Tab v-for="category in categories" as="template" :key="category.name" v-slot="{ selected }">
            <button :class="[
              'w-full rounded-lg py-2.5 text-sm font-bold leading-5 whitespace-nowrap px-4 outline-none',
              'ring-white ring-opacity-60 ring-offset-2 ring-offset-blue-400 focus:outline-none focus:ring-2 transition-all duration-200',
              selected
                ? 'bg-white text-primary shadow'
                : 'text-slate-500 hover:bg-white/[0.12] hover:text-slate-700',
              category.name === 'Files Upload Option' && isFilesTabDisabled
                ? 'opacity-40 cursor-not-allowed grayscale-[0.5] select-none'
                : '',
            ]" :disabled="category.name === 'Files Upload Option' && isFilesTabDisabled"
              :aria-disabled="category.name === 'Files Upload Option' && isFilesTabDisabled"
              :title="category.name === 'Files Upload Option' && isFilesTabDisabled ? 'Files can be uploaded only after the invoice is fully paid.' : ''">
              {{ category.name }}
            </button>
          </Tab>
        </TabList>

        <TabPanels>
          <!-- Case Info Wrapper Tab (Patient + Referer + Case Info + Invoice) -->
          <TabPanel v-if="categories.some((c) => c.name === 'Case Info')"
            class="focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-400 rounded-xl">
            <CaseInfoWrapper :form="form" :patients="patients" :referers="referers" :fetching="fetching"
              :logged-in-user-is-super-admin="loggedInUserIsSuperAdmin" :filtered-branches="filteredBranches"
              :scan-types="scanTypes" :payment-methods="paymentMethods" :get-scans="getScans" :add-item="addItem"
              :remove-item="removeItem" :handle-drop="handleDrop" :handle-files="handleFiles"
              :remove-folder="removeFolder" :remove-doc="removeDoc" :get-unique-folders="getUniqueFolders"
              :processing="loading" :can-edit="hasPermission('case-report-case-info', 'edit')" :sub-total="subTotal"
              :total-amount="totalAmount" :total-paid="totalPaid" :discounts="discounts"
              :add-invoice-item="addInvoiceItem" :remove-invoice-item="removeInvoiceItem"
              :add-payment-row="addPaymentRow" :remove-payment-row="removePaymentRow"
              @new-patient="isPatientDialogOpen = true" @new-referer="isRefererDialogOpen = true"
              @submit="handleSubmit" />
          </TabPanel>

          <!-- Files Upload Option Tab -->
          <TabPanel v-if="categories.some((c) => c.name === 'Files Upload Option')"
            class="focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-400 rounded-xl">
            <FileUploadsTab :form="form" :scan-types="scanTypes" :processing-general="processingGeneral"
              :processing="loading" :get-scans="getScans" :get-unique-folders="getUniqueFolders"
              :handle-general-files="handleGeneralFiles" :remove-general-doc="removeGeneralDoc" :add-item="addItem"
              :remove-item="removeItem" :handle-drop="handleDrop" :handle-files="handleFiles"
              :remove-folder="removeFolder" :remove-doc="removeDoc"
              :can-edit="hasPermission('case-report-files', 'edit')" @back="prevTab" @submit="handleSubmit" />
          </TabPanel>
        </TabPanels>
      </TabGroup>

      <!-- Error Display (global) -->
      <div v-if="error" class="mt-4 text-xs text-rose-500 font-bold px-4 flex items-center gap-2 animate-in fade-in">
        <AlertCircleIcon class="h-3.5 w-3.5" />
        {{ error }}
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center justify-end gap-4 pt-8 border-t border-slate-100">
        <button type="button" @click="$router.push('/case-reports')"
          class="px-8 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition-all active:scale-95">
          Cancel
        </button>
        <button type="submit" :disabled="loading"
          class="group flex items-center gap-2 px-10 py-3 bg-primary text-white rounded-xl font-bold text-sm shadow-lg shadow-primary/20 hover:opacity-90 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
          <Loader2Icon v-if="loading" class="h-4 w-4 animate-spin" />
          <SaveIcon v-else class="h-4 w-4" />
          Update Case Report
        </button>
      </div>
    </form>

    <!-- WhatsApp Recipient Selection Modal -->
    <WhatsAppRecipientModal :is-open="isWhatsappModalOpen" :report="reportForWhatsapp" :loading="sendingWhatsapp"
      :initial-recipients="initialRecipients" @close="handleModalClose" @confirm="handleSendWhatsApp" />

    <!-- DICOM Upload Progress & Confirmation Modal -->
    <DicomUploadModal :is-open="uploadModal.isOpen" :state="uploadModal.state" :file-count="uploadModal.fileCount"
      :progress="uploadModal.progress" :current-file-index="uploadModal.currentFileIndex"
      :total-files="uploadModal.totalFiles" :current-file-name="uploadModal.currentFileName" :title="uploadModal.title"
      :description="uploadModal.description" :variant="uploadModal.variant" @confirm="startBatchedUpload"
      @close="uploadModal.isOpen = false" />

    <!-- Patient Creation Dialog -->
    <PatientFormDialog :is-open="isPatientDialogOpen" @close="isPatientDialogOpen = false"
      @saved="handlePatientSaved" />

    <!-- Referer Creation Dialog -->
    <RefererFormDialog :is-open="isRefererDialogOpen" @close="isRefererDialogOpen = false"
      @saved="handleRefererSaved" />
  </div>
</template>

<script setup>
import { onMounted, ref, computed, watch } from "vue";
import { useRoute } from "vue-router";
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from "@headlessui/vue";
import { usePermissions } from "../../composables/usePermissions";
import { useCaseReportForm } from "../../composables/useCaseReportForm";
import WhatsAppRecipientModal from "../../components/notifications/WhatsAppRecipientModal.vue";
import DicomUploadModal from "../../components/dicom/DicomUploadModal.vue";
import PatientFormDialog from "../../components/patients/PatientFormDialog.vue";
import RefererFormDialog from "../../components/referers/RefererFormDialog.vue";
import CaseInfoWrapper from "../../components/case-reports/tabs/CaseInfoWrapper.vue";
import FileUploadsTab from "../../components/case-reports/tabs/FileUploadsTab.vue";

import {
  Loader2 as Loader2Icon,
  AlertCircle as AlertCircleIcon,
  Save as SaveIcon,
} from "lucide-vue-next";

import SkeletonCaseReportLoader from "../../components/loaders/SkeletonCaseReportLoader.vue";

const isPatientDialogOpen = ref(false);
const isRefererDialogOpen = ref(false);
const selectedTab = ref(0);
const { hasPermission } = usePermissions();

const allCategories = [
  { name: "Case Info", module: "case-report-case-info" },
  { name: "Files Upload Option", module: "case-report-files" },
];

const categories = computed(() => {
  return allCategories.filter((cat) => hasPermission(cat.module, "view"));
});

// Watch Categories to reset selected tab if needed
watch(categories, (newCats) => {
  if (newCats.length > 0 && selectedTab.value >= newCats.length) {
    selectedTab.value = 0;
  }
});

const isFilesTabDisabled = computed(() => {
  return form.status !== 'fully_paid';
});

const changeTab = (index) => {
  if (categories.value[index].name === 'Files Upload Option' && isFilesTabDisabled.value) {
    return;
  }
  selectedTab.value = index;
};

const nextTab = () => {
  if (selectedTab.value < categories.value.length - 1) {
    const nextIndex = selectedTab.value + 1;
    if (categories.value[nextIndex].name === 'Files Upload Option' && isFilesTabDisabled.value) {
      return;
    }
    selectedTab.value++;
  }
};

const prevTab = () => {
  if (selectedTab.value > 0) {
    selectedTab.value--;
  }
};

const route = useRoute();

// Initialize useCaseReportForm with isEdit = true for Edit page
const {
  loading,
  fetching,
  error,
  form,
  patients,
  referers,
  scanTypes,
  discounts,
  processingGeneral,
  uploadModal,
  isWhatsappModalOpen,
  reportForWhatsapp,
  sendingWhatsapp,
  initialRecipients,
  fetchMasters,
  fetchCaseReport,
  getScans,
  getFileName,
  addItem,
  removeItem,
  getUniqueFolders,
  handleGeneralFiles,
  removeGeneralDoc,
  handleDrop,
  handleFiles,
  startBatchedUpload,
  removeFolder,
  removeDoc,
  handleSubmit,
  handleSendWhatsApp,
  handleModalClose,
  loggedInUserIsSuperAdmin,
  filteredBranches,
  subTotal,
  totalAmount,
  addInvoiceItem,
  removeInvoiceItem,
  paymentMethods,
  addPaymentRow,
  removePaymentRow,
  totalPaid,
} = useCaseReportForm(true);

const isInitialLoading = ref(true);

const handlePatientSaved = async (patient) => {
  await fetchMasters();
  if (patient && patient.id) {
    form.patient_fk_id = patient.id.toString();
  }
  isPatientDialogOpen.value = false;
};

const handleRefererSaved = async (referer) => {
  await fetchMasters();
  if (referer && referer.id) {
    form.referer_fk_id = referer.id.toString();
  }
  isRefererDialogOpen.value = false;
};

onMounted(async () => {
  try {
    // Parallel fetching for faster load
    await Promise.all([fetchMasters(), fetchCaseReport(route.params.id)]);
  } catch (e) {
    console.error("Initial load failed", e);
  } finally {
    isInitialLoading.value = false;
  }
});
</script>
