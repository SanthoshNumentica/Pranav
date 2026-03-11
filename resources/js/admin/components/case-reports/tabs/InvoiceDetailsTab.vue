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
              <span v-if="isEditMode" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 text-[10px] font-bold tracking-wider">
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
                  <td colspan="4" class="px-4 py-8 text-center">
                    <div class="flex flex-col items-center gap-2 opacity-30">
                      <ReceiptIcon class="h-6 w-6 text-slate-400" />
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
            
            <button type="button" v-if="canEdit" @click="isPaymentDialogOpen = true"
              class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm active:scale-95">
              <CreditCardIcon class="h-3.5 w-3.5" />
              Add Payment
            </button>
          </div>

          <!-- Payment History Table -->
          <div class="overflow-hidden border border-slate-100 rounded-2xl">
            <template v-if="!form.payment_history || form.payment_history.length === 0">
              <div
                class="flex flex-col items-center justify-center text-slate-400 py-8 text-center space-y-2 bg-slate-50/50">
                <ReceiptIcon class="w-8 h-8 opacity-20" />
                <p class="text-[11px] font-bold uppercase tracking-wider mt-0">No payments added</p>
              </div>
            </template>
            <table v-else class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50">
                  <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-12 text-center">
                    #
                  </th>
                  <!-- Removed Payment ID column -->
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
                  <!-- Removed Payment ID field -->
                  <td class="px-4 py-4 text-sm font-medium text-slate-700">
                    {{ getMethodName(payment.payment_method_fk_id) || 'Unknown Method' }}
                  </td>
                  <td class="px-4 py-4 text-sm font-bold text-primary border-l border-slate-50">
                    ₹{{ parseFloat(payment.amount || 0).toFixed(2) }}
                  </td>
                  <td class="px-4 py-4 text-sm font-medium text-slate-600">
                    {{ payment.payment_date ? formatDate(payment.payment_date) : '-' }}
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
                Number(totalAmount ||
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
                Number(totalAmount || 0) - Number(form.invoice_paid_amount || 0)).toLocaleString('en-IN',
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

    <!-- New Payment Modal (Embedded directly or use a shared component, we will recreate it here for simplicity and consistency with the state) -->
    <TransitionRoot as="template" :show="isPaymentDialogOpen">
      <Dialog as="div" class="relative z-50" @close="() => {}">
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
                      <div class="h-12 w-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30">
                        <CreditCardIcon class="h-6 w-6 text-white" />
                      </div>
                      <div>
                        <DialogTitle as="h3" class="text-xl font-bold tracking-tight text-white">
                          New Payment
                        </DialogTitle>
                        <p class="text-sm font-medium mt-1 opacity-90">
                          <template v-if="form.invoice_no">Invoice #{{ form.invoice_no }}</template>
                          <template v-else>Draft Invoice</template>
                        </p>
                      </div>
                    </div>
                    <button type="button" @click.stop="isPaymentDialogOpen = false"
                      class="p-2 rounded-xl hover:bg-white/10 transition-colors">
                      <XIcon class="h-5 w-5 text-white" />
                    </button>
                  </div>
                </div>

                <!-- Form Body - Scrollable -->
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                  <form @submit.prevent="confirmPayment" class="p-6 sm:p-8 space-y-6">
                    <div v-if="dueAmount <= 0" class="bg-amber-50 text-amber-600 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2 border border-amber-100">
                      <AlertCircleIcon class="h-4 w-4 shrink-0" />
                      <span>This invoice is already fully paid.</span>
                    </div>

                    <div class="space-y-4">
                      <div class="space-y-4">
                        <div v-for="(payment, index) in localPaymentForm.payments" :key="index" class="p-4 bg-slate-50/50 rounded-2xl border border-slate-100 relative group">
                          <button v-if="localPaymentForm.payments.length > 1" type="button" @click="removeLocalPaymentRow(index)" class="absolute -right-2 -top-2 h-6 w-6 rounded-full bg-white border border-slate-200 text-slate-400 hover:text-rose-500 hover:border-rose-200 shadow-sm flex items-center justify-center transition-all opacity-0 group-hover:opacity-100">
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

                        <button type="button" @click="addLocalPaymentRow" class="w-full py-3 flex items-center justify-center gap-2 border-2 border-dashed border-slate-200 rounded-2xl text-sm font-bold text-slate-500 hover:text-primary hover:border-primary/30 hover:bg-primary/5 transition-all outline-none">
                          <PlusCircleIcon class="h-4 w-4" />
                          Add Split Payment
                        </button>
                        
                        <div class="flex items-center justify-between px-2 pt-2">
                          <span class="text-xs font-bold text-slate-500">Total Paying: <span class="text-primary ml-1">₹{{ currentTotalPaying.toFixed(2) }}</span></span>
                          <span class="text-[10px] text-slate-400 font-medium">Remaining Due: ₹{{ Math.max(0, dueAmount - currentTotalPaying).toFixed(2) }}</span>
                        </div>
                      </div>

                      <div class="space-y-1.5 pt-4">
                        <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Payment Date</label>
                        <input v-model="localPaymentForm.payment_date" type="date" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm focus:ring-primary/20 focus:border-primary transition-all duration-200 font-semibold text-slate-700" required />
                      </div>

                      <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 ml-1">Short Note</label>
                        <textarea v-model="localPaymentForm.notes" rows="2" placeholder="Optional remarks..." class="w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm focus:ring-primary/20 focus:border-primary transition-all duration-200 font-medium text-slate-700 resize-none"></textarea>
                      </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                      <button type="button" @click="isPaymentDialogOpen = false" class="flex-1 px-6 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all active:scale-95">
                        Cancel
                      </button>
                      <button type="submit" :disabled="dueAmount <= 0" class="flex-1 px-6 py-2.5 rounded-xl bg-primary text-sm font-bold text-white hover:opacity-90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2 active:scale-95 disabled:opacity-50">
                        Confirm Payment
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
import { computed, ref, watch } from "vue";
import { useRoute } from "vue-router";
import StatusBadge from "../../ui/StatusBadge.vue";
import { formatDate } from "../../../utils/format";
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import {
  Receipt as ReceiptIcon,
  Tag as TagIcon,
  Trash2 as TrashIcon,
  Printer as PrinterIcon,
  Plus as PlusIcon,
  CreditCard as CreditCardIcon,
  Calendar as CalendarIcon,
  PlusCircle as PlusCircleIcon,
  X as XIcon,
  AlertCircle as AlertCircleIcon,
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

const route = useRoute();
const isEditMode = computed(() => route.path.includes("/edit"));

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

// Payment Modal Logic
const isPaymentDialogOpen = ref(false);

const dueAmount = computed(() => {
  return Math.max(0, props.totalAmount - props.totalPaid);
});

const localPaymentForm = ref({
  payments: [
    { payment_method_fk_id: "", amount: dueAmount.value }
  ],
  payment_date: new Date().toISOString().split("T")[0],
  notes: "",
});

watch(() => isPaymentDialogOpen.value, (isOpen) => {
  if (isOpen) {
    if (props.form.payments && props.form.payments.length > 0) {
       localPaymentForm.value = JSON.parse(JSON.stringify(props.form.payments[0]));
       // ensure the layout is array if it was stored otherwise in the unified model
       if (!Array.isArray(localPaymentForm.value.payments)) {
         localPaymentForm.value.payments = [{
              payment_method_fk_id: localPaymentForm.value.payment_method_fk_id || "", 
              amount: localPaymentForm.value.amount || 0
         }];
       }
    } else {
       localPaymentForm.value = {
         payments: [
           { payment_method_fk_id: "", amount: dueAmount.value }
         ],
         payment_date: new Date().toISOString().split("T")[0],
         notes: "",
       };
    }
  }
});

const currentTotalPaying = computed(() => {
  return localPaymentForm.value.payments.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
});

const addLocalPaymentRow = () => {
  const remainingDue = dueAmount.value - currentTotalPaying.value;
  localPaymentForm.value.payments.push({
    payment_method_fk_id: "",
    amount: Math.max(0, remainingDue)
  });
};

const removeLocalPaymentRow = (index) => {
  localPaymentForm.value.payments.splice(index, 1);
};

const confirmPayment = () => {
  const validPayments = localPaymentForm.value.payments.filter(
    p => p.payment_method_fk_id && parseFloat(p.amount) > 0
  );

  if (validPayments.length === 0) {
    alert("You must add at least one valid payment amount.");
    return;
  }

  // Clear existing to overwrite with the new nested structure correctly
  props.form.payments = [];
  
  // Format to match what the api component expects temporarily for unsaved case reports
  // Since we don't have sub-components for payments in useCaseReportApi exactly like InvoiceDetail,
  // we attach it directly.
  props.form.payments = validPayments.map(p => ({
     payment_method_fk_id: p.payment_method_fk_id,
     amount: p.amount,
     payment_date: localPaymentForm.value.payment_date,
     notes: localPaymentForm.value.notes
  }));

  // But we want to visually display it as well.
  props.form.payment_history = [];
  validPayments.forEach((p, idx) => {
    props.form.payment_history.push({
      id: "draft-" + idx,
      payment_method_fk_id: p.payment_method_fk_id,
      amount: p.amount,
      payment_date: localPaymentForm.value.payment_date,
      notes: localPaymentForm.value.notes
    });
  });

  // Calculate new paid amount locally right away for preview
  const newTotalPaid = validPayments.reduce((acc, curr) => acc + parseFloat(curr.amount), 0);
  props.form.invoice_paid_amount = props.totalPaid + newTotalPaid; // This might get overwritten by computed, but handles temporary state

  isPaymentDialogOpen.value = false;
};
</script>
