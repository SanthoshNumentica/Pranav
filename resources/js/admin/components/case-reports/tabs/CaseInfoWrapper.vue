<template>
    <div class="space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-500">
        <!-- Patient Details Section -->
        <section>
            <PatientDetailsTab :form="form" :patients="patients" :can-edit="canEdit" @new-patient="$emit('new-patient')"
                @next="void 0" />
        </section>

        <!-- Referer Details Section -->
        <section>
            <RefererDetailsTab :form="form" :referers="referers" :can-edit="canEdit" @new-referer="$emit('new-referer')"
                @next="void 0" />
        </section>

        <!-- Case Info Details Section -->
        <section>
            <CaseInfoTab :form="form" :fetching="fetching" :logged-in-user-is-super-admin="loggedInUserIsSuperAdmin"
                :filtered-branches="filteredBranches" :scan-types="scanTypes" :get-scans="getScans" :add-item="addItem"
                :remove-item="removeItem" :handle-drop="handleDrop" :handle-files="handleFiles"
                :remove-folder="removeFolder" :remove-doc="removeDoc" :get-unique-folders="getUniqueFolders"
                :processing="processing" :can-edit="canEdit" :is-last-tab="false" @next="void 0" @back="void 0" />
        </section>

        <!-- Invoice Details Section -->
        <section>
            <InvoiceDetailsTab :form="form" :processing="processing" :sub-total="subTotal" :total-amount="totalAmount"
                :total-paid="totalPaid" :discounts="discounts" :payment-methods="paymentMethods"
                :add-invoice-item="addInvoiceItem" :remove-invoice-item="removeInvoiceItem"
                :add-payment-row="addPaymentRow" :remove-payment-row="removePaymentRow" :can-edit="canEdit"
                @submit="(data) => $emit('submit', data)" @next="void 0" @back="void 0" />
        </section>
    </div>
</template>

<script setup>
import PatientDetailsTab from "./PatientDetailsTab.vue";
import RefererDetailsTab from "./RefererDetailsTab.vue";
import CaseInfoTab from "./CaseInfoTab.vue";
import InvoiceDetailsTab from "./InvoiceDetailsTab.vue";

const props = defineProps({
    form: { type: Object, required: true },
    patients: { type: Array, default: () => [] },
    referers: { type: Array, default: () => [] },
    fetching: { type: Boolean, default: false },
    loggedInUserIsSuperAdmin: { type: Boolean, default: false },
    filteredBranches: { type: Array, default: () => [] },
    scanTypes: { type: Array, default: () => [] },
    getScans: { type: Function, required: true },
    addItem: { type: Function, required: true },
    removeItem: { type: Function, required: true },
    handleDrop: { type: Function, required: true },
    handleFiles: { type: Function, required: true },
    removeFolder: { type: Function, required: true },
    removeDoc: { type: Function, required: true },
    getUniqueFolders: { type: Function, required: true },
    processing: { type: Boolean, default: false },
    canEdit: { type: Boolean, default: true },
    subTotal: { type: Number, default: 0 },
    totalAmount: { type: Number, default: 0 },
    discounts: { type: Array, default: () => [] },
    paymentMethods: { type: Array, default: () => [] },
    addInvoiceItem: { type: Function, required: true },
    removeInvoiceItem: { type: Function, required: true },
    totalPaid: { type: Number, default: 0 },
    addPaymentRow: { type: Function, required: true },
    removePaymentRow: { type: Function, required: true },
});

defineEmits(["new-patient", "new-referer", "submit"]);
</script>

<style scoped>
/* Ensure sections don't have conflicting bottom spacing if components have their own */
section {
    position: relative;
}

/* Ensure the first section (Patient Details) stays on top of subsequent sections for dropdown visibility */
section:first-child {
    z-index: 20;
}

section:nth-child(2) {
    z-index: 10;
}
</style>
