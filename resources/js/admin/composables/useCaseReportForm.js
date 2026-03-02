import { ref, reactive, watch, computed } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "./useToast";
import { useAuth } from "./useAuth";
import axios from "axios";
import { useCaseMasters } from "./useCaseMasters";
import { useCaseFileUpload } from "./useCaseFileUpload";

export function useCaseReportForm(isEdit = false) {
    const router = useRouter();
    const { addToast } = useToast();
    const { user: authUser } = useAuth();

    const loading = ref(false);
    const fetching = ref(true);
    const error = ref(null);

    // --- Master Data Composable ---
    const {
        patients,
        referers,
        scanTypes,
        branches,
        paymentMethods,
        discounts,
        fetchMasters: fetchMastersBase,
        fetchNextCaseId: fetchNextIdBase
    } = useCaseMasters();

    // --- File Handling Composable ---
    const {
        processingGeneral,
        uploadModal,
        getFileName,
        handleGeneralFiles: handleGeneralFilesBase,
        removeGeneralDoc: removeGeneralDocBase,
        removeDoc: removeDocBase,
        removeFolder: removeFolderBase,
        handleFiles: handleFilesBase,
        handleDrop: handleDropBase,
        startBatchedUpload: startBatchedUploadBase,
        getUniqueFolders: getUniqueFoldersBase
    } = useCaseFileUpload(addToast);

    // WhatsApp Modal State
    const isWhatsappModalOpen = ref(false);
    const reportForWhatsapp = ref(null);
    const sendingWhatsapp = ref(false);
    const initialRecipients = ref([]);

    const form = reactive({
        id: null,
        case_id: "",
        patient_fk_id: "",
        patient_name: "",
        patient_place: "",
        send_whatsapp_patient: true,
        send_whatsapp_referer: true,
        whatsapp_no_patient: "",
        whatsapp_no_referer: "",
        description: "",
        documents: [], // General documents
        items: [],
        branch_id: "",
        referer_id: "",
        referer_name: "",
        hospital_name: "",
        hospital_id: "",
        rct_date: (() => {
            const d = new Date();
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
        })(),
        rct_hour: new Date().toTimeString().slice(0, 5),
        is_stat: false,
        patient_type: "out_patient",

        // Invoice Details
        invoice_date: (() => {
            const d = new Date();
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
        })(),
        discount_amount: 0,
        discount_id: "custom",
        tax_amount: 0,
        notes: "",
        invoice_id: null,
        invoice_no: "",
        status: "pending",
        invoice_items: [],
    });

    const subTotal = computed(() => {
        return form.invoice_items.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
    });

    const totalAmount = computed(() => {
        const sub = parseFloat(subTotal.value) || 0;
        const disc = parseFloat(form.discount_amount) || 0;
        const tax = parseFloat(form.tax_amount) || 0;
        return (sub - disc) + tax;
    });

    const calculateDiscount = () => {
        if (!form.discount_id || form.discount_id === "custom" || !discounts.value.length) return;
        const d = discounts.value.find((item) => String(item.id) === String(form.discount_id));
        if (d && d.percentage > 0) {
            form.discount_amount = (subTotal.value * parseFloat(d.percentage)) / 100;
        }
    };

    watch(() => form.discount_id, calculateDiscount);
    watch(subTotal, calculateDiscount);

    // Wrapper methods to pass 'form' context
    const fetchNextCaseId = () => fetchNextIdBase(form);
    const handleGeneralFiles = (e) => handleGeneralFilesBase(e, form);
    const removeGeneralDoc = (idx) => removeGeneralDocBase(idx, form);
    const removeDoc = (iIdx, dIdx) => removeDocBase(iIdx, dIdx, form);
    const removeFolder = (idx, name) => removeFolderBase(idx, name, form);
    const handleFiles = (e, idx) => handleFilesBase(e, idx, form);
    const handleDrop = (e, idx) => handleDropBase(e, idx, form);
    const startBatchedUpload = () => startBatchedUploadBase(form);
    const getUniqueFolders = (docs, ret) => getUniqueFoldersBase(docs, ret);

    // Default item structure
    const createNewItem = () => ({
        custom_id: "",
        group_token: Math.random().toString(36).substring(2, 11),
        scan_type_id: "",
        scan_id: "", // This will be the "temp" selector value in the UI
        selected_scans: [], // { scan_id, scan_name, amount }
        documents: [],
        folderName: "",
        remarks: "",
        processing: false,
        amount: "",
    });

    // Initialize form with one item if not editing or empty
    if (!isEdit && form.items.length === 0) {
        form.items.push(createNewItem());
    }

    const fetchMasters = async () => {
        try {
            await fetchMastersBase();
            if (!isEdit) {
                if (authUser.value?.branch_id) {
                    form.branch_id = authUser.value.branch_id.toString();
                }
                if (!form.case_id) {
                    await fetchNextCaseId();
                }
            }
        } catch (err) {
            console.error("Failed to fetch master data", err);
            error.value = "Failed to load master data. Please refresh.";
        } finally {
            if (!isEdit) fetching.value = false;
        }
    };

    const fetchCaseReport = async (id) => {
        try {
            const response = await axios.get(`/api/v1/case-reports/${id}`);
            const data = response.data.data;

            form.id = data.id;
            form.case_id = data.case_id;

            // Inject current patient into list if it's not there
            if (data.patient && !patients.value.find(p => p.id === data.patient.id)) {
                patients.value.push(data.patient);
            }

            form.patient_fk_id = data.patient_fk_id?.toString() || "";
            form.patient_name = data.patient?.name || "";
            form.patient_place = data.patient?.place || "";

            // Inject current referer into list if it's not there
            if (data.referer && !referers.value.find(r => r.id === data.referer.id)) {
                referers.value.push(data.referer);
            }

            form.referer_id = data.referer_id?.toString() || "";
            form.referer_name = data.referer?.name || "";
            form.whatsapp_no_patient = data.whatsapp_no_patient || "";
            form.whatsapp_no_referer = data.whatsapp_no_referer || "";
            form.hospital_name = data.referer?.hospital_name || "";
            form.hospital_id = data.referer?.hospital_id || "";
            form.send_whatsapp_patient = data.send_whatsapp_patient ?? true;
            form.send_whatsapp_referer = data.send_whatsapp_referer ?? true;
            form.description = data.description || "";
            form.branch_id = data.branch_id?.toString() || "";
            form.case_id = data.case_id || "";
            form.rct_date = data.rct_date ? data.rct_date.split('T')[0] : "";
            form.rct_hour = data.rct_hour || "";
            form.is_stat = data.is_stat ?? false;
            form.patient_type = data.patient_type || "out_patient";

            if (data.invoice) {
                form.invoice_id = data.invoice.id;
                form.invoice_no = data.invoice.invoice_no || "";
                form.status = data.invoice.status || "pending";
                form.discount_amount = data.invoice.discount_amount || 0;
                form.tax_amount = data.invoice.tax_amount || 0;
                form.invoice_date = data.invoice.invoice_date ? data.invoice.invoice_date.split('T')[0] : "";
                form.notes = data.invoice.notes || "";
                form.invoice_items = (data.invoice.items || []).map(item => ({
                    id: item.id,
                    case_report_item_id: item.case_report_item_id,
                    description: item.description,
                    quantity: item.quantity || 1,
                    unit_price: item.unit_price,
                    amount: item.amount
                }));
            }

            form.documents = (data.documents || []).map((path) => ({
                name: typeof path === 'string' ? path.split("/").pop() : path.name,
                path: typeof path === 'string' ? path : path.path
            }));

            // Group items by group_token or custom_id (fallback for legacy)
            const groupedItems = [];

            // To handle records with NULL group_token AND NULL custom_id, we'll try to group by scan_type_id 
            // if they belong to the same sequence of nulls
            let lastNullGroupToken = null;
            let lastNullScanTypeId = null;

            (data.items || []).forEach(item => {
                const scanData = item.scan_id ? {
                    id: item.id,
                    scan_id: item.scan_id.toString(),
                    scan_name: item.scan?.name || "Scan",
                    amount: item.amount || 0,
                } : null;

                // Stronger grouping logic
                let existingGroup = groupedItems.find(g => {
                    if (item.group_token && g.group_token === item.group_token) return true;
                    if (item.custom_id && g.custom_id === item.custom_id && g.scan_type_id === item.scan_type_id?.toString()) return true;
                    return false;
                });

                // Emergency fallback: If both are null, check if it's the same scan type as the previous item 
                // and assign them to the same emergency group token
                if (!existingGroup && !item.group_token && !item.custom_id) {
                    if (item.scan_type_id?.toString() === lastNullScanTypeId) {
                        existingGroup = groupedItems.find(g => g.group_token === lastNullGroupToken);
                    }
                }

                if (existingGroup) {
                    if (scanData) {
                        const isDuplicate = existingGroup.selected_scans.some(s => s.scan_id.toString() === item.scan_id?.toString());
                        if (!isDuplicate) {
                            existingGroup.selected_scans.push(scanData);
                        }
                    }
                } else {
                    const newToken = item.group_token || Math.random().toString(36).substring(2, 11);
                    if (!item.group_token && !item.custom_id) {
                        lastNullGroupToken = newToken;
                        lastNullScanTypeId = item.scan_type_id?.toString();
                    }

                    groupedItems.push({
                        id: item.id,
                        group_token: newToken,
                        custom_id: item.custom_id || "",
                        scan_type_id: item.scan_type_id?.toString() || "",
                        scan_id: item.scan_id?.toString() || "",
                        selected_scans: scanData ? [scanData] : [],
                        documents: (item.documents || []).map((path) => ({
                            name: typeof path === 'string' ? path.split("/").pop() : path.name,
                            path: typeof path === 'string' ? path : path.path
                        })),
                        remarks: item.remarks || "",
                        processing: false,
                        amount: item.amount || 0,
                    });
                }
            });
            form.items = groupedItems;

            // Sync invoice items initially
            if (!data.invoice || !data.invoice.items || data.invoice.items.length === 0) {
                form.invoice_items = [];
                groupedItems.forEach(block => {
                    (block.selected_scans || []).forEach(scan => {
                        // Check for duplicates before pushing
                        const exists = form.invoice_items.some(inv =>
                            (inv.case_report_item_id && String(inv.case_report_item_id) === String(scan.id)) ||
                            (inv.description === scan.scan_name && inv.amount === scan.amount)
                        );

                        if (!exists) {
                            form.invoice_items.push({
                                case_report_item_id: scan.id || null,
                                description: scan.scan_name || "Scan",
                                quantity: 1,
                                unit_price: scan.amount || 0,
                                amount: scan.amount || 0
                            });
                        }
                    });
                });
            } else {
                // If invoice exists with items, use those
                form.invoice_items = (data.invoice.items || []).map(item => ({
                    id: item.id,
                    case_report_item_id: item.case_report_item_id,
                    description: item.description,
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                    amount: item.amount
                }));
            }

        } catch (err) {
            console.error("Failed to fetch case report", err);
            error.value = "Failed to load case data. Please refresh.";
        } finally {
            fetching.value = false;
        }
    };

    // --- Watchers ---
    watch(() => form.patient_fk_id, (newVal) => {
        if (!newVal || fetching.value) return;
        const list = patients.value;
        const p = list.find((p) => String(p.id) === String(newVal));
        if (p) {
            form.patient_name = p.name || "";
            form.patient_place = p.place || "";
            form.whatsapp_no_patient = p.whatsapp_no || p.mobile_no || "";
        }
    });

    watch(() => form.referer_id, (newVal) => {
        if (!newVal || fetching.value) return;
        const list = referers.value;
        const r = list.find((d) => String(d.id) === String(newVal));
        if (r) {
            form.referer_name = r.name || "";
            form.whatsapp_no_referer = r.mobile_no || "";
            form.hospital_name = r.hospital_name || "";
            form.hospital_id = r.hospital_id || "";
        }
    });

    // --- Core Methods ---
    const getScans = (typeId) => {
        if (!typeId) return [];
        const type = scanTypes.value.find((t) => t.id == typeId);
        return type ? type.scans : [];
    };

    const addItem = () => form.items.push(createNewItem());
    const removeItem = (index) => form.items.splice(index, 1);

    const addInvoiceItem = (item) => {
        const safeItem = item || {};

        // If it's a grouped scan block with multiple selections
        if (safeItem.selected_scans && safeItem.selected_scans.length > 0) {
            safeItem.selected_scans.forEach((scan) => {
                // Duplicate check
                const exists = form.invoice_items.some(inv =>
                    (inv.case_report_item_id && String(inv.case_report_item_id) === String(scan.id)) ||
                    (inv.description === scan.scan_name && inv.amount === scan.amount)
                );

                if (!exists) {
                    form.invoice_items.push({
                        case_report_item_id: scan.id || null,
                        description: scan.scan_name || "Scan",
                        quantity: 1,
                        unit_price: scan.amount || 0,
                        amount: scan.amount || 0,
                    });
                }
            });
            return;
        }

        // Fallback for manual addition or single scan records
        const exists = form.invoice_items.some(inv =>
            (inv.case_report_item_id && String(inv.case_report_item_id) === String(safeItem.id)) ||
            (inv.description === safeItem.scan_name && inv.amount === safeItem.amount)
        );

        if (!exists) {
            form.invoice_items.push({
                case_report_item_id: safeItem.id || null,
                description: safeItem.scan_name || (safeItem.scan_id ? "Scan" : ""),
                quantity: 1,
                unit_price: safeItem.amount || 0,
                amount: safeItem.amount || 0,
            });
        }
    };

    const removeInvoiceItem = (index) => form.invoice_items.splice(index, 1);

    const handleSubmit = async () => {
        loading.value = true;
        error.value = null;

        const validationErrors = [];
        if (!form.patient_fk_id) validationErrors.push("Patient selection is required.");
        if (!form.referer_id) validationErrors.push("Referer selection is required.");

        form.items.forEach((item, idx) => {
            if (!item.scan_type_id) validationErrors.push(`Scan Item #${idx + 1}: Scan Type is required.`);
            if (!item.selected_scans || item.selected_scans.length === 0) {
                validationErrors.push(`Scan Item #${idx + 1}: At least one specific scan must be added.`);
            }
        });

        if (!isEdit && !form.case_id) {
            await fetchNextCaseId();
            if (!form.case_id) validationErrors.push("System failed to generate SRF No.");
        }

        if (validationErrors.length > 0) {
            addToast({ title: "Validation Required", description: validationErrors[0], variant: "danger" });
            loading.value = false;
            return;
        }

        try {
            const payload = {
                case_id: form.case_id,
                patient_fk_id: form.patient_fk_id,
                patient_name: form.patient_name,
                patient_place: form.patient_place,
                whatsapp_no_patient: form.whatsapp_no_patient,
                referer_id: form.referer_id,
                referer_name: form.referer_name,
                whatsapp_no_referer: form.whatsapp_no_referer,
                hospital_name: form.hospital_name,
                hospital_id: form.hospital_id,
                description: form.description || "",
                branch_id: form.branch_id || "",
                rct_date: form.rct_date || null,
                rct_hour: form.rct_hour || "",
                is_stat: form.is_stat ?? false,
                patient_type: form.patient_type || "out_patient",
                send_whatsapp_patient: form.send_whatsapp_patient ?? true,
                send_whatsapp_referer: form.send_whatsapp_referer ?? true,
                documents: (form.documents || []).map((d) => d.path || d),
                items: (form.items || []).flatMap((itemBlock) => {
                    // Calculate total for this block
                    const blockTotal = (itemBlock.selected_scans || []).reduce((sum, s) => {
                        return sum + (parseFloat(s.amount) || 0);
                    }, 0);

                    // Flatten multi-scans into individual case report items
                    return (itemBlock.selected_scans || []).map(scan => ({
                        custom_id: itemBlock.custom_id,
                        group_token: itemBlock.group_token,
                        scan_type_id: itemBlock.scan_type_id,
                        scan_id: scan.scan_id,
                        documents: (itemBlock.documents || []).map((d) => d.path || d),
                        remarks: itemBlock.remarks || "",
                        amount: scan.amount || 0,
                        total_amount: blockTotal,
                    }));
                }),
                // Invoice fields
                invoice_date: form.invoice_date || null,
                discount_amount: form.discount_amount || 0,
                discount_id: form.discount_id || null,
                tax_amount: form.tax_amount || 0,
                notes: form.notes || "",
                invoice_items: (form.invoice_items || []).map((item) => ({
                    case_report_item_id: item.case_report_item_id || null,
                    description: item.description,
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                    amount: item.amount,
                })),
            };

            let response;
            if (isEdit) {
                response = await axios.put(`/api/v1/case-reports/${form.id}`, payload);
            } else {
                if (!payload.branch_id && authUser.value?.branch_id) {
                    payload.branch_id = authUser.value.branch_id;
                }
                response = await axios.post("/api/v1/case-reports", payload);
            }

            if (response.data.success) {
                addToast({ title: "Success", description: isEdit ? "Case report updated successfully." : "Case report created successfully.", variant: "success" });

                // Refresh form data from response to show generated invoice details instantly
                const updatedData = response.data.data;
                if (updatedData.invoice) {
                    form.invoice_id = updatedData.invoice.id;
                    form.invoice_no = updatedData.invoice.invoice_no || "";
                    form.status = updatedData.invoice.status || "pending";
                    form.invoice_date = updatedData.invoice.invoice_date ? updatedData.invoice.invoice_date.split('T')[0] : "";
                    form.notes = updatedData.invoice.notes || "";
                    form.invoice_items = (updatedData.invoice.items || []).map(item => ({
                        id: item.id,
                        case_report_item_id: item.case_report_item_id,
                        description: item.description,
                        quantity: item.quantity || 1,
                        unit_price: item.unit_price,
                        amount: item.amount
                    }));
                }

                router.push("/case-reports");
            }
        } catch (err) {
            console.error("Save failed", err);
            error.value = err.response?.data?.message || "Failed to save.";
            addToast({ title: "Error", description: error.value, variant: "danger" });
        } finally {
            loading.value = false;
        }
    };

    const handleSendWhatsApp = async (recipients) => {
        if (!reportForWhatsapp.value) return;
        sendingWhatsapp.value = true;
        try {
            const response = await axios.post(`/api/v1/case-reports/${reportForWhatsapp.value.id}/whatsapp`, { recipients });
            if (response.data.success) {
                addToast({ title: "Success", description: "Sent", variant: "success" });
                handleModalClose();
            }
        } catch (err) {
            console.error("WhatsApp failed", err);
        } finally {
            sendingWhatsapp.value = false;
        }
    };

    const handleModalClose = () => {
        isWhatsappModalOpen.value = false;
        router.push("/case-reports");
    };

    const loggedInUserIsSuperAdmin = computed(() => authUser.value?.role?.name.toLowerCase() === "super-admin");

    const filteredBranches = computed(() => {
        if (loggedInUserIsSuperAdmin.value) return branches.value;
        if (!authUser.value?.branch_id) return [];
        return branches.value.filter((b) => b.id.toString() === authUser.value.branch_id.toString());
    });

    return {
        loading, fetching, error, form, patients, referers, scanTypes,
        processingGeneral, uploadModal, isWhatsappModalOpen, reportForWhatsapp,
        sendingWhatsapp, initialRecipients, branches, paymentMethods, discounts,
        loggedInUserIsSuperAdmin, filteredBranches, subTotal, totalAmount,
        fetchMasters, fetchCaseReport, getScans, getFileName, addItem, removeItem,
        handleGeneralFiles, removeGeneralDoc, handleDrop, handleFiles,
        startBatchedUpload, removeFolder, removeDoc, handleSubmit,
        handleSendWhatsApp, handleModalClose, addInvoiceItem, removeInvoiceItem,
        getUniqueFolders,
    };
}
