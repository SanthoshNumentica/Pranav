<template>
  <div
    class="max-w-5xl mx-auto space-y-8 pb-20 animate-in fade-in slide-in-from-bottom-4 duration-500"
  >
    <!-- Invoice Header -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-500"
    >
      <div class="flex items-center gap-4">
        <button
          @click="$router.push('/invoices')"
          class="p-2.5 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-primary hover:border-primary/20 transition-all shadow-sm active:scale-95"
        >
          <ArrowLeftIcon class="h-5 w-5" />
        </button>
        <div>
          <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
            Invoice #{{ invoice?.invoice_no }}
          </h1>
          <p class="text-sm text-slate-500 mt-1">
            Recorded on {{ formatDate(invoice?.invoice_date) }}
          </p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button
          @click="printInvoice"
          class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold transition-all hover:bg-slate-50 active:scale-95 shadow-sm"
        >
          <PrinterIcon class="h-4 w-4" />
          Print
        </button>
        <button
          v-if="invoice?.status === 'pending'"
          @click="isPaymentDialogOpen = true"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-700 text-white rounded-xl text-sm font-semibold transition-all shadow-md shadow-primary/10 active:scale-95"
        >
          <CreditCardIcon class="h-4 w-4" />
          Add Payment
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Main Content -->
      <div class="lg:col-span-2 space-y-8">
        <!-- Invoice Details Card -->
        <div
          class="bg-white rounded-3xl border border-slate-200 p-8 shadow-soft-xl space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 delay-100"
        >
          <div
            class="flex flex-col sm:flex-row justify-between gap-8 sm:gap-10"
          >
            <div class="space-y-4 flex-1">
              <span
                class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                >Bill To</span
              >
              <div class="space-y-1.5 px-1">
                <h4 class="text-lg font-bold text-slate-900">
                  {{ invoice?.patient?.name }}
                </h4>
                <div class="space-y-0.5">
                  <p class="text-xs text-slate-500 font-medium">
                    Patient ID: {{ invoice?.patient?.patient_id }}
                  </p>
                  <p class="text-slate-500 font-medium">
                    {{ invoice?.patient?.place }}
                  </p>
                  <p
                    class="text-slate-500 font-medium"
                    v-if="invoice?.patient?.mobile_no"
                  >
                    Ph: {{ invoice?.patient?.mobile_no }}
                  </p>
                </div>
              </div>
            </div>
            <div class="text-left sm:text-right space-y-4 flex-1">
              <span
                class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 mr-1"
                >Branch</span
              >
              <div class="space-y-1.5 px-1">
                <h4 class="text-lg font-bold text-slate-900">
                  {{ invoice?.branch?.name }}
                </h4>
                <div class="space-y-0.5">
                  <p
                    class="text-xs text-slate-500 font-medium"
                    v-if="invoice?.branch?.address"
                  >
                    {{ invoice?.branch?.address }}
                  </p>
                  <p
                    class="text-xs text-slate-500 font-medium"
                    v-if="invoice?.branch?.phone"
                  >
                    Ph: {{ invoice?.branch?.phone }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Items Table -->
          <div class="space-y-4 pt-2 border-t border-slate-100/50">
            <span
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
              >Services & Scans</span
            >
            <div
              class="bg-slate-50 rounded-2xl overflow-hidden border border-slate-100"
            >
              <table class="w-full text-left">
                <thead>
                  <tr class="border-b border-slate-200/50">
                    <th
                      class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.15em]"
                    >
                      Description
                    </th>
                    <th
                      class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.15em] text-center"
                    >
                      Qty
                    </th>
                    <th
                      class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.15em] text-right"
                    >
                      Price
                    </th>
                    <th
                      class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.15em] text-right"
                    >
                      Amount
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                  <tr v-for="item in invoice?.items" :key="item.id">
                    <td class="px-6 py-4 text-sm text-slate-900">
                      {{ item.description }}
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-500 text-center">
                      {{ item.quantity }}
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-600 text-right">
                      ₹{{ parseFloat(item.unit_price).toFixed(2) }}
                    </td>
                    <td
                      class="px-6 py-4 text-sm font-bold text-slate-900 text-right"
                    >
                      ₹{{ parseFloat(item.amount).toFixed(2) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Totals -->
          <div
            class="flex flex-col items-end space-y-3 pt-6 border-t border-slate-100"
          >
            <div class="w-72 space-y-3">
              <div
                class="flex justify-between text-slate-500 font-bold text-sm px-2"
              >
                <span>Subtotal</span>
                <span
                  >₹{{ parseFloat(invoice?.sub_total || 0).toFixed(2) }}</span
                >
              </div>
              <div
                class="flex justify-between text-rose-500 font-bold text-sm px-2"
                v-if="invoice?.discount_amount > 0"
              >
                <span>Discount</span>
                <span
                  >- ₹{{
                    parseFloat(invoice?.discount_amount).toFixed(2)
                  }}</span
                >
              </div>
              <div
                class="flex justify-between text-slate-500 font-bold text-sm px-2"
                v-if="invoice?.tax_amount > 0"
              >
                <span>Tax</span>
                <span>+ ₹{{ parseFloat(invoice?.tax_amount).toFixed(2) }}</span>
              </div>
              <div
                class="bg-primary/5 rounded-2xl p-4 flex justify-between items-center mt-2 group hover:bg-primary/10 transition-all"
              >
                <span
                  class="text-sm font-black text-slate-900 uppercase tracking-wider"
                  >Total</span
                >
                <span class="text-2xl font-black text-primary"
                  >₹{{
                    parseFloat(invoice?.total_amount || 0).toFixed(2)
                  }}</span
                >
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-8">
        <!-- Status Card -->
        <div
          class="bg-white rounded-3xl border border-slate-200 p-8 shadow-soft-xl space-y-6"
        >
          <div class="flex items-center gap-3">
            <div
              class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center"
            >
              <ActivityIcon class="h-5 w-5 text-slate-600" />
            </div>
            <h4
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400"
            >
              Payment Status
            </h4>
          </div>

          <div
            :class="[
              'w-full py-4 rounded-2xl flex flex-col items-center justify-center gap-1 border',
              invoice?.status === 'paid'
                ? 'bg-emerald-50 border-emerald-100 text-emerald-600'
                : invoice?.status === 'pending'
                  ? 'bg-amber-50 border-amber-100 text-amber-600'
                  : 'bg-slate-50 border-slate-100 text-slate-400',
            ]"
          >
            <span
              class="text-[10px] font-bold uppercase tracking-widest opacity-60"
              >Current State</span
            >
            <span class="text-xl font-bold uppercase tracking-widest">{{
              invoice?.status
            }}</span>
          </div>

          <div class="space-y-3 pt-2 border-t border-slate-100">
            <div class="flex justify-between items-center text-sm">
              <span class="text-slate-400 font-semibold">Total Due</span>
              <span class="text-slate-900 font-bold"
                >₹{{ totalDue.toFixed(2) }}</span
              >
            </div>
            <div class="flex justify-between items-center text-sm">
              <span class="text-slate-400 font-semibold">Total Paid</span>
              <span class="text-emerald-500 font-bold"
                >₹{{ totalPaid.toFixed(2) }}</span
              >
            </div>
          </div>
        </div>

        <!-- Payments History -->
        <div
          class="bg-white rounded-3xl border border-slate-200 p-8 shadow-soft-xl space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700 delay-400"
        >
          <div class="flex items-center gap-3">
            <div
              class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center"
            >
              <WalletIcon class="h-5 w-5 text-slate-600" />
            </div>
            <h4
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400"
            >
              Payments History
            </h4>
          </div>

          <div
            v-if="invoice?.payments?.length === 0"
            class="py-10 text-center italic text-slate-400 text-sm font-medium"
          >
            No payments recorded yet.
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="payment in invoice?.payments"
              :key="payment.id"
              class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2 group hover:border-emerald-200 transition-all"
            >
              <div class="flex justify-between items-center">
                <span class="text-xs font-black text-slate-900">{{
                  formatDate(payment.payment_date)
                }}</span>
                <span class="text-sm font-black text-emerald-600"
                  >₹{{ parseFloat(payment.amount).toFixed(2) }}</span
                >
              </div>
              <div
                class="flex justify-between items-center text-[10px] font-bold text-slate-400 uppercase tracking-widest"
              >
                <span>{{ payment.payment_method?.name }}</span>
                <span v-if="payment.notes" class="truncate max-w-[100px]">{{
                  payment.notes
                }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <TransitionRoot as="template" :show="isPaymentDialogOpen">
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
                class="relative transform overflow-hidden rounded-[32px] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200 flex flex-col"
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
                        <CreditCardIcon class="h-6 w-6 text-white" />
                      </div>
                      <div>
                        <DialogTitle
                          as="h3"
                          class="text-xl font-bold tracking-tight text-white"
                        >
                          Record Payment
                        </DialogTitle>
                        <p class="text-sm font-medium mt-1 opacity-90">
                          Invoice #{{ invoice?.invoice_no }}
                        </p>
                      </div>
                    </div>
                    <button
                      @click.stop="isPaymentDialogOpen = false"
                      class="p-2 rounded-xl hover:bg-white/10 transition-colors"
                    >
                      <XIcon class="h-5 w-5 text-white" />
                    </button>
                  </div>
                </div>

                <form
                  @submit.prevent="recordPayment"
                  class="p-6 sm:p-8 space-y-6"
                >
                  <!-- Error Message Placeholder if needed -->
                  <div
                    v-if="totalDue <= 0"
                    class="bg-amber-50 text-amber-600 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2 border border-amber-100"
                  >
                    <AlertCircleIcon class="h-4 w-4 shrink-0" />
                    <span>This invoice is already fully paid.</span>
                  </div>

                  <div class="space-y-4">
                    <div class="space-y-1.5">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                        >Payment Method</label
                      >
                      <select
                        v-model="paymentForm.payment_method_id"
                        class="w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm focus:ring-primary/20 focus:border-primary transition-all duration-200 font-semibold text-slate-700"
                        required
                      >
                        <option value="">Select Method</option>
                        <option
                          v-for="method in paymentMethods"
                          :key="method.id"
                          :value="method.id"
                        >
                          {{ method.name }}
                        </option>
                      </select>
                    </div>

                    <div class="space-y-1.5">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                        >Amount Paid</label
                      >
                      <input
                        v-model="paymentForm.amount"
                        type="number"
                        step="0.01"
                        class="w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm focus:ring-primary/20 focus:border-primary transition-all duration-200 font-bold text-slate-900"
                        required
                      />
                      <div class="flex justify-between px-1">
                        <span class="text-[10px] text-slate-400 font-medium"
                          >Remaining Due: ₹{{ totalDue.toFixed(2) }}</span
                        >
                      </div>
                    </div>

                    <div class="space-y-1.5">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                        >Payment Date</label
                      >
                      <input
                        v-model="paymentForm.payment_date"
                        type="date"
                        class="w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm focus:ring-primary/20 focus:border-primary transition-all duration-200 font-semibold text-slate-700"
                        required
                      />
                    </div>

                    <div class="space-y-1.5">
                      <label
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1"
                        >Short Note</label
                      >
                      <textarea
                        v-model="paymentForm.notes"
                        rows="2"
                        placeholder="Optional remarks..."
                        class="w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm focus:ring-primary/20 focus:border-primary transition-all duration-200 font-medium text-slate-700 resize-none"
                      ></textarea>
                    </div>
                  </div>

                  <div class="flex gap-3 pt-4">
                    <button
                      type="button"
                      @click="isPaymentDialogOpen = false"
                      class="flex-1 px-6 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all active:scale-95"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      :disabled="isSubmitting || totalDue <= 0"
                      class="flex-1 px-6 py-2.5 rounded-xl bg-primary text-sm font-bold text-white hover:opacity-90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2 active:scale-95 disabled:opacity-50"
                    >
                      <Loader2Icon
                        v-if="isSubmitting"
                        class="h-4 w-4 animate-spin"
                      />
                      Record Payment
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import {
  ArrowLeft as ArrowLeftIcon,
  Printer as PrinterIcon,
  CreditCard as CreditCardIcon,
  Activity as ActivityIcon,
  Wallet as WalletIcon,
  PlusCircle as PlusCircleIcon,
  Loader2 as Loader2Icon,
  X as XIcon,
  AlertCircle as AlertCircleIcon,
} from "lucide-vue-next";
import { useToast } from "../../composables/useToast";

const route = useRoute();
const router = useRouter();
const { addToast } = useToast();

const invoice = ref(null);
const loading = ref(true);
const paymentMethods = ref([]);
const isPaymentDialogOpen = ref(false);
const isSubmitting = ref(false);

const paymentForm = ref({
  invoice_id: route.params.id,
  payment_method_id: "",
  amount: 0,
  payment_date: new Date().toISOString().split("T")[0],
  notes: "",
});

const fetchInvoice = async () => {
  try {
    const response = await axios.get(`/api/v1/invoices/${route.params.id}`);
    invoice.value = response.data;
    paymentForm.value.amount = totalDue.value;
  } catch (error) {
    console.error("Failed to fetch invoice", error);
  } finally {
    loading.value = false;
  }
};

const fetchPaymentMethods = async () => {
  try {
    const response = await axios.get(
      "/api/v1/masters/payment-methods?status=active&nopaginate=1",
    );
    paymentMethods.value = response.data.data;
  } catch (error) {
    console.error("Failed to fetch payment methods", error);
  }
};

const totalPaid = computed(() => {
  if (!invoice.value?.payments) return 0;
  return invoice.value.payments.reduce(
    (sum, p) => sum + parseFloat(p.amount),
    0,
  );
});

const totalDue = computed(() => {
  if (!invoice.value) return 0;
  return parseFloat(invoice.value.total_amount) - totalPaid.value;
});

const formatDate = (date) => {
  if (!date) return "N/A";
  return new Date(date).toLocaleDateString();
};

const printInvoice = () => {
  window.open(`/api/v1/print/invoice/${invoice.value.id}`, "_blank");
};

const recordPayment = async () => {
  isSubmitting.value = true;
  try {
    const response = await axios.post("/api/v1/payments", paymentForm.value);
    addToast({
      title: "Success",
      description: "Payment recorded successfully",
      variant: "success",
    });
    isPaymentDialogOpen.value = false;
    fetchInvoice(); // Refresh data
  } catch (error) {
    addToast({
      title: "Error",
      description: error.response?.data?.message || "Failed to record payment",
      variant: "error",
    });
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  fetchInvoice();
  fetchPaymentMethods();
});
</script>
