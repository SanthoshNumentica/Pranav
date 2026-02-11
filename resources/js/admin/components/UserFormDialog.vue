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
              class="relative transform overflow-hidden rounded-[32px] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 flex flex-col"
            >
              <!-- Header -->
              <div class="px-6 py-6 border-b border-slate-100 shrink-0">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div
                      class="h-10 w-10 rounded-xl bg-primary/10 flex items-center justify-center"
                    >
                      <UserIcon class="h-5 w-5 text-primary" />
                    </div>
                    <div>
                      <h3 class="text-lg font-bold text-slate-900">
                        {{ user ? "Edit User" : "Add New User" }}
                      </h3>
                      <p class="text-xs text-slate-500">
                        Manage system access and roles.
                      </p>
                    </div>
                  </div>
                  <button
                    @click.stop="close"
                    class="p-2 rounded-xl hover:bg-slate-50 transition-colors"
                  >
                    <XIcon class="h-5 w-5 text-slate-400" />
                  </button>
                </div>
              </div>

              <!-- Form Body -->
              <div class="p-6 sm:p-8 space-y-6">
                <form
                  id="userForm"
                  @submit.prevent="handleSubmit"
                  class="space-y-6"
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

                  <div class="space-y-2">
                    <label
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                      >Role <span class="text-rose-500">*</span></label
                    >
                    <Select v-model="form.role" required>
                      <SelectTrigger>
                        <SelectValue placeholder="Select Role" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem 
                          v-for="role in roles" 
                          :key="role.id" 
                          :value="role.name.toLowerCase()"
                        >
                          {{ role.name }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div class="space-y-2">
                    <label
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                    >
                      {{ user ? "New Password" : "Password" }}
                      <span v-if="!user" class="text-rose-500">*</span>
                      <span v-else class="text-[9px] lowercase font-normal">(Leave blank to keep current)</span>
                    </label>
                    <input
                      v-model="form.password"
                      type="password"
                      :required="!user"
                      placeholder="••••••••"
                      class="w-full rounded-2xl py-3 px-4 text-sm border border-slate-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium"
                    />
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
import { ref, reactive, watch, onMounted } from "vue";
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
} from "./ui/select";
import axios from "axios";
import { useToast } from "../composables/useToast";

const { addToast } = useToast();

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

const initialForm = {
  name: "",
  email: "",
  role: "",
  password: "",
};

const form = reactive({ ...initialForm });

const fetchRoles = async () => {
  try {
    const response = await axios.get("/api/v1/masters/roles");
    if (response.data.success) {
      roles.value = response.data.data;
      if (!props.user && roles.value.length > 0) {
        form.role = roles.value[0].name.toLowerCase();
      }
    }
  } catch (err) {
    console.error("Failed to fetch roles", err);
  }
};

onMounted(fetchRoles);

watch(
  () => props.user,
  (newVal) => {
    if (newVal) {
      form.name = newVal.name || "";
      form.email = newVal.email || "";
      form.role = newVal.role || "staff";
      form.password = "";
    } else {
      Object.assign(form, initialForm);
    }
  },
  { immediate: true },
);

const close = () => {
  if (!props.isOpen) return;
  emit("close");
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    if (props.user) {
      await axios.put(`/api/v1/users/${props.user.id}`, form);
    } else {
      await axios.post("/api/v1/users", form);
    }
    addToast({
      title: "Success",
      description: `User ${props.user ? "updated" : "created"} successfully.`,
      variant: "success",
    });
    emit("saved");
    close();
  } catch (err) {
    console.error("Failed to save user", err);
    addToast({
      title: "Error",
      description:
        err.response?.data?.message ||
        "Failed to save user. Please check your data.",
      variant: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>
