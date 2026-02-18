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
              class="relative transform overflow-hidden rounded-[32px] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200 flex flex-col h-[90vh] sm:h-[80vh]"
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
                      <UserIcon class="h-6 w-6 text-white" />
                    </div>
                    <div>
                      <h3 class="text-xl font-bold tracking-tight">
                        {{ user ? "Edit User" : "Add New User" }}
                      </h3>
                      <div class="flex items-center gap-2 mt-1 opacity-90">
                        <span class="text-sm font-medium">
                          {{
                            user
                              ? "Modify existing account"
                              : "Create system access"
                          }}
                        </span>
                        <template v-if="user">
                          <span class="h-1 w-1 rounded-full bg-white/50"></span>
                          <span
                            class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full border border-white/20"
                          >
                            {{ user.status }}
                          </span>
                        </template>
                      </div>
                    </div>
                  </div>
                  <button
                    @click.stop="close"
                    class="p-2 rounded-xl hover:bg-white/10 transition-colors"
                  >
                    <XIcon class="h-5 w-5" />
                  </button>
                </div>
              </div>

              <!-- Form Body -->
              <div class="flex-1 overflow-y-auto p-6 sm:p-8 custom-scrollbar">
                <form
                  id="userForm"
                  @submit.prevent="handleSubmit"
                  class="space-y-8"
                >
                  <div class="space-y-2">
                    <label
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >Full Name <span class="text-rose-500">*</span></label
                    >
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      placeholder="John Doe"
                      class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                    />
                  </div>

                  <div class="space-y-2">
                    <label
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >Email Address <span class="text-rose-500">*</span></label
                    >
                    <input
                      v-model="form.email"
                      type="email"
                      required
                      placeholder="user@example.com"
                      class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                    />
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                        >Role <span class="text-rose-500">*</span></label
                      >
                      <Select
                        v-model="form.role_id"
                        v-model:open="isRoleDropdownOpen"
                        required
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Select Role" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem
                            v-for="role in roles"
                            :key="role.id"
                            :value="role.id.toString()"
                          >
                            {{ role.name }}
                          </SelectItem>
                        </SelectContent>
                      </Select>
                    </div>

                    <div class="space-y-2" v-if="!isSuperAdmin">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                        >Branch <span class="text-rose-500">*</span></label
                      >
                      <Select
                        v-model="form.branch_id"
                        v-model:open="isBranchDropdownOpen"
                        :disabled="!loggedInUserIsSuperAdmin"
                        required
                      >
                        <SelectTrigger>
                          <SelectValue
                            :placeholder="
                              !loggedInUserIsSuperAdmin
                                ? authUser?.branch?.name
                                : 'Select Branch'
                            "
                          />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem
                            v-for="branch in filteredBranches"
                            :key="branch.id"
                            :value="branch.id.toString()"
                          >
                            {{ branch.name }}
                          </SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                        >Status <span class="text-rose-500">*</span></label
                      >
                      <Select
                        v-model="form.status"
                        v-model:open="isStatusDropdownOpen"
                        required
                      >
                        <SelectTrigger>
                          <SelectValue placeholder="Select Status" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="active">Active</SelectItem>
                          <SelectItem value="inactive">Inactive</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >
                        {{ user ? "New Password" : "Password" }}
                        <span v-if="!user" class="text-rose-500">*</span>
                        <span v-else class="text-[9px] lowercase font-normal"
                          >(Leave blank to keep current)</span
                        >
                      </label>
                      <input
                        v-model="form.password"
                        type="password"
                        :required="!user"
                        placeholder="••••••••"
                        class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                      />
                    </div>

                    <div class="space-y-2">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >
                        Confirm Password
                        <span v-if="!user" class="text-rose-500">*</span>
                      </label>
                      <input
                        v-model="form.password_confirmation"
                        type="password"
                        :required="!user"
                        placeholder="••••••••"
                        class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                      />
                    </div>
                  </div>
                </form>
              </div>

              <!-- Footer Actions -->
              <div
                class="p-6 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0"
              >
                <button
                  @click.stop="close"
                  type="button"
                  class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-50 transition-all active:scale-95"
                >
                  Cancel
                </button>
                <button
                  form="userForm"
                  type="submit"
                  :disabled="loading"
                  class="px-8 py-2.5 rounded-xl bg-primary text-white text-sm font-bold hover:opacity-90 transition-all active:scale-95 shadow-lg shadow-primary/20 flex items-center gap-2"
                >
                  <Loader2Icon v-if="loading" class="h-4 w-4 animate-spin" />
                  {{ user ? "Update User" : "Create User" }}
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
import { ref, reactive, watch, onMounted, computed } from "vue";
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import {
  User as UserIcon,
  X as XIcon,
  Loader2 as Loader2Icon,
} from "lucide-vue-next";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../ui/select";
import axios from "axios";
import { useToast } from "../../composables/useToast";
import { useAuth } from "../../composables/useAuth";

