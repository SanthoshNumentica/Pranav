<template>
    <div class="max-w-5xl mx-auto space-y-8 pb-20">
        <!-- Page Header -->
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Edit Invoice
                </h1>
                <p class="text-sm text-slate-500 mt-1" v-if="form.invoice_no">
                    Invoice: {{ form.invoice_no }} (Case: {{ form.case_id }})
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="$router.push('/invoices')"
                    class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-50 transition-all active:scale-95 flex items-center gap-2">
                    Back to Invoices
                </button>
            </div>
        </div>

        <!-- Skeleton Loading State -->
        <SkeletonCaseReportLoader v-if="isInitialLoading" />

        <!-- Main Content -->
        <div v-else class="animate-in fade-in duration-500">
            <div class="bg-white rounded-[32px] border border-slate-200 p-1 shadow-soft-xl mb-6">
                <div class="flex items-center gap-4 px-6 py-4">
                    <div class="h-10 w-10 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                        <ReceiptIcon class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900">Invoice Details</h3>
                        <p class="text-xs text-slate-500">Update items, discounts and billing info</p>
                    </div>
                </div>
            </div>

            <InvoiceDetailsTab :form="form" :processing="loading" :sub-total="subTotal" :total-amount="totalAmount"
                :discounts="discounts" :add-invoice-item="addInvoiceItem" :remove-invoice-item="removeInvoiceItem"
                :can-edit="hasPermission('case-report-invoice', 'edit')" @submit="handleSubmit"
                @back="$router.push('/invoices')" />

            <!-- Error Display (global) -->
            <div v-if="error"
                class="mt-4 text-xs text-rose-500 font-bold px-4 flex items-center gap-2 animate-in fade-in">
                <AlertCircleIcon class="h-3.5 w-3.5" />
                {{ error }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { usePermissions } from "../../composables/usePermissions";
import { useCaseReportForm } from "../../composables/useCaseReportForm";
import InvoiceDetailsTab from "../../components/case-reports/tabs/InvoiceDetailsTab.vue";
import SkeletonCaseReportLoader from "../../components/loaders/SkeletonCaseReportLoader.vue";
import {
    Receipt as ReceiptIcon,
    AlertCircle as AlertCircleIcon
} from "lucide-vue-next";

const route = useRoute();
const router = useRouter();
const { hasPermission } = usePermissions();

// We reuse the same form logic as Case Report Edit
const {
    loading,
    error,
    form,
    discounts,
    fetchMasters,
    fetchCaseReport,
    handleSubmit,
    subTotal,
    totalAmount,
    addInvoiceItem,
    removeInvoiceItem,
} = useCaseReportForm(true);

const isInitialLoading = ref(true);

onMounted(async () => {
    try {
        // Here we need the Case Report ID to load the invoice data
        // The invoice list should pass the case_report_id or we need to find it by invoice_id
        // For now, assuming we are passing case_report_id in the redirection
        const caseReportId = route.params.id;

        await Promise.all([
            fetchMasters(),
            fetchCaseReport(caseReportId)
        ]);

    } catch (e) {
        console.error("Failed to load invoice data", e);
    } finally {
        isInitialLoading.value = false;
    }
});
</script>
