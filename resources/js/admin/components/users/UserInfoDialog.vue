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
                        User Details
                      </h3>
                      <div class="flex items-center gap-2 mt-1 opacity-90">
                        <span class="text-sm font-medium">{{
                          user?.name
                        }}</span>
                        <span class="h-1 w-1 rounded-full bg-white/50"></span>
                        <span
                          class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full border border-white/20"
                        >
                          {{ user?.status }}
                        </span>
                      </div>
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <button
                      v-if="canEdit"
                      @click="$emit('edit', user)"
                      class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/20 hover:bg-white/30 text-white text-xs font-bold transition-all active:scale-95 border border-white/20 backdrop-blur-sm shadow-lg shadow-black/5"
                    >
                      <EditIcon class="h-4 w-4" />
                      Edit User
                    </button>
                    <button
                      @click.stop="close"
                      class="p-2 rounded-xl hover:bg-white/10 transition-colors"
                    >
                      <XIcon class="h-5 w-5" />
                    </button>
                  </div>
                </div>
              </div>

              <!-- Content Body -->
              <div
                class="flex-1 overflow-y-auto p-6 sm:p-8 custom-scrollbar space-y-8"
              >
                <!-- Fields Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                  <div
                    v-for="(field, label) in detailedInfo"
                    :key="label"
                    class="space-y-1"
                  >
                    <span
                      class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 block ml-1"
                      >{{ label }}</span
                    >
                    <p
                      class="text-sm font-semibold text-slate-700 bg-slate-50/50 p-3 rounded-xl border border-slate-100/50"
                    >
                      {{ field || "N/A" }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Footer Actions -->
              <div
                class="p-6 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0"
              >
                <button
                  @click.stop="close"
                  type="button"
                  class="px-8 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-bold hover:bg-slate-800 transition-all active:scale-95 shadow-lg shadow-slate-900/10"
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
import { ref, computed, onMounted, watch } from "vue";
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import {
  User as UserIcon,
  X as XIcon,
  Edit as EditIcon,
} from "lucide-vue-next";
import { formatDate } from "../../utils/format";
import axios from "axios";

const props = defineProps({
  isOpen: Boolean,
  user: {
    type: Object,
    default: null,
  },
  canEdit: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["close", "edit"]);

const branches = ref([]);

const fetchBranches = async () => {
  try {
    const response = await axios.get("/api/v1/masters/branches");
    if (response.data.success) {
      branches.value = response.data.data;
    }
  } catch (err) {
    console.error("Failed to fetch branches", err);
  }
};

onMounted(fetchBranches);

watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal && branches.value.length === 0) {
      fetchBranches();
    }
  },
);

const close = () => {
  if (!props.isOpen) return;
  emit("close");
};

function cn(...classes) {
  return classes.filter(Boolean).join(" ");
}

const detailedInfo = computed(() => {
  if (!props.user) return {};

  const isSuperAdmin = props.user.role?.name.toLowerCase() === "super-admin";
  let branchInfo = props.user.branch?.name || "Unassigned";

  if (isSuperAdmin) {
    const branchNames = branches.value.map((b) => b.name).join(", ");
    branchInfo = branchNames ? `All (${branchNames})` : "All Branches";
  }

  return {
    "User Name": props.user.name,
    "Email Address": props.user.email,
    Role: props.user.role?.name || "N/A",
    Branch: branchInfo,
    "Created At": formatDate(props.user.created_at),
    "Last Updated": formatDate(props.user.updated_at),
  };
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 20px;
}
</style>
