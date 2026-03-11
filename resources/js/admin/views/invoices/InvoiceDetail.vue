<template>
  <div class="max-w-5xl mx-auto space-y-8 pb-20 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <!-- Invoice Header -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
      <div class="flex items-center gap-4">
        <button @click="$router.push('/invoices')"
          class="p-2.5 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-primary hover:border-primary/20 transition-all shadow-sm active:scale-95">
          <ArrowLeftIcon class="h-5 w-5" />
        </button>
        <div>
          <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
            Invoice #{{ invoice?.invoice_id }}
          </h1>
          <p class="text-sm text-slate-500 mt-1">
            Recorded on {{ formatDate(invoice?.invoice_date) }}
          </p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button @click="printInvoice"
          class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold transition-all hover:bg-slate-50 active:scale-95 shadow-sm">
          <PrinterIcon class="h-4 w-4" />
          Print
        </button>
        <button v-if="invoice?.status !== 'fully_paid' && invoice?.status !== 'cancelled'"
          @click="isPaymentDialogOpen = true"
          class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-700 text-white rounded-xl text-sm font-semibold transition-all shadow-md shadow-primary/10 active:scale-95">
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
          class="bg-white rounded-3xl border border-slate-200 p-8 shadow-soft-xl space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 delay-100">
          <div class="flex flex-col sm:flex-row justify-between gap-8 sm:gap-10">
            <div class="space-y-4 flex-1">
              <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Bill To</span>
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
                  <p class="text-slate-500 font-medium" v-if="invoice?.patient?.mobile_no">
                    Ph: {{ invoice?.patient?.mobile_no }}
                  </p>
                </div>
              </div>
            </div>
            <div class="text-left sm:text-right space-y-4 flex-1">
              <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 mr-1">Branch</span>
              <div class="space-y-1.5 px-1">
                <h4 class="text-lg font-bold text-slate-900">
                  {{ invoice?.branch?.name }}
                </h4>
                <div class="space-y-0.5">
                  <p class="text-xs text-slate-500 font-medium" v-if="invoice?.branch?.address">
                    {{ invoice?.branch?.address }}
                  </p>
                  <p class="text-xs text-slate-500 font-medium" v-if="invoice?.branch?.phone">
                    Ph: {{ invoice?.branch?.phone }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Items Table -->
          <div class="space-y-4 pt-2 border-t border-slate-100/50">
            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Services & Scans</span>
            <div class="bg-slate-50 rounded-2xl overflow-hidden border border-slate-100">
              <table class="w-full text-left">
                <thead>
                  <tr class="border-b border-slate-200/50">
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.15em]">
                      Description
                    </th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-[0.15em] text-right">
                      Amount
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                  <tr v-for="item in invoice?.items" :key="item.id">
                    <td class="px-6 py-4 text-sm text-slate-900">
                      <div class="flex items-center gap-2">
                        <span>{{ item.description }}</span>
                        <span v-if="item.scan_type_name"
                          class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">
                          ({{ item.scan_type_name }})
                        </span>
                      </div>
                    </td>
                    <td class="px-6 py-4 text-sm font-bold text-slate-900 text-right">
                      ₹{{ parseFloat(item.amount).toFixed(2) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Totals -->
          <div class="flex flex-col items-end space-y-3 pt-6 border-t border-slate-100">
            <div class="w-72 space-y-3">
              <div class="flex justify-between text-slate-500 font-bold text-sm px-2">
                <span>Subtotal</span>
                <span>₹{{ parseFloat(invoice?.sub_total || 0).toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-rose-500 font-bold text-sm px-2"
                v-if="invoice?.discount_amount > 0">
                <span>Discount</span>
                <span>- ₹{{
                  parseFloat(invoice?.discount_amount).toFixed(2)
                }}</span>
              </div>
              <div class="flex justify-between text-slate-500 font-bold text-sm px-2" v-if="invoice?.tax_amount > 0">
                <span>Tax</span>
                <span>+ ₹{{ parseFloat(invoice?.tax_amount).toFixed(2) }}</span>
              </div>
              <div
                class="bg-primary/5 rounded-2xl p-4 flex justify-between items-center mt-2 group hover:bg-primary/10 transition-all">
                <span class="text-sm font-black text-slate-900 uppercase tracking-wider">Total</span>
                <span class="text-2xl font-black text-primary">₹{{
                  parseFloat(invoice?.total_amount || 0).toFixed(2)
                }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-8">
        <!-- Status Card -->
        <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-soft-xl space-y-6">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center">
              <ActivityIcon class="h-5 w-5 text-slate-600" />
            </div>
            <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
              Payment Status
            </h4>
          </div>

          <div
            class="w-full h-24 rounded-2xl flex flex-col items-center justify-center gap-2 border bg-slate-50/50 border-slate-100">
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Current State</span>
            <StatusBadge :status="invoice?.status || ''" type="invoice" class="scale-125 origin-center" />
          </div>

          <div class="space-y-3 pt-2 border-t border-slate-100">
            <div class="flex justify-between items-center text-sm">
              <span class="text-slate-400 font-semibold">Total Amount</span>
              <span class="text-slate-900 font-bold tabular-nums">₹{{ parseFloat(invoice?.total_amount ||
                0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
            </div>
            <div class="flex justify-between items-center text-sm">
              <span class="text-slate-400 font-semibold">Total Paid</span>
              <span class="text-emerald-500 font-bold tabular-nums">₹{{ parseFloat(invoice?.paid_amount ||
                totalPaid).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
            </div>
            <div class="flex justify-between items-center text-sm pt-2 border-t border-slate-50">
              <span class="text-slate-500 font-bold">Total Due</span>
              <span class="text-rose-500 font-bold tabular-nums">₹{{ parseFloat(invoice?.due_amount !== undefined ?
                invoice.due_amount : totalDue).toLocaleString('en-IN', {
                  minimumFractionDigits: 2,
                maximumFractionDigits: 2 }) }}</span>
            </div>
          </div>
        </div>

        <!-- Payments History -->
        <div
          class="bg-white rounded-3xl border border-slate-200 p-8 shadow-soft-xl space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700 delay-400">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center">
              <WalletIcon class="h-5 w-5 text-slate-600" />
            </div>
            <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
              Payments History
            </h4>
          </div>

          <div v-if="invoice?.payments?.length === 0"
            class="py-10 text-center italic text-slate-400 text-sm font-medium">
            No payments recorded yet.
          </div>

          <div v-else class="overflow-hidden border border-slate-100 rounded-2xl">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50">
                  <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
                    Payment Date</th>
                  <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
                    Amount Paid</th>
                  <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
                    Method Name</th>
                  <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Notes</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="payment in invoice?.payments" :key="payment.id"
                  class="hover:bg-slate-50/50 transition-colors">
                  <td class="px-4 py-4 text-sm font-medium text-slate-700 whitespace-nowrap">{{
                    formatDate(payment.payment_date) }}</td>
                  <td class="px-4 py-4 text-sm font-bold text-emerald-600 whitespace-nowrap">₹{{
                    parseFloat(payment.amount).toLocaleString('en-IN', {
                      minimumFractionDigits: 2,
                    maximumFractionDigits: 2 }) }}</td>
                  <td class="px-4 py-4 text-sm font-medium text-slate-600 whitespace-nowrap">{{
                    payment.payment_method?.name || 'Unknown' }}</td>
                  <td class="px-4 py-4 text-sm font-medium text-slate-500 italic max-w-[150px] truncate"
                    :title="payment.notes">{{ payment.notes || '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <TransitionRoot as="template" :show="isPaymentDialogOpen">
      <Dialog as="div" class="relative z-50" @close="() => { }">
        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
          leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" />
        </TransitionChild>

        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <TransitionChild as="template" enter="ease-out duration-300"
              enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
              enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
              leave-from="opacity-100 translate-y-0 sm:scale-100"
              leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
              <DialogPanel
                class="relative transform overflow-hidden rounded-[32px] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200 flex flex-col h-[90vh] sm:h-[80vh]">
                <!-- Header/Banner - Fixed -->
                <div class="relative bg-primary px-6 py-8 sm:px-10 text-white overflow-hidden shrink-0">
                  <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                  <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>

                  <div class="relative flex items-center justify-between">
                    <div class="flex items-center gap-4">
                      <div
                        class="h-12 w-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30">
                        <CreditCardIcon class="h-6 w-6 text-white" />
                      </div>
                      <div>
                        <DialogTitle as="h3" class="text-xl font-bold tracking-tight text-white">
                          New Payment
                        </DialogTitle>
                        <p class="text-sm font-medium mt-1 opacity-90">
                          Invoice #{{ invoice?.invoice_id }}
                        </p>
                      </div>
                    </div>
                    <button @click.stop="isPaymentDialogOpen = false"
                      class="p-2 rounded-xl hover:bg-white/10 transition-colors">
                      <XIcon class="h-5 w-5 text-white" />
                    </button>
                  </div>
                </div>

                <!-- Form Body - Scrollable -->
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                  <form @submit.prevent="recordPayment" class="p-6 sm:p-8 space-y-6">
                    <!-- Error Message Placeholder if needed -->
                    <div v-if="totalDue <= 0"
                      class="bg-amber-50 text-amber-600 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2 border border-amber-100">
                      <AlertCircleIcon class="h-4 w-4 shrink-0" />
                      <span>This invoice is already fully paid.</span>
                    </div>

                    <div class="space-y-4">
                      <div class="space-y-4">
                        <div v-for="(payment, index) in paymentForm.payments" :key="index" class="p-4 bg-slate-50/50 rounded-2xl border border-slate-100 relative group">
                          <button v-if="paymentForm.payments.length > 1" type="button" @click="removePaymentRow(index)" class="absolute -right-2 -top-2 h-6 w-6 rounded-full bg-white border border-slate-200 text-slate-400 hover:text-rose-500 hover:border-rose-200 shadow-sm flex items-center justify-center transition-all opacity-0 group-hover:opacity-100">
                            <XIcon class="h-3 w-3" />
                          </button>
                          
                          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                              <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Payment Method</label>
                              <Select v-model="payment.payment_method_fk_id" required>
                                <SelectTrigger class="h-10 rounded-xl border-slate-200 bg-white">
                                  <SelectValue placeholder="Select Method" />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl border-slate-100">
                                  <SelectItem v-for="method in paymentMethods" :key="method.id" :value="String(method.id)">
                                    {{ method.name }}
                                  </SelectItem>
                                </SelectContent>
                              </Select>
                            </div>
      
                            <div class="space-y-1.5">
                              <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Amount Paid</label>
                              <input v-model="payment.amount" type="number" step="0.01" class="w-full bg-white border-slate-200 rounded-xl h-10 px-4 text-sm focus:ring-primary/20 focus:border-primary transition-all font-bold text-slate-900" required />
                            </div>
                          </div>
                        </div>

                        <button type="button" @click="addPaymentRow" class="w-full py-3 flex items-center justify-center gap-2 border-2 border-dashed border-slate-200 rounded-2xl text-sm font-bold text-slate-500 hover:text-primary hover:border-primary/30 hover:bg-primary/5 transition-all outline-none">
                          <PlusCircleIcon class="h-4 w-4" />
                          Add Split Payment
                        </button>
                        
                        <div class="flex items-center justify-between px-2 pt-2">
                          <span class="text-xs font-bold text-slate-500">Total Paying: <span class="text-primary ml-1">₹{{ currentTotalPaying.toFixed(2) }}</span></span>
                          <span class="text-[10px] text-slate-400 font-medium">Remaining Due: ₹{{ Math.max(0, totalDue - currentTotalPaying).toFixed(2) }}</span>
                        </div>
                      </div>

                      <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Payment
                          Date</label>
                        <input v-model="paymentForm.payment_date" type="date"
                          class="w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm focus:ring-primary/20 focus:border-primary transition-all duration-200 font-semibold text-slate-700"
                          required />
                      </div>

                      <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Short
                          Note</label>
                        <textarea v-model="paymentForm.notes" rows="2" placeholder="Optional remarks..."
                          class="w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm focus:ring-primary/20 focus:border-primary transition-all duration-200 font-medium text-slate-700 resize-none"></textarea>
                      </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                      <button type="button" @click="isPaymentDialogOpen = false"
                        class="flex-1 px-6 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all active:scale-95">
                        Cancel
                      </button>
                      <button type="submit" :disabled="isSubmitting || totalDue <= 0"
                        class="flex-1 px-6 py-2.5 rounded-xl bg-primary text-sm font-bold text-white hover:opacity-90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2 active:scale-95 disabled:opacity-50">
                        <Loader2Icon v-if="isSubmitting" class="h-4 w-4 animate-spin" />
                        Record Payment
                      </button>
                    </div>
                  </form>
                </div>
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
import StatusBadge from "../../components/ui/StatusBadge.vue";
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
import { formatDate } from "../../utils/format";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../../components/ui/select";

const route = useRoute();
const router = useRouter();
const { addToast } = useToast();

const invoice = ref(null);
const loading = ref(true);
const paymentMethods = ref([]);
const isPaymentDialogOpen = ref(false);
const isSubmitting = ref(false);

const paymentForm = ref({
  invoice_fk_id: route.params.id,
  payments: [
    { payment_method_fk_id: "", amount: 0 }
  ],
  payment_date: new Date().toISOString().split("T")[0],
  notes: "",
});

const currentTotalPaying = computed(() => {
  return paymentForm.value.payments.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
});

const addPaymentRow = () => {
  const remainingDue = totalDue.value - currentTotalPaying.value;
  paymentForm.value.payments.push({
    payment_method_fk_id: "",
    amount: Math.max(0, remainingDue)
  });
};

const removePaymentRow = (index) => {
  paymentForm.value.payments.splice(index, 1);
};

const fetchInvoice = async () => {
  try {
    const response = await axios.get(`/api/v1/invoices/${route.params.id}`);
    const data = response.data;

    // Parse payment_details JSON and flatten payments
    if (data.payments && data.payments.length > 0) {
      let parsed = [];
      data.payments.forEach(paymentRecord => {
        try {
          const details = typeof paymentRecord.payment_details === 'string'
            ? JSON.parse(paymentRecord.payment_details)
            : (paymentRecord.payment_details || {});

          if (details && details.payments && Array.isArray(details.payments)) {
            details.payments.forEach(p => {
              parsed.push({
                ...paymentRecord,
                id: paymentRecord.id + '-' + Math.random(),
                payment_method_fk_id: p.payment_method_fk_id,
                amount: p.amount || 0,
                payment_date: paymentRecord.payment_date ? paymentRecord.payment_date.split('T')[0] : p.payment_date,
                notes: p.notes || details.notes || paymentRecord.notes || "",
                payment_method: paymentMethods.value.find(m => String(m.id) === String(p.payment_method_fk_id))
                  || paymentRecord.payment_method
              });
            });
          } else {
            parsed.push({
              ...paymentRecord,
              notes: paymentRecord.notes || details.notes || "",
            });
          }
        } catch (e) {
          console.error("Failed to parse payment details JSON", e);
          parsed.push(paymentRecord);
        }
      });
      // Sort parsed payments by date descending
      parsed.sort((a, b) => new Date(b.payment_date) - new Date(a.payment_date));
      data.payments = parsed;
    }

    invoice.value = data;
    if (paymentForm.value.payments.length === 1) {
      paymentForm.value.payments[0].amount = Math.max(0, totalDue.value);
    }
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



const printInvoice = () => {
  window.open(`/api/v1/print/invoice/${invoice.value.id}`, "_blank");
};

const recordPayment = async () => {
  isSubmitting.value = true;
  try {
    const validPayments = paymentForm.value.payments.filter(
      p => p.payment_method_fk_id && parseFloat(p.amount) > 0
    );

    if (validPayments.length === 0) {
      addToast({ title: "Validation Error", description: "You must add at least one valid payment amount.", variant: "danger" });
      isSubmitting.value = false;
      return;
    }

    const payload = {
      invoice_fk_id: paymentForm.value.invoice_fk_id,
      payment_date: paymentForm.value.payment_date,
      notes: paymentForm.value.notes,
      payment_details: JSON.stringify({
        notes: paymentForm.value.notes,
        payments: validPayments.map(p => ({
          payment_method_fk_id: p.payment_method_fk_id,
          amount: parseFloat(p.amount)
        }))
      })
    };

    const response = await axios.post("/api/v1/payments", payload);
    addToast({
      title: "Success",
      description: "Payment recorded successfully",
      variant: "success",
    });
    isPaymentDialogOpen.value = false;
    
    // Reset form dynamically based on new invoice data
    fetchInvoice().then(() => {
      paymentForm.value.payments = [{ payment_method_fk_id: "", amount: Math.max(0, totalDue.value) }];
      paymentForm.value.notes = "";
    });
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

onMounted(async () => {
  await fetchPaymentMethods();
  await fetchInvoice();
});
</script>