const { addToast } = useToast();
const { user: authUser } = useAuth();

const props = defineProps({
  isOpen: Boolean,
  user: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["close", "saved"]);

const loading = ref(false);
const roles = ref([]);
const branches = ref([]);
const isRoleDropdownOpen = ref(false);
const isBranchDropdownOpen = ref(false);
const isStatusDropdownOpen = ref(false);

const initialForm = {
  name: "",
  email: "",
  role_id: "",
  branch_id: "null",
  status: "active",
  password: "",
  password_confirmation: "",
};

const form = reactive({ ...initialForm });

const isSuperAdmin = computed(() => {
  if (!form.role_id) return false;
  const role = roles.value.find((r) => r.id.toString() === form.role_id);
  return role?.name.toLowerCase() === "super-admin";
});

const loggedInUserIsSuperAdmin = computed(
  () => authUser.value?.role?.name.toLowerCase() === "super-admin",
);

const filteredBranches = computed(() => {
  if (loggedInUserIsSuperAdmin.value) return branches.value;
  if (!authUser.value?.branch_id) return [];
  // Only show the branch the current user belongs to
  return branches.value.filter(
    (b) => b.id.toString() === authUser.value.branch_id.toString(),
  );
});

const fetchRoles = async () => {
  try {
    const response = await axios.get("/api/v1/masters/roles");
    if (response.data.success) {
      roles.value = response.data.data;
    }
  } catch (err) {
    console.error("Failed to fetch roles", err);
  }
};

const fetchBranches = async () => {
  try {
    const response = await axios.get("/api/v1/masters/branches");
    if (response.data.success) {
      branches.value = Array.isArray(response.data.data)
        ? response.data.data
        : response.data.data?.data || [];
    }
  } catch (err) {
    console.error("Failed to fetch branches", err);
  }
};

onMounted(() => {
  fetchRoles();
  fetchBranches();
});

watch(
  () => props.user,
  (newVal) => {
    if (newVal) {
      form.name = newVal.name || "";
      form.email = newVal.email || "";
      form.role_id = newVal.role_id ? newVal.role_id.toString() : "";
      form.branch_id = newVal.branch_id ? newVal.branch_id.toString() : "null";
      form.status = newVal.status || "active";
      form.password = "";
      form.password_confirmation = "";
    } else {
      Object.assign(form, initialForm);
      // Auto-assign branch for non-super-admins adding a new user
      if (!loggedInUserIsSuperAdmin.value && authUser.value?.branch_id) {
        form.branch_id = authUser.value.branch_id.toString();
      }
    }
  },
  { immediate: true },
);

watch(isSuperAdmin, (newVal) => {
  if (newVal) {
    form.branch_id = "null";
  }
});

const close = () => {
  if (isRoleDropdownOpen.value || isStatusDropdownOpen.value) return;
  if (!props.isOpen) return;
  emit("close");
};

const handleSubmit = async () => {
  if (form.password && form.password !== form.password_confirmation) {
    addToast({
      title: "Error",
      description: "Passwords do not match.",
      variant: "error",
    });
    return;
  }

  loading.value = true;
  try {
    const payload = { ...form };
    // backend expects numeric IDs or null
    payload.role_id = parseInt(payload.role_id);
    payload.branch_id =
      payload.branch_id === "null" ? null : parseInt(payload.branch_id);

    // Only send password if it's filled
    if (props.user && !payload.password) {
      delete payload.password;
      delete payload.password_confirmation;
    }

    if (props.user) {
      await axios.put(`/api/v1/users/${props.user.id}`, payload);
    } else {
      await axios.post("/api/v1/users", payload);
    }
    addToast({
      title: "Success",
      description: `User ${props.user ? "updated" : "created"} successfully.`,
      variant: "success",
    });
    emit("saved");
    emit("close");
  } catch (err) {
    console.error("Failed to save user", err);
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors;
      Object.keys(errors).forEach((key) => {
        errors[key].forEach((msg) => {
          addToast({
            title: "Validation Error",
            description: msg,
            variant: "error",
          });
        });
      });
    } else {
      addToast({
        title: "Error",
        description:
          err.response?.data?.message ||
          "Failed to save user. Please check your data.",
        variant: "error",
      });
    }
  } finally {
    loading.value = false;
  }
};
</script>
