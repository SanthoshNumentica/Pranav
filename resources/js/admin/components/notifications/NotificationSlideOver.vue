<template>
  <TransitionRoot as="template" :show="isOpen">
    <Dialog as="div" class="relative z-50" @close="close">
      <TransitionChild
        as="template"
        enter="ease-in-out duration-500"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in-out duration-500"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div
          class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
        />
      </TransitionChild>

      <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <div
            class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10"
          >
            <TransitionChild
              as="template"
              enter="transform transition ease-in-out duration-500 sm:duration-700"
              enter-from="translate-x-full"
              enter-to="translate-x-0"
              leave="transform transition ease-in-out duration-500 sm:duration-700"
              leave-from="translate-x-0"
              leave-to="translate-x-full"
            >
              <DialogPanel class="pointer-events-auto w-screen max-w-md">
                <div
                  class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl"
                >
                  <div
                    class="px-4 py-6 sm:px-6 bg-slate-50 border-b border-slate-100"
                  >
                    <div class="flex items-start justify-between">
                      <DialogTitle
                        class="text-base font-semibold leading-6 text-slate-900"
                      >
                        WhatsApp Logs
                      </DialogTitle>
                      <div class="ml-3 flex h-7 items-center">
                        <button
                          type="button"
                          class="relative rounded-md bg-transparent text-slate-400 hover:text-slate-500 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                          @click.stop="close"
                        >
                          <span class="absolute -inset-2.5" />
                          <span class="sr-only">Close panel</span>
                          <XIcon class="h-6 w-6" aria-hidden="true" />
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Filter & Search (Optional for V1) -->
                  <div class="p-4 border-b border-slate-100">
                    <div class="relative group">
                      <SearchIcon
                        class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-primary transition-colors"
                      />
                      <input
                        v-model="search"
                        @input="fetchLogs(1)"
                        type="text"
                        placeholder="Search logs..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                      />
                    </div>
                  </div>

                  <div class="relative mt-6 flex-1 px-4 sm:px-6">
                    <!-- Logs List -->
                    <div v-if="loading" class="space-y-4">
                      <!-- Skeleton -->
                      <div
                        v-for="i in 5"
                        :key="i"
                        class="animate-pulse flex space-x-4"
                      >
                        <div class="rounded-full bg-slate-200 h-10 w-10"></div>
                        <div class="flex-1 space-y-2 py-1">
                          <div class="h-2 bg-slate-200 rounded"></div>
                          <div class="space-y-1">
                            <div class="grid grid-cols-3 gap-4">
                              <div
                                class="h-2 bg-slate-200 rounded col-span-2"
                              ></div>
                              <div
                                class="h-2 bg-slate-200 rounded col-span-1"
                              ></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div
                      v-else-if="logs.length === 0"
                      class="text-center py-10"
                    >
                      <p class="text-slate-500">No logs found.</p>
                    </div>

                    <ul v-else class="space-y-4 pb-20">
                      <li
                        v-for="log in logs"
                        :key="log.id"
                        class="bg-white border border-slate-100 rounded-xl p-3 shadow-sm hover:shadow-md transition-shadow"
                      >
                        <div class="flex justify-between items-start mb-2">
                          <div class="flex items-center gap-2">
                            <span
                              :class="[
                                'h-2 w-2 rounded-full',
                                log.status === 'sent'
                                  ? 'bg-emerald-500'
                                  : 'bg-rose-500',
                              ]"
                            ></span>
                            <span class="font-bold text-sm text-slate-800">{{
                              log.recipient_mobile_no
                            }}</span>
                          </div>
                          <span class="text-xs text-slate-400">{{
                            formatDate(log.updated_at)
                          }}</span>
                        </div>
                        <p class="text-xs text-slate-600 mb-2 line-clamp-2">
                          {{ log.message }}
                        </p>

                        <div
                          class="flex justify-between items-center mt-2 pt-2 border-t border-slate-50"
                        >
                          <span
                            class="text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded uppercase font-bold"
                            >{{ log.status }}</span
                          >
                          <button
                            @click="openResendConfirm(log)"
                            :disabled="resendingId === log.id"
                            class="text-primary text-xs font-semibold hover:underline disabled:opacity-50 flex items-center gap-1"
                          >
                            <span
                              v-if="resendingId === log.id"
                              class="animate-spin h-3 w-3 border-2 border-primary border-t-transparent rounded-full"
                            ></span>
                            {{
                              resendingId === log.id ? "Resending..." : "Resend"
                            }}
                          </button>
                        </div>
                      </li>
                    </ul>

                    <!-- Pagination -->
                    <!-- Simple Load More or Pagination at bottom -->
                    <div
                      v-if="pagination && pagination.last_page > 1"
                      class="mt-4 flex justify-between items-center text-xs pb-6"
                    >
                      <button
                        @click="fetchLogs(pagination.current_page - 1)"
                        :disabled="!pagination.prev_page_url"
                        class="px-3 py-1 bg-slate-100 rounded disabled:opacity-50"
                      >
                        Prev
                      </button>
                      <span class="text-slate-500"
                        >Page {{ pagination.current_page }} of
                        {{ pagination.last_page }}</span
                      >
                      <button
                        @click="fetchLogs(pagination.current_page + 1)"
                        :disabled="!pagination.next_page_url"
                        class="px-3 py-1 bg-slate-100 rounded disabled:opacity-50"
                      >
                        Next
                      </button>
                    </div>
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>

  <!-- Resend Confirmation -->
  <ConfirmationModal
    :is-open="isConfirmOpen"
    title="Resend Message"
    description="Are you sure you want to resend this WhatsApp message?"
    confirm-label="Resend"
    :loading="resendingId !== null"
    @close="isConfirmOpen = false"
    @confirm="confirmResend"
  />
