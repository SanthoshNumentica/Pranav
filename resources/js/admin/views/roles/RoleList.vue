<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <PageHeader
      title="Role Management"
      subtitle="Manage user roles and their assigned permissions."
    >
      <template #actions>
        <button
          v-if="canAdd"
          @click="isCreateModalOpen = true"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:opacity-90 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-primary/20 active:scale-95"
        >
          <PlusIcon class="h-4 w-4" />
          Add Role
        </button>
      </template>
    </PageHeader>

    <!-- Filters & Search -->
    <div
      class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm animate-in fade-in duration-700 delay-100 relative z-30"
    >
      <div
        class="flex flex-col md:flex-row md:items-center justify-between gap-4"
      >
        <div class="flex flex-wrap items-center gap-3 flex-1">
          <!-- Search -->
          <div class="relative w-full md:w-72 group">
            <SearchIcon
              class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 group-focus-within:text-primary transition-colors"
            />
            <input
              v-model="search"
              type="text"
              placeholder="Search roles..."
              class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-700"
              style="color: #475569 !important"
            />
          </div>
        </div>

        <div
          class="text-xs font-semibold text-gray-400 uppercase tracking-widest"
        >
          Showing {{ roles.length }} roles
        </div>
      </div>
    </div>

    <!-- Main Table Container -->
    <DataTable
      :columns="columns"
      :items="filteredRoles"
      :loading="loading"
      empty-text="No roles found matching your criteria."
    >
      <!-- S.No Cell -->
      <template #cell-sn="{ item: role }">
        <span class="text-xs font-bold text-slate-400">#{{ role.sn }}</span>
      </template>

      <!-- Role Name Cell -->
      <template #cell-name="{ item: role }">
        <div class="flex items-center gap-4">
          <div
            class="h-10 w-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 overflow-hidden shrink-0 border border-gray-200 font-bold text-xs"
          >
            {{ role.name.substring(0, 2).toUpperCase() }}
          </div>
          <div class="flex flex-col min-w-0">
            <span class="text-sm font-semibold text-gray-700 truncate">{{
              role.name
            }}</span>
          </div>
        </div>
      </template>

      <!-- Permissions Cell -->
      <template #cell-permissions="{ item: role }">
        <div class="flex flex-wrap gap-1">
          <span
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
          >
            {{
              role.name.toLowerCase() === "super admin" ||
              role.name.toLowerCase() === "super-admin"
                ? totalPermissionCount
                : role.permissions
                  ? role.permissions.length
                  : 0
            }}
            Permissions
          </span>
        </div>
      </template>

      <!-- Created Date Cell -->
      <template #cell-created_at="{ item: role }">
        <div class="flex flex-col">
          <span class="text-xs text-slate-600">{{
            formatDate(role.created_at)
          }}</span>
          <span v-if="role.added_by_user" class="text-[10px] text-slate-400">
            by {{ role.added_by_user?.name || "Unknown" }}
          </span>
        </div>
      </template>

      <!-- Modified Date Cell -->
      <template #cell-updated_at="{ item: role }">
        <div class="flex flex-col">
          <span class="text-xs text-slate-600">{{
            formatDate(role.updated_at)
          }}</span>
          <span v-if="role.modified_by_user" class="text-[10px] text-slate-400">
            by {{ role.modified_by_user?.name || "Unknown" }}
          </span>
          <span
            v-else-if="role.added_by_user"
            class="text-[10px] text-slate-400"
          >
            by {{ role.added_by_user?.name || "Unknown" }}
          </span>
        </div>
      </template>

      <!-- Actions Cell -->
      <template #cell-actions="{ item: role }">
        <div class="flex justify-end gap-1.5 transition-opacity duration-200">
          <TableActions
            :item="role"
            :permissions="{ canView, canEdit, canDelete: false }"
            :show-delete="false"
            view-title="View Details"
            edit-title="Edit Role"
            @view="viewRole($event)"
            @edit="handleEdit($event)"
          />
        </div>
      </template>
    </DataTable>

    <!-- Dialogs -->
    <RoleInfoDialog
      :isOpen="isInfoModalOpen"
      :role="selectedRole"
      :modules="modules"
      @close="isInfoModalOpen = false"
    />

    <RoleEditDialog
      :open="isEditModalOpen"
      :role="selectedRole"
      :modules="modules"
      :is-saving="isSaving"
      @openChange="isEditModalOpen = $event"
      @save="handleSave"
    />

    <RoleCreateDialog
      :isOpen="isCreateModalOpen"
      :modules="modules"
      :is-saving="isSaving"
      @close="isCreateModalOpen = false"
      @save="handleSaveCreate"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import {
  Plus as PlusIcon,
  Search as SearchIcon,
  Edit3 as Edit3Icon,
  Eye as EyeIcon,
  Shield as ShieldIcon,
  Calendar as CalendarIcon,
} from "lucide-vue-next";
import axios from "axios";
import RoleInfoDialog from "../../components/roles/RoleInfoDialog.vue";
import RoleEditDialog from "../../components/roles/RoleEditDialog.vue";
import RoleCreateDialog from "../../components/roles/RoleCreateDialog.vue";
import PageHeader from "../../components/ui/PageHeader.vue";
import DataTable from "../../components/ui/DataTable.vue";
import ConfirmationModal from "../../components/ui/ConfirmationModal.vue";
import { usePermissions } from "../../composables/usePermissions";
import { useToast } from "../../composables/useToast";
import TableActions from "../../components/ui/TableActions.vue";

