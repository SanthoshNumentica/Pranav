<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-500"
    >
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
          Doctors
        </h1>
        <p class="text-sm text-slate-500 mt-1">Manage referring doctors.</p>
      </div>
      <div class="flex items-center gap-3">
        <button
          v-if="modulePermissions.canAdd"
          @click="openAddModal"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-700 text-white rounded-xl text-sm font-semibold transition-all shadow-md shadow-primary/10 active:scale-95"
        >
          <PlusIcon class="h-4 w-4" />
          Add Doctor
        </button>
      </div>
    </div>

    <!-- Filters & Search -->
    <div
      class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm animate-in fade-in duration-700 delay-100"
    >
      <div
        class="flex flex-col md:flex-row md:items-center justify-between gap-4"
      >
        <div class="flex flex-wrap items-center gap-3 flex-1">
          <!-- Search Inner -->
          <div class="relative w-full md:w-72 group">
            <SearchIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
            />
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search Name, Email..."
              class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              @input="debouncedFetch"
            />
          </div>

          <div class="relative w-full md:w-48">
            <Select v-model="filters.status" @update:modelValue="fetchDoctors">
              <SelectTrigger class="w-full pl-10">
                <SelectValue placeholder="Status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="active">Active Doctors</SelectItem>
                <SelectItem value="inactive">Inactive Doctors</SelectItem>
                <SelectItem value="all">All Doctors</SelectItem>
              </SelectContent>
            </Select>
            <FilterIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none group-focus-within:text-primary transition-colors"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Main Table Container -->
    <div
      class="bg-white rounded-3xl border border-slate-200 shadow-soft-xl overflow-x-auto custom-scrollbar animate-in fade-in slide-in-from-bottom-4 duration-700 delay-200"
    >
      <DoctorsTable
        :doctors="doctors"
        :loading="loading"
        :permissions="modulePermissions"
        @view-info="handleView"
        @edit="handleEdit"
        @delete="handleDelete"
        @toggle-status="handleToggleStatus"
      />
      <!-- Pagination -->
      <Pagination
        v-if="pagination"
        :pagination="pagination"
        @page-change="handlePageChange"
      />
    </div>

    <!-- Modals -->
    <DoctorFormDialog
      :is-open="isFormModalOpen"
      :doctor="selectedDoctor"
      @close="isFormModalOpen = false"
      @saved="fetchDoctors"
    />

    <DoctorInfoDialog
      :is-open="isInfoModalOpen"
      :doctor="selectedDoctor"
      @close="isInfoModalOpen = false"
      @edit="handleEditFromInfo"
    />

    <ConfirmationModal
      :is-open="isDeleteModalOpen"
      title="Delete Doctor"
      :description="`Are you sure you want to delete doctor ${doctorToDelete?.name}? This action will move the record to trash.`"
      confirm-label="Delete Doctor"
      variant="danger"
      :icon="Trash2Icon"
      :loading="isDeleting"
      @close="isDeleteModalOpen = false"
      @confirm="confirmDelete"
    />

    <ConfirmationModal
      :is-open="isStatusModalOpen"
      title="Update Status"
      :description="`Are you sure you want to change the status of ${doctorToToggle?.name} to ${nextStatus}?`"
      confirm-label="Update Status"
      variant="warning"
      :icon="AlertCircleIcon"
      :loading="isStatusUpdating"
      @close="isStatusModalOpen = false"
      @confirm="confirmToggleStatus"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from "vue";
import {
  Plus as PlusIcon,
  Search as SearchIcon,
  Filter as FilterIcon,
  ChevronDown as ChevronDownIcon,
  Trash2 as Trash2Icon,
  AlertCircle as AlertCircleIcon,
} from "lucide-vue-next";
import axios from "axios";
import { debounce } from "lodash";
import DoctorsTable from "../../components/doctors/DoctorsTable.vue";
import DoctorFormDialog from "../../components/doctors/DoctorFormDialog.vue";
import DoctorInfoDialog from "../../components/doctors/DoctorInfoDialog.vue";
import ConfirmationModal from "../../components/ui/ConfirmationModal.vue";
import Pagination from "../../components/ui/Pagination.vue";
import { useToast } from "../../composables/useToast";
import { usePermissions } from "../../composables/usePermissions";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../../components/ui/select";

const { addToast } = useToast();
const { getModulePermissions } = usePermissions();
const modulePermissions = getModulePermissions("doctors");

const doctors = ref([]);
const pagination = ref(null);
const loading = ref(true);
const filters = reactive({
  search: "",
  status: "active",
});

const isFormModalOpen = ref(false);
const isInfoModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const isStatusModalOpen = ref(false);
const isStatusUpdating = ref(false);
const selectedDoctor = ref(null);
const doctorToDelete = ref(null);
const doctorToToggle = ref(null);
const nextStatus = ref("");

const fetchDoctors = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/doctors", {
      params: { ...filters, page },
    });
    if (response.data.success) {
      doctors.value = response.data.data.data;
      pagination.value = response.data.data;
    }
  } catch (error) {
    console.error("Failed to fetch doctors", error);
  } finally {
    loading.value = false;
  }
};

const handlePageChange = (page) => {
  fetchDoctors(page);
};

const debouncedFetch = debounce(fetchDoctors, 300);

const openAddModal = () => {
  selectedDoctor.value = null;
  isFormModalOpen.value = true;
};

const handleView = async (doctor) => {
  try {
    const res = await axios.get(`/api/v1/doctors/${doctor.id}`);
    selectedDoctor.value = res.data.data;
    isInfoModalOpen.value = true;
  } catch (err) {
    console.error("Failed to fetch doctor details", err);
  }
};

const handleEdit = (doctor) => {
  selectedDoctor.value = doctor;
  isFormModalOpen.value = true;
};

const handleEditFromInfo = (doctor) => {
  isInfoModalOpen.value = false;
  handleEdit(doctor);
};

const handleDelete = (doctor) => {
  doctorToDelete.value = doctor;
  isDeleteModalOpen.value = true;
};

const confirmDelete = async () => {
  if (!doctorToDelete.value) return;
  isDeleting.value = true;
  try {
    await axios.delete(`/api/v1/doctors/${doctorToDelete.value.id}`);
    fetchDoctors();
    isDeleteModalOpen.value = false;
    addToast({
      title: "Success",
      description: "Doctor moved to trash successfully.",
      variant: "success",
    });
  } catch (err) {
    console.error("Failed to delete doctor", err);
    addToast({
      title: "Error",
      description: "Failed to delete doctor.",
      variant: "error",
    });
  } finally {
    isDeleting.value = false;
  }
};

const handleToggleStatus = (doctor) => {
  doctorToToggle.value = doctor;
  nextStatus.value = doctor.status === "active" ? "inactive" : "active";
  isStatusModalOpen.value = true;
};

const confirmToggleStatus = async () => {
  if (!doctorToToggle.value) return;
  isStatusUpdating.value = true;
  try {
    await axios.post(`/api/v1/doctors/${doctorToToggle.value.id}/status`, {
      status: nextStatus.value,
    });
    fetchDoctors();
    isStatusModalOpen.value = false;
    addToast({
      title: "Success",
      description: "Doctor status updated successfully.",
      variant: "success",
    });
  } catch (err) {
    console.error("Failed to toggle status", err);
    addToast({
      title: "Error",
      description: "Failed to update doctor status.",
      variant: "error",
    });
  } finally {
    isStatusUpdating.value = false;
  }
};

onMounted(() => {
  fetchDoctors(1);
});
</script>
