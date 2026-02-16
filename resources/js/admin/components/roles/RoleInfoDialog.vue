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
        <div
          class="flex min-h-full items-center justify-center p-4 text-center"
        >
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
              class="w-full max-w-2xl transform rounded-[2.5rem] bg-white text-left shadow-2xl transition-all border border-slate-100 flex flex-col h-[70vh] overflow-hidden"
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
                      <ShieldIcon class="h-7 w-7 text-white" />
                    </div>
                    <div>
                      <DialogTitle
                        as="h3"
                        class="text-2xl font-bold tracking-tight text-white mb-1"
                      >
                        Role: {{ role?.name }}
                      </DialogTitle>
                      <div class="flex items-center gap-3 mt-1.5">
                        <span
                          class="text-xs text-slate-200 font-medium flex items-center gap-1.5 bg-white/10 px-2 py-0.5 rounded-lg border border-white/10 backdrop-blur-sm"
                        >
                          <LockIcon class="h-3.5 w-3.5" />
                          {{ displayedPermissions.length }} Permissions
                        </span>
                        <span
                          class="text-xs text-slate-200 font-medium flex items-center gap-1.5 bg-white/10 px-2 py-0.5 rounded-lg border border-white/10 backdrop-blur-sm"
                        >
                          <CalendarIcon class="h-3.5 w-3.5" />
                          {{ formatDate(role?.created_at) }}
                        </span>
                      </div>
                    </div>
                  </div>
                  <button
                    @click="$emit('close')"
                    class="p-3 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 hover:text-white transition-all border border-white/5"
                  >
                    <XIcon class="h-6 w-6" />
                  </button>
                </div>
              </div>

              <!-- Content -->
              <div
                class="flex-1 overflow-y-auto bg-slate-50/50 custom-scrollbar p-8"
              >
                <div v-if="role" class="space-y-6">
                  <!-- Visual Profile Section -->
                  <div
                    class="flex flex-col items-center pb-6 border-b border-slate-200/60"
                  >
                    <div
                      class="w-20 h-20 rounded-[1.5rem] bg-slate-100 flex items-center justify-center border-2 border-white shadow-lg overflow-hidden mb-3 ring-4 ring-slate-50 text-xl font-bold text-slate-400"
                    >
                      {{ role.name.substring(0, 2).toUpperCase() }}
                    </div>
                    <h4 class="text-lg font-bold text-slate-700">
                      {{ role.name }}
                    </h4>
                    <p
                      class="text-xs text-slate-500 font-medium uppercase tracking-wider mt-1"
                    >
                      System Role ID: {{ role.id }}
                    </p>
                  </div>

                  <!-- Details Grid -->
                  <div class="grid grid-cols-1 gap-6">
                    <div>
                      <h5
                        class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-4"
                      >
                        <LockIcon class="h-4 w-4 text-primary" />
                        Assigned Permissions
                      </h5>
                      <div class="flex flex-wrap gap-2">
                        <span
                          v-for="perm in displayedPermissions"
                          :key="perm.id"
                          class="px-3 py-1.5 bg-white text-slate-600 text-[11px] font-semibold rounded-xl border border-slate-200 shadow-sm hover:border-primary/30 transition-colors"
                        >
                          {{ perm.name }}
                        </span>
                        <div
                          v-if="displayedPermissions.length === 0"
                          class="w-full py-8 text-center bg-slate-100/50 rounded-2xl border border-dashed border-slate-300"
                        >
                          <p class="text-sm text-slate-400 italic">
                            No permissions assigned to this role.
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Footer -->
              <div
                class="p-6 border-t border-slate-100 bg-slate-50/50 flex justify-end"
              >
                <button
                  @click="$emit('close')"
                  class="px-6 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all active:scale-95 shadow-sm"
                >
                  Close
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
import { computed } from "vue";
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from "@headlessui/vue";
import {
  X as XIcon,
  Shield as ShieldIcon,
  Calendar as CalendarIcon,
  Lock as LockIcon,
} from "lucide-vue-next";

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true,
  },
  role: {
    type: Object,
    default: null,
  },
  modules: {
    type: Array,
    default: () => [],
  },
});

defineEmits(["close"]);

const displayedPermissions = computed(() => {
  if (!props.role) return [];

  const isSuperAdmin =
    props.role.name.toLowerCase() === "super admin" ||
    props.role.name.toLowerCase() === "super-admin";

  if (isSuperAdmin && props.modules) {
    // Flatten all permissions from modules
    return props.modules.flatMap((m) => m.permissions || []);
  }

  return props.role.permissions || [];
});

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  const options = { year: "numeric", month: "short", day: "numeric" };
  return new Date(dateString).toLocaleDateString("en-US", options);
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