</template>

<script setup>
import { ref, watch } from "vue";
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { X as XIcon, Search as SearchIcon } from "lucide-vue-next";
import axios from "axios";
import { formatDate } from "../../utils/format";
import ConfirmationModal from "../ui/ConfirmationModal.vue";
import { useToast } from "../../composables/useToast";

const { addToast } = useToast();

const props = defineProps({
  isOpen: Boolean,
});

const emit = defineEmits(["close"]);

const logs = ref([]);
const loading = ref(false);
const search = ref("");
const pagination = ref(null);
const resendingId = ref(null);
const isConfirmOpen = ref(false);
const logToResend = ref(null);

const fetchLogs = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get("/api/v1/whatsapp-logs", {
      params: {
        page,
        search: search.value,
      },
    });
    if (response.data.success) {
      logs.value = response.data.data.data;
      pagination.value = response.data.data;
    }
  } catch (error) {
    console.error("Failed to fetch whatsapp logs", error);
  } finally {
    loading.value = false;
  }
};

// Fetch logs when opened
watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal) {
      fetchLogs();
    }
  },
);

const openResendConfirm = (log) => {
  logToResend.value = log;
  isConfirmOpen.value = true;
};

const confirmResend = async () => {
  if (!logToResend.value) return;

  const log = logToResend.value;
  resendingId.value = log.id;

  try {
    const response = await axios.post(`/api/v1/whatsapp-logs/resend/${log.id}`);
    if (response.data.success) {
      await fetchLogs(pagination.value?.current_page || 1);
      isConfirmOpen.value = false;
      addToast({
        title: "Success",
        description: response.data.message,
        variant: "success",
      });
    } else {
      addToast({
        title: "Error",
        description: response.data.message || "Failed to resend message.",
        variant: "error",
      });
    }
  } catch (error) {
    console.error("Failed to resend log", error);
    addToast({
      title: "Error",
      description: "An error occurred while resending the message.",
      variant: "error",
    });
  } finally {
    isConfirmOpen.value = false;
    resendingId.value = null;
    logToResend.value = null;
    close();
  }
};

const close = () => {
  if (!props.isOpen) return;
  emit("close");
};
</script>
