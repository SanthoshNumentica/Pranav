<template>
  <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div class="block">
      <!-- Full Width Invoice Details & Preview -->
      <div class="w-full space-y-6">
        <div class="bg-white rounded-[24px] border border-slate-200 p-6 shadow-soft-xl space-y-4">
          <div class="flex items-center justify-between gap-4 pb-2">
            <div class="flex items-center gap-3">
              <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                <div class="h-1 w-1 rounded-full bg-primary"></div>
                Invoice Details
              </h4>
              <div class="h-4 w-px bg-slate-200"></div>
              <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 text-[10px] font-bold tracking-wider">
                Invoice ID: {{ form.invoice_no || 'AUTO_GENERATED' }}
              </span>
            </div>

            <div class="flex items-center gap-3" v-if="form.invoice_id">
              <StatusBadge :status="form.status || ''" type="invoice" />
            </div>
          </div>

          <!-- Manual Invoice Items Table -->
          <div class="overflow-hidden border border-slate-100 rounded-2xl">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50">
                  <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-12 text-center">
                    #
                  </th>
                  <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-1/2">
                    Description
                  </th>
                  <th
                    class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-32 border-l border-slate-100">
                    Amount
                  </th>
                  <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-12 text-center">
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="(item, idx) in form.invoice_items" :key="idx" class="hover:bg-slate-50/50 transition-colors">
                  <td class="px-4 py-4 text-sm font-bold text-slate-400 text-center">
                    {{ idx + 1 }}
                  </td>
                  <td class="px-4 py-2">
                    <div class="flex flex-col">
                      <div class="flex items-center gap-2">
                        <input v-model="item.description" placeholder="Item description" :disabled="!canEdit"
                          class="w-full rounded-xl py-2.5 px-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium disabled:opacity-70 disabled:cursor-not-allowed text-slate-900" />
                        <span v-if="item.scan_type_name"
                          class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded shrink-0">
                          ({{ item.scan_type_name }})
                        </span>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-2 border-l border-slate-50">
                    <div class="relative group">
                      <div
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">
                        <span class="text-xs font-bold pl-1">₹</span>
                      </div>
                      <input v-model.number="item.amount" type="number" step="0.01" :disabled="!canEdit"
                        class="w-full pl-9 rounded-xl py-2.5 px-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium disabled:opacity-70 disabled:cursor-not-allowed text-slate-900" />
                    </div>
                  </td>
                  <td class="px-4 py-4 text-center">
                    <button v-if="canEdit" type="button" @click="removeInvoiceItem(idx)"
                      class="p-1.5 hover:bg-rose-50 text-slate-300 hover:text-rose-500 rounded-lg transition-all">
                      <TrashIcon class="h-4 w-4" />
                    </button>
                  </td>
                </tr>
                <tr v-if="form.invoice_items.length === 0">
                  <td colspan="4" class="px-4 py-12 text-center">
                    <div class="flex flex-col items-center gap-2 opacity-30">
                      <ReceiptIcon class="h-8 w-8 text-slate-400" />
                      <p class="text-sm font-bold text-slate-500">
                        No items added to invoice.
                      </p>
                      <p class="text-[10px] text-slate-400 uppercase tracking-wider">
                        Select scans from the Scan Items tab
                      </p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex justify-start">
            <button v-if="canEdit" type="button" @click="addInvoiceItem()"
              class="px-3 py-1.5 rounded-lg bg-primary/5 text-primary text-[10px] font-bold uppercase tracking-wider hover:bg-primary hover:text-white transition-all active:scale-95 flex items-center gap-2 shadow-sm border border-primary/10">
              <PlusIcon class="h-3 w-3" />
              Add Manual Item
            </button>
          </div>

          <!-- Invoice Details Grid -->
          <div class="space-y-4 pt-2 animate-in fade-in slide-in-from-top-2 duration-300">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
              <div class="md:col-span-3 space-y-1.5">
                <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Notes</label>
                <textarea v-model="form.notes" rows="3" placeholder="Internal notes..." :disabled="!canEdit"
                  class="w-full rounded-xl py-3 px-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium disabled:opacity-70 disabled:cursor-not-allowed resize-none"></textarea>
              </div>
            </div>

            <!-- Discount & Tax -->
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
              <div class="md:col-span-2 space-y-1.5">
                <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Discount
                  Type</label>
                <div class="relative group">
                  <div
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">
                    <TagIcon class="h-4 w-4" />
                  </div>
                  <Select v-model="form.discount_fk_id" :disabled="!canEdit">
                    <SelectTrigger
                      class="pl-9 w-full rounded-xl py-2 h-[42px] border border-slate-200 bg-slate-50 focus:bg-white transition-all text-sm font-medium outline-none text-slate-900 disabled:opacity-70 disabled:cursor-not-allowed">
                      <SelectValue placeholder="Custom Discount" />
                    </SelectTrigger>
                    <SelectContent class="rounded-xl border-slate-100">
                      <SelectItem value="custom">Custom Discount</SelectItem>
                      <SelectItem v-for="d in discounts" :key="d.id" :value="d.id.toString()">
                        {{ d.name }} ({{ d.percentage }}%)
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div class="md:col-span-2 space-y-1.5">
                <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Discount
                  amount</label>
                <div class="relative group">
                  <div
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">
                    <span class="text-xs font-bold pl-1">₹</span>
                  </div>
                  <input v-model.number="form.discount_amount" type="number" step="0.01" placeholder="0.00"
                    :disabled="!canEdit"
                    class="w-full pl-9 rounded-xl py-2.5 px-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium disabled:opacity-70 disabled:cursor-not-allowed text-slate-900" />
                </div>
              </div>

              <div class="md:col-span-2 space-y-1.5">
                <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Tax amount</label>
                <div class="relative group">
                  <div
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">
                    <span class="text-xs font-bold pl-1">₹</span>
                  </div>
                  <input v-model.number="form.tax_amount" type="number" step="0.01" placeholder="0.00"
                    :disabled="!canEdit"
                    class="w-full pl-9 rounded-xl py-2.5 px-4 text-sm border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/5 outline-none transition-all font-medium disabled:opacity-70 disabled:cursor-not-allowed text-slate-900" />
                </div>
              </div>
            </div>
          </div>

          <!-- Summary Section -->
          <div class="bg-slate-50 rounded-3xl p-6 space-y-3">
            <div class="flex justify-between items-center text-slate-500">
              <span class="text-xs font-bold uppercase tracking-wider">Subtotal</span>
              <span class="font-bold text-sm">₹{{ subTotal.toFixed(2) }}</span>
            </div>
            <div class="flex justify-between items-center text-rose-500" v-if="form.discount_amount > 0">
              <span class="text-xs font-bold uppercase tracking-wider">Discount</span>
              <span class="font-bold text-sm">- ₹{{ parseFloat(form.discount_amount).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between items-center text-slate-500" v-if="form.tax_amount > 0">
              <span class="text-xs font-bold uppercase tracking-wider">Tax</span>
              <span class="font-bold text-sm">+ ₹{{ parseFloat(form.tax_amount).toFixed(2) }}</span>
            </div>
            <div class="pt-4 border-t border-slate-200 flex justify-between items-center text-slate-900">
              <span class="text-xs font-black uppercase tracking-widest text-slate-400">Grand Total</span>
              <span class="text-2xl font-black text-primary">₹{{ totalAmount.toFixed(2) }}</span>
            </div>
          </div>

        </div>

        <!-- Payment Details Section -->
        <div class="bg-white rounded-[24px] border border-slate-200 p-6 shadow-soft-xl space-y-4">
          <div class="flex items-center justify-between gap-4 pb-2">
            <div class="flex items-center gap-3">
              <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                <div class="h-1 w-1 rounded-full bg-primary"></div>
                Payment Details
              </h4>
            </div>
          </div>

          <!-- Payment History Table -->
          <div class="overflow-hidden border border-slate-100 rounded-2xl">
            <template v-if="!form.payment_history || form.payment_history.length === 0">
              <div
                class="flex flex-col items-center justify-center text-slate-400 py-12 text-center space-y-2 bg-slate-50/50">
                <ReceiptIcon class="w-10 h-10 opacity-20 mb-2" />
                <p class="text-[11px] font-bold uppercase tracking-wider">No payments added</p>
              </div>
            </template>
            <table v-else class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50">
                  <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-12 text-center">
                    #
                  </th>
                  <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    Payment ID
                  </th>
                  <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    Payment Method
                  </th>
                  <th
                    class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-32 border-l border-slate-100">
                    Amount
                  </th>
                  <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    Payment Date
                  </th>
                  <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    Notes
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="(payment, idx) in form.payment_history" :key="idx"
                  class="hover:bg-slate-50/50 transition-colors">
                  <td class="px-4 py-4 text-sm font-bold text-slate-400 text-center">
                    {{ idx + 1 }}
                  </td>
                  <td class="px-4 py-4 text-sm font-bold text-slate-700">
                    {{ payment.payment_id || 'UNKNOWN' }}
                  </td>
                  <td class="px-4 py-4 text-sm font-medium text-slate-700">
                    {{ getMethodName(payment.payment_method_fk_id) || 'Unknown Method' }}
                  </td>
                  <td class="px-4 py-4 text-sm font-bold text-primary border-l border-slate-50">
                    ₹{{ parseFloat(payment.amount || 0).toFixed(2) }}
                  </td>
                  <td class="px-4 py-4 text-sm font-medium text-slate-600">
                    {{ payment.payment_date ? new Date(payment.payment_date).toLocaleDateString('en-GB', {
                      day:
                        '2-digit', month:
                        'short', year: 'numeric'
                    }) : '-' }}
                  </td>
                  <td class="px-4 py-4 text-sm font-medium text-slate-500 italic max-w-xs truncate"
                    :title="payment.notes">
                    {{ payment.notes || '-' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Summary Totals from DB -->
          <div class="flex items-center justify-end gap-6 pt-6 border-t border-slate-50 mt-4">

            <div class="flex flex-col items-end">
              <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 leading-none mb-1">Total
                Amount</span>
              <span class="text-xl font-bold text-slate-700 leading-none tabular-nums">₹{{
                Number(form.invoice_total_amount ||
                  0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
            </div>

            <div class="h-8 w-px bg-slate-200"></div>

            <div class="flex flex-col items-end">
              <span
                class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-500/80 leading-none mb-1">Total
                Paid</span>
              <span class="text-xl font-black text-emerald-500 leading-none tabular-nums">₹{{
                Number(form.invoice_paid_amount ||
                  0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
            </div>

            <div class="h-8 w-px bg-slate-200"></div>

            <div class="flex flex-col items-end bg-rose-50 px-4 py-2 rounded-xl border border-rose-100/50">
              <span class="text-[10px] font-black uppercase tracking-[0.2em] text-rose-500/80 leading-none mb-1">Due
                Amount</span>
              <span class="text-2xl font-black text-rose-500 leading-none tabular-nums">₹{{ Math.max(0,
                Number(form.invoice_total_amount || 0) - Number(form.invoice_paid_amount || 0)).toLocaleString('en-IN',
                  {
                minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
            </div>

          </div>
        </div>

        <!-- Action Buttons Section -->
        <div class="flex items-center justify-between gap-4 pt-4">
          <div class="flex items-center gap-3">
            <button v-if="form.invoice_id" type="button" @click="handlePrint"
              class="px-6 py-2.5 rounded-xl border border-primary/20 text-primary font-bold text-sm hover:bg-primary/5 transition-all active:scale-95 flex items-center gap-2">
              <PrinterIcon class="h-4 w-4" />
              Print Invoice
            </button>
          </div>

          <div class="flex items-center gap-3">
            <!-- Submit button could go here or is handled by parent -->
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import StatusBadge from "../../ui/StatusBadge.vue";
import {
  Receipt as ReceiptIcon,
  Tag as TagIcon,
  Trash2 as TrashIcon,
  Printer as PrinterIcon,
  Plus as PlusIcon,
  CreditCard as CreditCardIcon,
  Calendar as CalendarIcon,
} from "lucide-vue-next";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "../../../components/ui/select";

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
  processing: {
    type: Boolean,
    default: false,
  },
  subTotal: {
    type: Number,
    default: 0,
  },
  totalAmount: {
    type: Number,
    default: 0,
  },
  addInvoiceItem: {
    type: Function,
    required: true,
  },
  removeInvoiceItem: {
    type: Function,
    required: true,
  },
  discounts: {
    type: Array,
    default: () => [],
  },
  paymentMethods: {
    type: Array,
    default: () => [],
  },
  canEdit: {
    type: Boolean,
    default: true,
  },
  totalPaid: {
    type: Number,
    default: 0,
  },
  addPaymentRow: {
    type: Function,
    required: true,
  },
  removePaymentRow: {
    type: Function,
    required: true,
  },
});

defineEmits(["submit"]);

const handlePrint = () => {
  if (props.form.invoice_id) {
    window.open(`/api/v1/print/invoice/${props.form.invoice_id}`, "_blank");
  }
};

const getMethodName = (id) => {
  if (!id) return '';
  const method = props.paymentMethods.find(m => String(m.id) === String(id));
  return method ? method.name : 'Unknown Method';
};
</script>
