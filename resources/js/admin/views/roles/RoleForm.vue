<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <PageHeader
      :title="isEdit ? 'Edit Role' : 'Create Role'"
      :subtitle="
        isEdit
          ? 'Update role details and permissions.'
          : 'Create a new role and assign permissions.'
      "
    >
      <template #actions>
        <router-link
          :to="{ name: 'roles.index' }"
          class="flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 rounded-xl text-sm font-semibold transition-all shadow-sm active:scale-95"
        >
          <ChevronLeftIcon class="h-4 w-4" />
          Back
        </router-link>
      </template>
    </PageHeader>

    <form @submit.prevent="submitForm" class="space-y-6">
      <!-- Role Name Card -->
      <div
        class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm relative z-20"
      >
        <h3
          class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2"
        >
          <ShieldIcon class="h-5 w-5 text-primary" />
          Role Details
        </h3>
        <div class="max-w-xl">
          <label class="block text-sm font-semibold text-gray-700 mb-2.5 ml-1">
            Role Name <span class="text-rose-500">*</span>
          </label>
          <input
            v-model="form.name"
            type="text"
            placeholder="e.g. Sales Manager"
            class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-gray-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary focus:bg-white transition-all placeholder:text-gray-400"
            required
          />
        </div>
      </div>

      <!-- Permission Matrix -->
      <div
        class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden relative z-10"
      >
        <div
          class="p-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50"
        >
          <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <LockIcon class="h-5 w-5 text-primary" />
            Permissions
          </h3>
          <div class="flex gap-2">
            <button
              type="button"
              @click="selectAll"
              class="text-xs font-semibold text-primary hover:underline"
            >
              Select All
            </button>
            <span class="text-gray-300">|</span>
            <button
              type="button"
              @click="deselectAll"
              class="text-xs font-semibold text-gray-500 hover:text-gray-700"
            >
              Deselect All
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-gray-600">
            <thead
              class="bg-gray-50 text-xs uppercase font-bold text-gray-500 tracking-wider border-b border-gray-200"
            >
              <tr>
                <th class="px-6 py-4 font-extrabold text-gray-700 w-1/4">
                  Module
                </th>
                <th
                  v-for="action in actions"
                  :key="action"
                  class="px-6 py-4 text-center"
                >
                  {{ action }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="module in modules"
                :key="module.id"
                class="hover:bg-gray-50/50 transition-colors"
              >
                <td class="px-6 py-4 font-semibold text-gray-800 bg-gray-50/30">
                  {{ module.name }}
                </td>
                <td
                  v-for="action in actions"
                  :key="action"
                  class="px-6 py-4 text-center"
                >
                  <div class="flex justify-center">
                    <template v-if="getPermissionId(module, action)">
                      <label
                        class="relative inline-flex items-center cursor-pointer group"
                      >
                        <input
                          type="checkbox"
                          class="sr-only peer"
                          :value="getPermissionName(module, action)"
                          v-model="form.permission"
                        />
                        <div
                          class="w-5 h-5 border-2 border-gray-300 rounded-md peer-checked:bg-primary peer-checked:border-primary transition-all flex items-center justify-center"
                        >
                          <CheckIcon
                            class="h-3.5 w-3.5 text-white opacity-0 peer-checked:opacity-100 transition-opacity"
                          />
                        </div>
                      </label>
                    </template>
                    <span v-else class="text-gray-300 text-xs">-</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end gap-3 pt-4">
        <router-link
          :to="{ name: 'roles.index' }"
          class="px-6 py-3 rounded-2xl text-gray-600 font-semibold hover:bg-gray-100 transition-all active:scale-95 bg-white border border-gray-200 shadow-sm"
        >
          Cancel
        </router-link>
        <button
          type="submit"
          :disabled="loading"
          class="px-8 py-3 bg-primary text-white rounded-2xl font-bold hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-primary/25 flex items-center gap-2 active:scale-95"
        >
          <span
            v-if="loading"
            class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
          ></span>
          {{ isEdit ? "Save Changes" : "Create Role" }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import {
  ChevronLeft as ChevronLeftIcon,
  Shield as ShieldIcon,
  Lock as LockIcon,
  Check as CheckIcon,
} from "lucide-vue-next";
import PageHeader from "../../components/ui/PageHeader.vue";
import { useToast } from "../../composables/useToast";

const route = useRoute();
const router = useRouter();
const { success: toastSuccess, error: toastError } = useToast();

const loading = ref(false);
const modules = ref([]);
const actions = ref(["List", "View", "Create", "Edit", "Delete", "Export"]); // Standard actions

const form = ref({
  name: "",
  permission: [],
});

const isEdit = computed(() => !!route.params.id);

const getPermissions = async () => {
  try {
    const response = await axios.get("/api/v1/roles");
    modules.value = response.data.modules;
    // We could perform dynamic action extraction if needed, but static list ensures order
    // actions.value = [...new Set(modules.value.flatMap(m => m.permissions.map(p => p.name.split('-')[1])))];
  } catch (error) {
    console.error("Error fetching permissions:", error);
    toastError("Failed to load permissions.");
  }
};

const getRole = async () => {
  if (!isEdit.value) return;
  try {
    const response = await axios.get(`/api/v1/roles/${route.params.id}`);
    form.value.name = response.data.role.name;
    form.value.permission = response.data.rolePermissions.map((p) => p.name);
  } catch (error) {
    console.error("Error fetching role:", error);
    toastError("Failed to load role details.");
  }
};

const getPermissionId = (module, action) => {
  return module.permissions.find(
    (p) =>
      p.name.toLowerCase() ===
      `${module.name.toLowerCase()}-${action.toLowerCase()}`,
  )?.id;
};

const getPermissionName = (module, action) => {
  return module.permissions.find(
    (p) =>
      p.name.toLowerCase() ===
      `${module.name.toLowerCase()}-${action.toLowerCase()}`,
  )?.name;
};

const selectAll = () => {
  const allPerms = [];
  modules.value.forEach((m) => {
    m.permissions.forEach((p) => allPerms.push(p.name));
  });
  form.value.permission = allPerms;
};

const deselectAll = () => {
  form.value.permission = [];
};

const submitForm = async () => {
  loading.value = true;
  try {
    if (isEdit.value) {
      await axios.put(`/api/v1/roles/${route.params.id}`, form.value);
      toastSuccess("Role updated successfully");
    } else {
      await axios.post("/api/v1/roles", form.value);
      toastSuccess("Role created successfully");
    }
    router.push({ name: "roles.index" });
  } catch (error) {
    console.error("Error saving role:", error);
    toastError("Failed to save role.");
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await getPermissions();
  if (isEdit.value) {
    await getRole();
  }
});
</script>
