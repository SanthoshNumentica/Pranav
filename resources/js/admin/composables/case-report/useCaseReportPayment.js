import { computed, watch } from "vue";

export function useCaseReportPayment(form, totalAmount) {
    const totalPaid = computed(() => {
        const historyTotal = (form.payment_history || []).reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
        const newTotal = (form.payments || []).reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
        return historyTotal + newTotal;
    });

    const addPaymentRow = () => {
        const d = new Date();
        const today = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
        form.payments.push({
            payment_id: "",
            payment_method_fk_id: "",
            amount: 0,
            payment_date: today,
            notes: ""
        });
    };

    const removePaymentRow = (index) => {
        if (form.payments.length > 1) {
            form.payments.splice(index, 1);
        }
    };

    const setupPaymentWatchers = () => {
        // No auto-sync watchers are required as payments are generated via modal
    };

    return {
        totalPaid,
        addPaymentRow,
        removePaymentRow,
        setupPaymentWatchers
    };
}
