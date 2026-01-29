<template>
  <div class="max-w-5xl mx-auto space-y-8 pb-20">
    <!-- Page Header -->
    <div
      class="flex items-center justify-between animate-in fade-in slide-in-from-top-4 duration-500"
    >
      <div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">
          Edit Case Report
        </h1>
        <p class="text-slate-500 mt-2 font-medium">
          Update diagnostic case details and manage DICOM files.
        </p>
      </div>
      <button
        @click="$router.push('/case-reports')"
        class="px-5 py-2.5 rounded-2xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-50 transition-all active:scale-95"
      >
        Cancel
      </button>
    </div>

    <!-- Main Form Grid -->
    <form
      v-if="!fetching"
      @submit.prevent="handleSubmit"
      class="grid grid-cols-1 lg:grid-cols-3 gap-8"
    >
      <!-- Left Column: Case Information -->
      <div
        class="lg:col-span-1 space-y-8 animate-in fade-in slide-in-from-left-4 duration-700 delay-100"
      >
        <div
          class="bg-white rounded-[32px] border border-slate-200 p-8 shadow-soft-xl space-y-6"
        >
          <div class="flex items-center gap-3 text-slate-900 mb-2">
            <div
              class="h-10 w-10 rounded-2xl bg-primary/10 flex items-center justify-center"
            >
              <UserIcon class="h-5 w-5 text-primary" />
            </div>
            <h3 class="font-bold text-lg">Case Info</h3>
          </div>

          <div class="space-y-1">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
              >Case ID</label
            >
            <p class="text-sm font-black text-slate-700 ml-1">
              {{ form.case_id }}
            </p>
          </div>

          <!-- Patient Selection -->
          <div class="space-y-2">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
              >Patient <span class="text-rose-500">*</span></label
            >
            <div class="relative">
              <Select v-model="form.patient_fk_id" required>
                <SelectTrigger>
                  <SelectValue placeholder="Select Patient" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="patient in patients"
                    :key="patient.id"
                    :value="patient.id.toString()"
                  >
                    {{ patient.name }} ({{ patient.patient_id }})
                  </SelectItem>
                </SelectContent>
              </Select>
              <ChevronDownIcon
                class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none"
              />
            </div>
          </div>

          <!-- Doctor Selection -->
          <div class="space-y-2">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
              >Referring Doctor <span class="text-rose-500">*</span></label
            >
            <div class="relative">
              <Select v-model="form.doc_ref_fk_id" required>
                <SelectTrigger>
                  <SelectValue placeholder="Select Doctor" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="doctor in doctors"
                    :key="doctor.id"
                    :value="doctor.id.toString()"
                  >
                    {{ doctor.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <ChevronDownIcon
                class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none"
              />
            </div>
          </div>

          <!-- General Documents -->
          <div class="space-y-2">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
              >Case Documents (JPG, PNG)</label
            >
            <div class="flex items-center gap-3">
              <label
                class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-primary/30 transition-all cursor-pointer group relative overflow-hidden"
              >
                <input
                  type="file"
                  multiple
                  accept="image/jpeg,image/png,application/pdf"
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
                    >Attach Files</span
                  >
                </template>
                <template v-else>
                  <Loader2Icon class="h-4 w-4 text-primary animate-spin" />
                  <span class="text-xs font-bold text-primary"
                    >Processing...</span
                  >
                </template>
              </label>
            </div>

            <div
              v-if="form.documents.length > 0"
              class="flex flex-wrap gap-2 mt-2"
            >
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
                <span class="truncate max-w-[80px]">{{
                  getFileName(doc.path)
                }}</span>
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

          <div class="space-y-2">
            <label
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
              >Description (Optional)</label
            >
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full rounded-2xl py-3.5 px-4 text-sm border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 transition-all outline-none resize-none"
              placeholder="Case history or notes..."
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Right Column: Scan Items -->
      <div
        class="lg:col-span-2 space-y-8 animate-in fade-in slide-in-from-right-4 duration-700 delay-200"
      >
        <div
          class="bg-white rounded-[40px] border border-slate-200 p-8 md:p-10 shadow-soft-xl"
        >
          <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
              <div
                class="h-10 w-10 rounded-2xl bg-primary/10 flex items-center justify-center"
              >
                <ActivityIcon class="h-5 w-5 text-primary" />
              </div>
              <h3 class="font-bold text-lg text-slate-900">
                Scan Items & Documents
              </h3>
            </div>
            <button
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
                v-if="form.items.length > 1"
                type="button"
                @click="removeItem(index)"
                class="absolute -right-2 -top-2 h-8 w-8 rounded-full bg-white border border-slate-100 text-slate-400 hover:text-rose-500 hover:border-rose-100 hover:bg-rose-50 transition-all shadow-sm flex items-center justify-center"
              >
                <XIcon class="h-4 w-4" />
              </button>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Scan Selection -->
                <div class="space-y-4">
                  <div class="space-y-2">
                    <label
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >Scan Type <span class="text-rose-500">*</span></label
                    >
                    <div class="relative group/select">
                      <div
                        class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 group-hover/select:text-primary transition-colors"
                      >
                        <ActivityIcon class="h-4 w-4" />
                      </div>
                      <Select v-model="item.scan_type_id" required>
                        <SelectTrigger class="pl-10">
                          <SelectValue placeholder="Select Type" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem
                            v-for="type in scanTypes"
                            :key="type.id"
                            :value="type.id.toString()"
                          >
                            {{ type.name }}
                          </SelectItem>
                        </SelectContent>
                      </Select>
                      <ChevronDownIcon
                        class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none group-hover/select:text-primary transition-colors"
                      />
                    </div>
                  </div>

                  <div class="space-y-2">
                    <label
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >Specific Scan <span class="text-rose-500">*</span></label
                    >
                    <div class="relative group/select">
                      <div
                        class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 group-hover/select:text-primary transition-colors"
                      >
                        <ActivityIcon class="h-4 w-4" />
                      </div>
                      <Select
                        v-model="item.scan_id"
                        required
                        :disabled="!item.scan_type_id"
                      >
                        <SelectTrigger class="pl-10">
                          <SelectValue placeholder="Select Scan" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem
                            v-for="scan in getScans(item.scan_type_id)"
                            :key="scan.id"
                            :value="scan.id.toString()"
                          >
                            {{ scan.name }}
                          </SelectItem>
                        </SelectContent>
                      </Select>
                      <ChevronDownIcon
                        class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none group-hover/select:text-primary transition-colors"
                      />
                    </div>
                  </div>
                </div>

                <!-- File Collection -->
                <div class="space-y-4">
                  <div class="space-y-2">
                    <label
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >DICOM Files (.dcm)</label
                    >
                    <div
                      class="relative h-[112px] rounded-2xl border-2 border-dashed border-slate-200 bg-white flex flex-col items-center justify-center p-4 transition-all hover:border-primary/50 group/upload overflow-hidden"
                      @dragover.prevent
                      @drop.prevent="handleDrop($event, index)"
                    >
                      <input
                        type="file"
                        multiple
                        accept=".dcm"
                        class="absolute inset-0 opacity-0 cursor-pointer"
                        @change="handleFiles($event, index)"
                      />
                      <div
                        class="flex flex-col items-center gap-2 pointer-events-none"
                      >
                        <div
                          class="p-2 rounded-xl bg-slate-50 group-hover/upload:bg-primary/10 transition-colors"
                        >
                          <UploadIcon
                            v-if="!item.processing"
                            class="h-5 w-5 text-slate-400 group-hover/upload:text-primary"
                          />
                          <Loader2Icon
                            v-else
                            class="h-5 w-5 text-primary animate-spin"
                          />
                        </div>
                        <span
                          class="text-[11px] font-bold text-slate-500 group-hover/upload:text-primary"
                        >
                          {{
                            item.processing
                              ? "Analyzing files..."
                              : "Click or drag DICOM files"
                          }}
                        </span>
                      </div>
                    </div>

                    <!-- File List Preview -->
                    <div
                      v-if="item.documents.length > 0"
                      class="mt-3 flex flex-wrap gap-2"
                    >
                      <div
                        v-for="(doc, dIdx) in item.documents"
                        :key="dIdx"
                        class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-100 rounded-lg text-[10px] font-bold text-emerald-600 animate-in zoom-in-95 duration-200"
                      >
                        <CheckCircleIcon class="h-3 w-3" />
                        <span class="truncate max-w-[100px]">{{
                          getFileName(doc.path)
                        }}</span>
                        <button
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
                      >Remarks</label
                    >
                    <textarea
                      v-model="item.remarks"
                      rows="2"
                      class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all placeholder:text-slate-300 resize-none font-medium text-slate-600"
                      placeholder="Special instructions or notes for this scan..."
                    ></textarea>
                  </div>
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

          <!-- Final Actions -->
          <div
            class="mt-12 flex items-center justify-end gap-4 border-t border-slate-100 pt-8"
          >
            <div
              v-if="error"
              class="text-xs text-rose-500 font-bold px-4 flex items-center gap-2 animate-in fade-in"
            >
              <AlertCircleIcon class="h-3.5 w-3.5" />
              {{ error }}
            </div>
            <button
              type="submit"
              :disabled="loading"
              class="px-10 py-4 bg-primary text-white rounded-2xl font-bold text-sm shadow-xl shadow-primary/20 hover:opacity-90 active:scale-[0.98] transition-all disabled:opacity-50 flex items-center gap-3"
            >
              <Loader2Icon v-if="loading" class="h-4 w-4 animate-spin" />
              {{ loading ? "Updating..." : "Save Changes" }}
            </button>
          </div>
        </div>
      </div>
    </form>

    <!-- Loading State -->
    <div v-else class="h-96 flex flex-col items-center justify-center gap-4">
      <Loader2Icon class="h-12 w-12 text-primary animate-spin" />
      <p class="text-slate-500 font-bold">Loading case specifics...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import axios from "axios";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../../components/ui/select";
import {
  User as UserIcon,
  Activity as ActivityIcon,
  Plus as PlusIcon,
  X as XIcon,
  Upload as UploadIcon,
  Loader2 as Loader2Icon,
  AlertCircle as AlertCircleIcon,
  ChevronDown as ChevronDownIcon,
  Paperclip as PaperclipIcon,
  Check as CheckIcon,
  CheckCircle as CheckCircleIcon,
} from "lucide-vue-next";

const router = useRouter();
const route = useRoute();
const { addToast } = useToast();
const loading = ref(false);
const fetching = ref(true);
const error = ref(null);

const patients = ref([]);
const doctors = ref([]);
const scanTypes = ref([]);

const processingGeneral = ref(false);

const form = reactive({
  id: null,
  case_id: "",
  patient_fk_id: "",
  doc_ref_fk_id: "",
  description: "",
  documents: [], // General documents
  items: [],
});

onMounted(async () => {
  try {
    const [pRes, dRes, sRes, cRes] = await Promise.all([
      axios.get("/api/v1/masters/patients?status=active&nopaginate=1"),
      axios.get("/api/v1/masters/doctors?status=active&nopaginate=1"),
      axios.get("/api/v1/masters/scan-types?status=active&nopaginate=1"),
      axios.get(`/api/v1/case-reports/${route.params.id}`),
    ]);

    patients.value = pRes.data.data;
    doctors.value = dRes.data.data;
    scanTypes.value = sRes.data.data;

    // Populate form
    const data = cRes.data.data;
    form.id = data.id;
    form.case_id = data.case_id;
    form.patient_fk_id = data.patient_fk_id;
    form.doc_ref_fk_id = data.doc_ref_fk_id;
    form.description = data.description || "";
    form.documents = (data.documents || []).map((path) => ({ path }));

    form.items = data.items.map((item) => ({
      scan_type_id: item.scan_type_id,
      scan_id: item.scan_id,
      documents: (item.documents || []).map((path) => ({ path })),
      remarks: item.remarks || "",
      processing: false,
    }));
  } catch (err) {
    console.error("Failed to fetch data", err);
    error.value = "Failed to load case data. Please refresh.";
  } finally {
    fetching.value = false;
  }
});

const getScans = (typeId) => {
  if (!typeId) return [];
  const type = scanTypes.value.find((t) => t.id == typeId);
  return type ? type.scans : [];
};

const getFileName = (path) => {
  if (!path) return "";
  return path.split("/").pop();
};

const addItem = () => {
  form.items.push({
    scan_type_id: "",
    scan_id: "",
    documents: [],
    remarks: "",
    processing: false,
  });
};

const removeItem = (index) => {
  form.items.splice(index, 1);
};

const handleGeneralFiles = async (event) => {
  const files = Array.from(event.target.files);
  if (files.length === 0) return;

  processingGeneral.value = true;
  error.value = null;

  try {
    for (const file of files) {
      const formData = new FormData();
      formData.append("file", file);
      formData.append("type", "document");

      const response = await axios.post("/api/v1/files/upload", formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });

      if (response.data.success) {
        form.documents.push({
          path: response.data.path,
        });
      }
    }
  } catch (err) {
    console.error("Upload failed", err);
    error.value = "Failed to upload document. Please try again.";
  } finally {
    processingGeneral.value = false;
  }
};

const removeGeneralDoc = (index) => {
  form.documents.splice(index, 1);
};

const handleFiles = async (event, index) => {
  const files = Array.from(event.target.files);
  await uploadDicomFiles(files, index);
};

const handleDrop = async (event, index) => {
  const files = Array.from(event.dataTransfer.files);
  await uploadDicomFiles(files, index);
};

const uploadDicomFiles = async (files, index) => {
  const validFiles = files.filter((f) => f.name.toLowerCase().endsWith(".dcm"));
  if (validFiles.length === 0) return;

  form.items[index].processing = true;
  error.value = null;

  try {
    for (const file of validFiles) {
      const formData = new FormData();
      formData.append("file", file);
      formData.append("type", "dicom");

      const response = await axios.post("/api/v1/files/upload", formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });

      if (response.data.success) {
        form.items[index].documents.push({
          path: response.data.path,
        });
      }
    }
  } catch (err) {
    console.error("DICOM Upload failed", err);
    error.value = "Failed to upload DICOM file. Please try again.";
  } finally {
    form.items[index].processing = false;
  }
};

const removeDoc = (itemIndex, docIndex) => {
  form.items[itemIndex].documents.splice(docIndex, 1);
};

const handleSubmit = async () => {
  loading.value = true;
  error.value = null;

  try {
    const payload = {
      patient_fk_id: form.patient_fk_id,
      doc_ref_fk_id: form.doc_ref_fk_id,
      description: form.description,
      documents: form.documents.map((d) => d.path),
      items: form.items.map((item) => ({
        scan_type_id: item.scan_type_id,
        scan_id: item.scan_id,
        documents: item.documents.map((d) => d.path),
        remarks: item.remarks,
      })),
    };

    const response = await axios.put(
      `/api/v1/case-reports/${form.id}`,
      payload,
    );

    if (response.data.success) {
      addToast({
        title: "Success",
        description: "Case report updated successfully.",
        variant: "success",
      });
      router.push("/case-reports");
    }
  } catch (err) {
    console.error("Save failed", err);
    error.value =
      err.response?.data?.message || "Failed to update case report.";
    addToast({
      title: "Error",
      description: error.value,
      variant: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>
