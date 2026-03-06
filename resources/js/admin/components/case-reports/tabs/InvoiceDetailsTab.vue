<template>
  <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div class="block">
      <!-- Full Width Invoice Details & Preview -->
      <div class="w-full space-y-6">
        <div class="bg-white rounded-[32px] border border-slate-200 p-8 shadow-soft-xl space-y-8">
          <div class="flex items-center justify-between gap-4 pb-2">
            <div class="flex items-center gap-3">
              <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                <div class="h-1 w-1 rounded-full bg-primary"></div>
                Invoice Details
              </h4>
              <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 text-[10px] font-bold tracking-wider">
                Invoice ID: {{ form.invoice_no || '--' }}
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
                  <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
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
                          class="bg-transparent border-none p-0 focus:ring-0 font-medium text-slate-700 text-sm placeholder:text-slate-300 disabled:opacity-70 disabled:cursor-not-allowed" />
                        <span v-if="item.scan_type_name"
                          class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">
                          ({{ item.scan_type_name }})
                        </span>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-2 border-l border-slate-50">
                    <div class="flex items-center gap-1">
                      <span class="text-slate-400 font-bold text-sm">₹</span>
                      <input v-model.number="item.amount" type="number" step="0.01" :disabled="!canEdit"
                        class="w-full bg-transparent border-none p-0 focus:ring-0 font-bold text-slate-900 text-sm disabled:opacity-70 disabled:cursor-not-allowed" />
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

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Notes -->
            <div class="space-y-2 md:col-span-2">
              <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Notes</label>
              <textarea v-model="form.notes" rows="3" placeholder="Internal notes..." :disabled="!canEdit"
                class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-slate-900 placeholder:text-slate-400 text-sm disabled:opacity-70 disabled:cursor-not-allowed resize-none"></textarea>
            </div>

            <!-- Discount & Tax in a smaller grid -->
            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Discount
                  Type</label>
                <div class="relative group">
                  <div
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">
                    <TagIcon class="h-4 w-4" />
                  </div>
                  <Select v-model="form.discount_id" :disabled="!canEdit">
                    <SelectTrigger
                      class="pl-11 h-12 rounded-xl bg-slate-50 border-none font-bold text-slate-900 text-sm disabled:opacity-70 disabled:cursor-not-allowed">
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

              <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Discount
                  amount</label>
                <div class="relative group">
                  <div
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">
                    <span class="text-xs font-bold pl-1">₹</span>
                  </div>
                  <input v-model.number="form.discount_amount" type="number" step="0.01" placeholder="0.00"
                    :disabled="!canEdit"
                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-slate-900 text-sm disabled:opacity-70 disabled:cursor-not-allowed" />
                </div>
              </div>

              <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Tax amount</label>
                <div class="relative group">
                  <div
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">
                    <span class="text-xs font-bold pl-1">₹</span>
                  </div>
                  <input v-model.number="form.tax_amount" type="number" step="0.01" placeholder="0.00"
                    :disabled="!canEdit"
                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-slate-900 text-sm disabled:opacity-70 disabled:cursor-not-allowed" />
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
        <div class="bg-white rounded-[32px] border border-slate-200 p-8 shadow-soft-xl space-y-8">
          <div class="flex items-center justify-between gap-4 pb-2">
            <div class="flex items-center gap-3">
              <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                <div class="h-1 w-1 rounded-full bg-primary"></div>
                Payment Details
              </h4>
              <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 text-[10px] font-bold tracking-wider">
                Payment ID: {{ form.payment_id || '--' }}
              </span>
            </div>
          </div>

          <!-- Multi-Row Payment Table -->
          <div class="overflow-hidden border border-slate-100 rounded-2xl">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50">
                  <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-12 text-center">
                    #
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
                  <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-12 text-center">
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="(payment, idx) in form.payments" :key="idx" class="hover:bg-slate-50/50 transition-colors">
                  <td class="px-4 py-4 text-sm font-bold text-slate-400 text-center">
                    {{ idx + 1 }}
                  </td>
                  <td class="px-4 py-2">
                    <Select v-model="payment.payment_method_id" :disabled="!canEdit">
                      <SelectTrigger class="h-9 rounded-lg bg-slate-50 border-none font-bold text-slate-900 text-xs">
                        <SelectValue placeholder="Select Method" />
                      </SelectTrigger>
                      <SelectContent class="rounded-xl border-slate-100">
                        <SelectItem v-for="method in paymentMethods" :key="method.id" :value="method.id.toString()">
                          {{ method.name }}
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </td>
                  <td class="px-4 py-2 border-l border-slate-50">
                    <div class="flex items-center gap-1">
                      <span class="text-slate-400 font-bold text-sm">₹</span>
                      <input v-model.number="payment.amount" type="number" step="0.01" :disabled="!canEdit"
                        class="w-full bg-transparent border-none p-0 focus:ring-0 font-bold text-slate-900 text-sm disabled:opacity-70 disabled:cursor-not-allowed" />
                    </div>
                  </td>
                  <td class="px-4 py-2">
                    <input v-model="payment.payment_date" type="date" :disabled="!canEdit"
                      class="w-full bg-transparent border-none p-0 focus:ring-0 font-bold text-slate-900 text-sm disabled:opacity-70 disabled:cursor-not-allowed" />
                  </td>
                  <td class="px-4 py-2">
                    <input v-model="payment.notes" placeholder="Notes" :disabled="!canEdit"
                      class="w-full bg-transparent border-none p-0 focus:ring-0 font-medium text-slate-700 text-sm placeholder:text-slate-300 disabled:opacity-70 disabled:cursor-not-allowed" />
                  </td>
                  <td class="px-4 py-4 text-center">
                    <button v-if="canEdit && form.payments.length > 1" type="button" @click="removePaymentRow(idx)"
                      class="p-1.5 hover:bg-rose-50 text-slate-300 hover:text-rose-500 rounded-lg transition-all">
                      <TrashIcon class="h-4 w-4" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Add Payment Button & Total Paid Display -->
          <div class="flex flex-col md:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-50">
            <button type="button" @click="addPaymentRow" :disabled="!canEdit"
              class="px-5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-100 transition-all active:scale-95 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
              <PlusIcon class="h-3.5 w-3.5" />
              Add Payment
            </button>

            <div class="flex items-center gap-8 px-6 py-4 rounded-2xl bg-primary/5 border border-primary/10">
              <div class="flex flex-col items-end">
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary/60 leading-none mb-1">Total
                  Paid</span>
                <span class="text-xl font-black text-primary leading-none tabular-nums">₹{{
                  totalPaid.toLocaleString('en-IN', {
                    minimumFractionDigits: 2, maximumFractionDigits: 2
                  })
                }}</span>
              </div>
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
</script>
