<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="() => {}" class="relative z-[60]">
      <!-- Backdrop -->
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95 translateY(20px)"
            enter-to="opacity-100 scale-100 translateY(0)"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100 translateY(0)"
            leave-to="opacity-0 scale-95 translateY(20px)"
          >
            <DialogPanel
              class="w-full max-w-5xl transform rounded-[2.5rem] bg-white shadow-2xl transition-all border border-slate-100 flex flex-col h-[85vh] overflow-hidden"
            >
              <!-- Header -->
              <div
                class="relative bg-primary text-white overflow-hidden shrink-0"
              >
                <!-- Decorative Background -->
                <div
                  class="absolute inset-0 bg-gradient-to-r from-primary/20 to-transparent"
                ></div>
                <div
                  class="absolute -right-20 -top-20 w-96 h-96 bg-primary/10 rounded-full blur-3xl"
                ></div>

                <div
                  class="relative px-8 py-6 flex items-center justify-between"
                >
                  <div class="flex items-center gap-5">
                    <div
                      class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 flex items-center justify-center shadow-inner"
                    >
                      <PlusIcon class="h-7 w-7 text-white" />
                    </div>
                    <div>
                      <DialogTitle
                        as="h3"
                        class="text-2xl font-bold tracking-tight text-white mb-1"
                      >
                        Create New Role
                      </DialogTitle>
                      <p class="text-sm text-slate-200 opacity-80">
                        Define a new system role and assign specific
                        permissions.
                      </p>
                    </div>
                  </div>
                  <button
                    @click="handleClose"
                    class="p-3 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 hover:text-white transition-all border border-white/5 disabled:opacity-50"
                  >
                    <XIcon class="h-6 w-6" />
                  </button>
                </div>
              </div>

              <!-- Content -->
              <div
                class="flex-1 overflow-y-auto bg-slate-50/50 custom-scrollbar p-0 relative"
              >
                <div class="bg-white min-h-full p-8 rounded-b-[2rem]">
                  <div class="max-w-4xl mx-auto space-y-8">
                    <!-- Role Name -->
                    <div
                      class="bg-slate-50 rounded-2xl border border-slate-200 p-6"
                    >
                      <label
                        class="block text-sm font-bold text-slate-700 mb-2"
                      >
                        Role Name <span class="text-rose-500">*</span>
                      </label>
                      <input
                        v-model="form.name"
                        type="text"
                        placeholder="e.g. Sales Manager"
                        class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-xl text-slate-700 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all"
                        required
                      />
                    </div>

                    <!-- Permission Matrix -->
                    <div
                      class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden"
                    >
                      <div
                        class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50"
                      >
                        <h3
                          class="text-sm font-bold text-slate-800 flex items-center gap-2"
                        >
                          <LockIcon class="h-4 w-4 text-primary" />
                          Permission Matrix
                        </h3>
                        <div class="flex gap-2">
                          <button
                            type="button"
                            @click="selectAll"
                            class="text-xs font-bold text-primary hover:underline"
                          >
                            Select All
                          </button>
                          <span class="text-slate-300">|</span>
                          <button
                            type="button"
                            @click="deselectAll"
                            class="text-xs font-bold text-slate-500 hover:text-slate-700"
                          >
                            Deselect All
                          </button>
                        </div>
                      </div>

                      <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                          <thead
                            class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500 tracking-wider border-b border-slate-200"
                          >
                            <tr>
                              <th class="px-6 py-4 w-1/4">Module</th>
                              <th
                                v-for="action in actions"
                                :key="action"
                                class="px-6 py-4 text-center"
                              >
                                {{ action }}
                              </th>
                            </tr>
                          </thead>
                          <tbody class="divide-y divide-slate-100">
                            <tr
                              v-for="module in modules"
                              :key="module.id"
                              class="hover:bg-slate-50/50 transition-colors"
                            >
                              <td
                                class="px-6 py-4 font-bold text-slate-700 bg-slate-50/30"
                              >
                                {{ module.name }}
                              </td>
                              <td
                                v-for="action in actions"
                                :key="action"
                                class="px-6 py-4 text-center"
                              >
                                <div class="flex justify-center">
                                  <template
                                    v-if="getPermissionId(module, action)"
                                  >
                                    <label
                                      class="relative inline-flex items-center cursor-pointer group"
                                    >
                                      <input
                                        type="checkbox"
                                        class="sr-only peer"
                                        :value="
                                          getPermissionName(module, action)
                                        "
                                        v-model="form.permission"
                                      />
                                      <div
                                        class="w-5 h-5 border-2 border-slate-300 rounded-md peer-checked:bg-primary peer-checked:border-primary transition-all flex items-center justify-center"
                                      >
                                        <CheckIcon
                                          class="h-3.5 w-3.5 text-white opacity-0 peer-checked:opacity-100 transition-opacity"
                                        />
                                      </div>
                                    </label>
                                  </template>
                                  <span v-else class="text-slate-300 text-xs"
                                    >-</span
                                  >
                                </div>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Footer -->
              <div
                class="p-6 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3"
              >
                <button
                  @click="handleClose"
                  class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all active:scale-95"
                >
                  Cancel
                </button>
                <button
                  @click="handleSubmit"
                  :disabled="isSaving || !form.name"
                  class="px-8 py-2.5 bg-primary text-white rounded-xl text-sm font-bold hover:opacity-90 disabled:opacity-50 transition-all shadow-lg shadow-primary/20 flex items-center gap-2 active:scale-95"
                >
                  <span
                    v-if="isSaving"
                    class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
                  ></span>
                  Create Role
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
import { ref, reactive } from "vue";
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from "@headlessui/vue";
import {
  X as XIcon,
  Plus as PlusIcon,
  Lock as LockIcon,
  Check as CheckIcon,
} from "lucide-vue-next";

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true,
  },
  isSaving: {
    type: Boolean,
    default: false,
  },
  modules: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["close", "save"]);

const actions = ref(["List", "View", "Create", "Edit", "Delete", "Export"]);

const form = reactive({
  name: "",
  permission: [],
});

const handleClose = () => {
  // Reset form
  form.name = "";
  form.permission = [];
  emit("close");
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
  props.modules.forEach((m) => {
    m.permissions.forEach((p) => allPerms.push(p.name));
  });
  form.permission = allPerms;
};

const deselectAll = () => {
  form.permission = [];
};

const handleSubmit = () => {
  emit("save", { ...form });
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 20px;
}
</style>