const { addToast } = useToast();
const { getModulePermissions } = usePermissions();
const { canAdd, canView, canEdit, canDelete } = getModulePermissions("Role");

// State
const roles = ref([]);
const modules = ref([]);
const loading = ref(true);
const isSaving = ref(false);
const search = ref("");
const isInfoModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isCreateModalOpen = ref(false);
const selectedRole = ref(null);

const columns = [
  { key: "sn", label: "S.No", width: "80px" },
  { key: "name", label: "Role Name", sortable: true },
  { key: "permissions", label: "Permissions" },
  { key: "created_at", label: "Created" },
  { key: "updated_at", label: "Modified" },
  { key: "actions", label: "Actions", align: "right" },
];

// Computed
const totalPermissionCount = computed(() => {
  return modules.value.reduce(
    (acc, module) => acc + (module.permissions?.length || 0),
    0,
  );
});

const filteredRoles = computed(() => {
  if (!search.value) return roles.value;
  const lowerSearch = search.value.toLowerCase();
  return roles.value.filter((role) =>
    role.name.toLowerCase().includes(lowerSearch),
  );
});

// Methods
const fetchRoles = async () => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/roles");
    roles.value = response.data.roles.map((role, index) => ({
      ...role,
      sn: index + 1,
    }));
    modules.value = response.data.modules;
  } catch (error) {
    console.error("Error fetching roles:", error);
    addToast({
      title: "Error",
      description: "Failed to load roles.",
      variant: "error",
    });
  } finally {
    loading.value = false;
  }
};

const handleEdit = (role) => {
  selectedRole.value = role;
  isEditModalOpen.value = true;
};

const handleSave = async (formData) => {
  isSaving.value = true;
  try {
    await axios.put(`/api/v1/roles/${formData.id}`, formData);
    addToast({
      title: "Success",
      description: "Role updated successfully.",
      variant: "success",
    });
    isEditModalOpen.value = false;
    fetchRoles();
  } catch (error) {
    console.error("Error updating role:", error);
    addToast({
      title: "Error",
      description: "Failed to update role.",
      variant: "error",
    });
  } finally {
    isSaving.value = false;
  }
};

const handleSaveCreate = async (formData) => {
  isSaving.value = true;
  try {
    const response = await axios.post("/api/v1/roles", formData);
    if (response.data.success) {
      addToast({
        title: "Success",
        description: "Role created successfully.",
        variant: "success",
      });
      isCreateModalOpen.value = false;
      fetchRoles();
    }
  } catch (error) {
    console.error("Error creating role:", error);
    addToast({
      title: "Error",
      description: "Failed to create role.",
      variant: "error",
    });
  } finally {
    isSaving.value = false;
  }
};

const viewRole = (role) => {
  selectedRole.value = role;
  isInfoModalOpen.value = true;
};

const formatDate = (dateString, includeTime = false) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  const options = { year: "numeric", month: "short", day: "numeric" };
  if (includeTime) {
    options.hour = "2-digit";
    options.minute = "2-digit";
  }
  return date.toLocaleDateString("en-US", options);
};

onMounted(() => {
  fetchRoles();
});
</script>
