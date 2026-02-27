<template>
  <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Column: Case Scans List -->
      <div class="lg:col-span-1 space-y-6">
        <div
          class="bg-white rounded-[32px] border border-slate-200 p-6 shadow-soft-xl"
        >
          <div class="flex items-center gap-2 mb-2">
            <h4
              class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2"
            >
              <div class="h-1 w-1 rounded-full bg-primary"></div>
              Case Scans
            </h4>
          </div>

          <div class="space-y-4">
            <div
              v-for="(item, index) in form.items"
              :key="index"
              class="p-4 rounded-2xl border border-slate-100 bg-slate-50/50 space-y-3"
            >
              <div class="flex justify-between items-start">
                <div>
                  <p
                    class="text-[10px] font-black uppercase tracking-wider text-slate-400"
                  >
                    Scan Item #{{ index + 1 }}
                  </p>
                  <p class="font-bold text-slate-700">
                    {{ item.scan_name || "Generic Scan" }}
                  </p>
                </div>
                <p class="font-black text-primary">
                  ₹{{ parseFloat(item.amount || 0).toFixed(2) }}
                </p>
              </div>

              <button
                v-if="canEdit"
                type="button"
                @click="addInvoiceItem(item)"
                class="w-full py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-xs font-bold hover:bg-primary hover:text-white hover:border-primary transition-all flex items-center justify-center gap-2 group"
              >
                <PlusIcon
                  class="h-3.5 w-3.5 group-hover:scale-110 transition-transform"
                />
                Add to Invoice
              </button>
            </div>

            <div v-if="form.items.length === 0" class="text-center py-8">
              <p class="text-slate-400 text-sm font-medium">
                No scans added to this case yet.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Invoice Details & Preview -->
      <div class="lg:col-span-2 space-y-6">
        <div
          class="bg-white rounded-[32px] border border-slate-200 p-8 shadow-soft-xl space-y-8"
        >
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
              <h4
                class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary flex items-center gap-2"
              >
                <div class="h-1 w-1 rounded-full bg-primary"></div>
                Invoice Details
              </h4>
            </div>

            <div class="flex items-center gap-3">
              <button
                v-if="canEdit"
                type="button"
                @click="addInvoiceItem()"
                class="px-3 py-1.5 rounded-lg bg-primary/5 text-primary text-[10px] font-bold uppercase tracking-wider hover:bg-primary hover:text-white transition-all active:scale-95 flex items-center gap-2 shadow-sm border border-primary/10"
              >
                <PlusIcon class="h-3 w-3" />
                Add Manual Item
              </button>

              <div
                class="flex items-center gap-3 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-100 shadow-sm"
                v-if="form.invoice_id"
              >
                <div class="flex flex-col">
                  <span
                    class="text-[9px] font-black uppercase tracking-wider text-slate-400 leading-none mb-0.5"
                    >Invoice No</span
                  >
                  <span class="text-xs font-bold text-slate-700 leading-none">{{
                    form.invoice_no
                  }}</span>
                </div>
                <div class="w-px h-6 bg-slate-200"></div>
                <div
                  :class="[
                    'px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-tight flex items-center gap-1',
                    form.status === 'paid'
                      ? 'bg-emerald-50 text-emerald-600'
                      : 'bg-amber-50 text-amber-600',
                  ]"
                >
                  <div
                    :class="[
                      'h-1.5 w-1.5 rounded-full',
                      form.status === 'paid'
                        ? 'bg-emerald-500'
                        : 'bg-amber-500 animate-pulse',
                    ]"
                  ></div>
                  {{ form.status }}
                </div>
              </div>
            </div>
          </div>

          <!-- Manual Invoice Items Table -->
          <div class="overflow-hidden border border-slate-100 rounded-2xl">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50">
                  <th
                    class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-12 text-center"
                  >
                    #
                  </th>
                  <th
                    class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider"
                  >
                    Description
                  </th>
                  <th
                    class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-32 border-l border-slate-100"
                  >
                    Amount
                  </th>
                  <th
                    class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-12 text-center"
                  ></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr
                  v-for="(item, idx) in form.invoice_items"
                  :key="idx"
                  class="hover:bg-slate-50/50 transition-colors"
                >
                  <td
                    class="px-4 py-4 text-sm font-bold text-slate-400 text-center"
                  >
                    {{ idx + 1 }}
                  </td>
                  <td class="px-4 py-2">
                    <input
                      v-model="item.description"
                      placeholder="Item description"
                      :disabled="!canEdit"
                      class="w-full bg-transparent border-none p-0 focus:ring-0 font-medium text-slate-700 text-sm placeholder:text-slate-300 disabled:opacity-70 disabled:cursor-not-allowed"
                    />
                  </td>
                  <td class="px-4 py-2 border-l border-slate-50">
                    <div class="flex items-center gap-1">
                      <span class="text-slate-400 font-bold text-sm">₹</span>
                      <input
                        v-model.number="item.amount"
                        type="number"
                        step="0.01"
                        :disabled="!canEdit"
                        class="w-full bg-transparent border-none p-0 focus:ring-0 font-bold text-slate-900 text-sm disabled:opacity-70 disabled:cursor-not-allowed"
                      />
                    </div>
                  </td>
                  <td class="px-4 py-4 text-center">
                    <button
                      v-if="canEdit"
                      type="button"
                      @click="removeInvoiceItem(idx)"
                      class="p-1.5 hover:bg-rose-50 text-slate-300 hover:text-rose-500 rounded-lg transition-all"
                    >
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
                      <p
                        class="text-[10px] text-slate-400 uppercase tracking-wider"
                      >
                        Click "Add to Invoice" from the scans list
                      </p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Invoice Date -->
            <div class="space-y-2">
              <label
                class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1"
                >Invoice Date</label
              >
              <div class="relative group">
                <div
                  class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"
                >
                  <CalendarIcon class="h-4 w-4" />
                </div>
                <input
                  v-model="form.invoice_date"
                  type="date"
                  :disabled="!canEdit"
                  class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-slate-900 text-sm disabled:opacity-70 disabled:cursor-not-allowed"
                />
              </div>
            </div>

            <!-- Notes -->
            <div class="space-y-2">
              <label
                class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1"
                >Notes</label
              >
              <input
                v-model="form.notes"
                placeholder="Internal notes..."
                :disabled="!canEdit"
                class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-slate-900 placeholder:text-slate-400 text-sm disabled:opacity-70 disabled:cursor-not-allowed"
              />
            </div>

            <!-- Discount & Tax in a smaller grid -->
            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="space-y-2">
                <label
                  class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1"
                  >Discount Type</label
                >
                <div class="relative group">
                  <div
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"
                  >
                    <TagIcon class="h-4 w-4" />
                  </div>
                  <Select v-model="form.discount_id" :disabled="!canEdit">
                    <SelectTrigger
                      class="pl-11 h-12 rounded-xl bg-slate-50 border-none font-bold text-slate-900 text-sm disabled:opacity-70 disabled:cursor-not-allowed"
                    >
                      <SelectValue placeholder="Custom Discount" />
                    </SelectTrigger>
                    <SelectContent class="rounded-xl border-slate-100">
                      <SelectItem value="custom">Custom Discount</SelectItem>
                      <SelectItem
                        v-for="d in discounts"
                        :key="d.id"
                        :value="d.id.toString()"
                      >
                        {{ d.name }} ({{ d.percentage }}%)
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div class="space-y-2">
                <label
                  class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1"
                  >Discount amount</label
                >
                <div class="relative group">
                  <div
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"
                  >
                    <span class="text-xs font-bold pl-1">₹</span>
                  </div>
                  <input
                    v-model.number="form.discount_amount"
                    type="number"
                    step="0.01"
                    placeholder="0.00"
                    :disabled="!canEdit"
                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-slate-900 text-sm disabled:opacity-70 disabled:cursor-not-allowed"
                  />
                </div>
              </div>

              <div class="space-y-2">
                <label
                  class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1"
                  >Tax amount</label
                >
                <div class="relative group">
                  <div
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"
                  >
                    <span class="text-xs font-bold pl-1">₹</span>
                  </div>
                  <input
                    v-model.number="form.tax_amount"
                    type="number"
                    step="0.01"
                    placeholder="0.00"
                    :disabled="!canEdit"
                    class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-slate-900 text-sm disabled:opacity-70 disabled:cursor-not-allowed"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Summary Section -->
          <div class="bg-slate-50 rounded-3xl p-6 space-y-3">
            <div class="flex justify-between items-center text-slate-500">
              <span class="text-xs font-bold uppercase tracking-wider"
                >Subtotal</span
              >
              <span class="font-bold text-sm">₹{{ subTotal.toFixed(2) }}</span>
            </div>
            <div
              class="flex justify-between items-center text-rose-500"
              v-if="form.discount_amount > 0"
            >
              <span class="text-xs font-bold uppercase tracking-wider"
                >Discount</span
              >
              <span class="font-bold text-sm"
                >- ₹{{ parseFloat(form.discount_amount).toFixed(2) }}</span
              >
            </div>
            <div
              class="flex justify-between items-center text-slate-500"
              v-if="form.tax_amount > 0"
            >
              <span class="text-xs font-bold uppercase tracking-wider"
                >Tax</span
              >
              <span class="font-bold text-sm"
                >+ ₹{{ parseFloat(form.tax_amount).toFixed(2) }}</span
              >
            </div>
            <div
              class="pt-4 border-t border-slate-200 flex justify-between items-center text-slate-900"
            >
              <span
                class="text-xs font-black uppercase tracking-widest text-slate-400"
                >Grand Total</span
              >
              <span class="text-2xl font-black text-primary"
                >₹{{ totalAmount.toFixed(2) }}</span
              >
            </div>
          </div>

          <div class="flex justify-between gap-4 pt-4 border-t border-slate-50">
            <button
              type="button"
              @click="$emit('back')"
              class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition-all active:scale-95"
            >
              Back
            </button>
            <div class="flex items-center gap-3">
              <button
                v-if="form.invoice_id"
                type="button"
                @click="handlePrint"
                class="px-6 py-2.5 rounded-xl border border-primary/20 text-primary font-bold text-sm hover:bg-primary/5 transition-all active:scale-95 flex items-center gap-2"
              >
                <PrinterIcon class="h-4 w-4" />
                Print Invoice
              </button>

              <button
                type="button"
                @click="$emit('submit')"
                :disabled="processing || !canEdit"
                class="group flex items-center gap-2 px-8 py-2.5 bg-primary text-white rounded-xl font-bold text-sm shadow-lg shadow-primary/20 hover:opacity-90 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <Loader2Icon v-if="processing" class="h-4 w-4 animate-spin" />
                {{ form.invoice_id ? "Update Invoice" : "Generate Invoice" }}
                <ArrowRightIcon
                  v-if="!processing"
                  class="h-4 w-4 group-hover:translate-x-1 transition-transform"
                />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  Receipt as ReceiptIcon,
  Calendar as CalendarIcon,
  Tag as TagIcon,
  Percent as PercentIcon,
  Plus as PlusIcon,
  Trash2 as TrashIcon,
  Info as InfoIcon,
  ArrowRight as ArrowRightIcon,
  Loader2 as Loader2Icon,
  Printer as PrinterIcon,
  CheckCircle2 as CheckCircleIcon,
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
  canEdit: {
    type: Boolean,
    default: true,
  },
});

defineEmits(["next", "back", "submit"]);
const handlePrint = () => {
  if (props.form.invoice_id) {
    window.open(`/api/v1/print/invoice/${props.form.invoice_id}`, "_blank");
  }
};
</script>
