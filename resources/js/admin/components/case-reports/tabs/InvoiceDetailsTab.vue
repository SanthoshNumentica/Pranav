<template>
  <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div
      class="bg-white rounded-[32px] border border-slate-200 p-8 shadow-soft-xl space-y-8"
    >
      <div class="flex items-center gap-3 text-slate-900 mb-2">
        <div
          class="h-10 w-10 rounded-2xl bg-primary/10 flex items-center justify-center"
        >
          <ReceiptIcon class="h-5 w-5 text-primary" />
        </div>
        <h3 class="font-bold text-lg">Invoice Details</h3>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Invoice Date -->
        <div class="space-y-2">
          <label class="text-sm font-bold text-slate-700 ml-1"
            >Invoice Date</label
          >
          <div class="relative group">
            <div
              class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"
            >
              <CalendarIcon class="h-5 w-5" />
            </div>
            <input
              v-model="form.invoice_date"
              type="date"
              class="w-full pl-12 pr-4 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-primary/20 transition-all font-medium text-slate-900 placeholder:text-slate-400"
            />
          </div>
        </div>

        <!-- Discount Amount -->
        <div class="space-y-2">
          <label class="text-sm font-bold text-slate-700 ml-1"
            >Discount Amount</label
          >
          <div class="relative group">
            <div
              class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"
            >
              <TagIcon class="h-5 w-5" />
            </div>
            <input
              v-model="form.discount_amount"
              type="number"
              step="0.01"
              placeholder="0.00"
              class="w-full pl-12 pr-4 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-primary/20 transition-all font-medium text-slate-900 placeholder:text-slate-400"
            />
          </div>
        </div>

        <!-- Tax Amount -->
        <div class="space-y-2">
          <label class="text-sm font-bold text-slate-700 ml-1"
            >Tax Amount</label
          >
          <div class="relative group">
            <div
              class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"
            >
              <PercentIcon class="h-5 w-5" />
            </div>
            <input
              v-model="form.tax_amount"
              type="number"
              step="0.01"
              placeholder="0.00"
              class="w-full pl-12 pr-4 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-primary/20 transition-all font-medium text-slate-900 placeholder:text-slate-400"
            />
          </div>
        </div>

        <!-- Notes -->
        <div class="space-y-2 md:col-span-2">
          <label class="text-sm font-bold text-slate-700 ml-1">Notes</label>
          <textarea
            v-model="form.notes"
            rows="3"
            placeholder="Add any additional notes here..."
            class="w-full px-4 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-primary/20 transition-all font-medium text-slate-900 placeholder:text-slate-400 resize-none"
          ></textarea>
        </div>
      </div>

      <!-- Summary Section -->
      <div class="bg-slate-50 rounded-3xl p-6 space-y-4">
        <div class="flex justify-between items-center text-slate-600">
          <span class="font-medium">Subtotal</span>
          <span class="font-bold">₹{{ subTotal.toFixed(2) }}</span>
        </div>
        <div
          class="flex justify-between items-center text-rose-500"
          v-if="form.discount_amount > 0"
        >
          <span class="font-medium">Discount</span>
          <span class="font-bold"
            >- ₹{{ parseFloat(form.discount_amount).toFixed(2) }}</span
          >
        </div>
        <div
          class="flex justify-between items-center text-slate-600"
          v-if="form.tax_amount > 0"
        >
          <span class="font-medium">Tax</span>
          <span class="font-bold"
            >+ ₹{{ parseFloat(form.tax_amount).toFixed(2) }}</span
          >
        </div>
        <div
          class="pt-4 border-t border-slate-200 flex justify-between items-center text-slate-900"
        >
          <span class="text-lg font-bold">Total Amount</span>
          <span class="text-2xl font-black text-primary"
            >₹{{ totalAmount.toFixed(2) }}</span
          >
        </div>
      </div>

      <div class="flex justify-between gap-4 pt-4">
        <button
          type="button"
          @click="$emit('back')"
          class="flex-1 px-8 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold text-sm hover:bg-slate-200 transition-all active:scale-[0.98]"
        >
          Back
        </button>
        <button
          type="button"
          @click="$emit('next')"
          class="flex-1 px-8 py-4 bg-primary text-white rounded-2xl font-bold text-sm hover:bg-primary-dark shadow-lg shadow-primary/20 transition-all active:scale-[0.98]"
        >
          Next: Review
        </button>
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
} from "lucide-vue-next";

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
  subTotal: {
    type: Number,
    default: 0,
  },
  totalAmount: {
    type: Number,
    default: 0,
  },
});

defineEmits(["next", "back"]);
</script>
