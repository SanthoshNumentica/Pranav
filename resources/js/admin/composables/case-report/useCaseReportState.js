import { ref, reactive, computed } from "vue";

export function useCaseReportState(isEdit = false, authUser, branches) {
    const loading = ref(false);
    const fetching = ref(true);
    const error = ref(null);

    // WhatsApp Modal State
    const isWhatsappModalOpen = ref(false);
    const reportForWhatsapp = ref(null);
    const sendingWhatsapp = ref(false);
    const initialRecipients = ref([]);

    const form = reactive({
        id: null,
        case_id: "",
        patient_fk_id: "",
        title_fk_id: "",
        patient_name: "",
        patient_place: "",
        gender_fk_id: "",
        age: "",
        send_whatsapp_patient: true,
        send_whatsapp_referer: true,
        whatsapp_no_patient: "",
        whatsapp_no_referer: "",
        description: "",
        documents: [], // General documents
        items: [],
        branch_id: "",
        referer_fk_id: "",
        title_id: "",
        referer_type_id: "",
        referer_name: "",
        hospital_name: "",
        hospital_id: "",
        scanning_date: (() => {
            const d = new Date();
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
        })(),
        check_in: new Date().toTimeString().slice(0, 5),
        is_stat_case: false,

        // Invoice Details - initialized as null for new cases
        invoice_date: isEdit ? (() => {
            const d = new Date();
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
        })() : null,
        discount_amount: 0,
        discount_fk_id: "custom",
        tax_amount: 0,
        notes: "",
        invoice_id: null,
        invoice_no: "",
        status: "pending",
        invoice_items: [],

        // Multi-Row Payment Details
        payment_id: "", // Auto-generated ID for the payment section
        invoice_total_amount: 0,
        invoice_paid_amount: 0,
        // List of previously saved (read-only) payments
        payment_history: [],
        payments: []
    });

    const deletedInvoiceItemIds = ref([]);
    const deletedCaseReportItemIds = ref([]);

    const loggedInUserIsSuperAdmin = computed(() => authUser.value?.role?.name.toLowerCase() === "super-admin");

    const filteredBranches = computed(() => {
        if (loggedInUserIsSuperAdmin.value) return branches.value;
        if (!authUser.value?.branch_id) return [];
        return branches.value.filter((b) => b.id.toString() === authUser.value.branch_id.toString());
    });

    return {
        loading,
        fetching,
        error,
        isWhatsappModalOpen,
        reportForWhatsapp,
        sendingWhatsapp,
        initialRecipients,
        form,
        deletedInvoiceItemIds,
        deletedCaseReportItemIds,
        loggedInUserIsSuperAdmin,
        filteredBranches
    };
}
