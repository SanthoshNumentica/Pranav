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

        // Invoice Details - initialized as null for new cases
        invoice_date: isEdit ? (() => {
            const d = new Date();
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
        })() : null,
        discount_amount: 0,
        discount_id: "custom",
        tax_amount: 0,
        notes: "",
        invoice_id: null,
        invoice_no: "",
        status: "pending",
        invoice_items: [],
    });

    const deletedInvoiceItemIds = ref([]);
    const deletedCaseReportItemIds = ref([]);

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
    const handleGeneralFiles = (e) => handleGeneralFilesBase(e, form, form.id);
    const removeGeneralDoc = (idx) => removeGeneralDocBase(idx, form);
    const removeDoc = (iIdx, dIdx) => removeDocBase(iIdx, dIdx, form);
    const removeFolder = (idx, name) => removeFolderBase(idx, name, form);
    const handleFiles = (e, idx) => handleFilesBase(e, idx, form);
    const handleDrop = (e, idx) => handleDropBase(e, idx, form);
    const startBatchedUpload = () => startBatchedUploadBase(form);
    const getUniqueFolders = (docs, ret) => getUniqueFoldersBase(docs, ret);

    // Default item structure
    const createNewItem = () => ({
        item_reference: "",
        scan_type_id: "",
        scan_type_name: "",
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

    /**
     * Helper to reconstruct the frontend tracking key for invoice items.
     * This links an invoice item back to a specific scan in the items list.
     */
    const resolveItemKey = (invoiceItem, caseReportItems) => {
        if (!invoiceItem || !invoiceItem.case_report_item_id) return null;

        const crId = String(invoiceItem.case_report_item_id);
        const crItem = (caseReportItems || []).find(cri => String(cri.id) === crId);
        if (!crItem) return null;

        // NEW: If invoice item has scan_id, use it for direct matching
        if (invoiceItem.scan_id) {
            return `${String(crItem.id)}-${String(crItem.scan_type_id)}-${String(invoiceItem.scan_id)}`;
        }

        // Fallback: The item might have scan_details (raw) or scans_with_names (enriched)
        const scans = crItem.scans_with_names && crItem.scans_with_names.length > 0
            ? crItem.scans_with_names
            : (Array.isArray(crItem.scan_details) ? crItem.scan_details : (crItem.scan_details ? [crItem.scan_details] : []));

        const descMatch = (invoiceItem.description || "").trim().toLowerCase();

        // Match by name/description
        const match = scans.find(s => {
            const sName = (s.scan_name || s.name || "").trim().toLowerCase();
            return sName === descMatch;
        });

        if (match) {
            return `${String(crItem.id)}-${String(crItem.scan_type_id)}-${String(match.scan_id || match.id)}`;
        }
        return null;
    };

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


            form.documents = (data.documents || []).map((path) => ({
                name: typeof path === 'string' ? path.split("/").pop() : path.name,
                path: typeof path === 'string' ? path : path.path
            }));
            const groupedItems = [];
            // Restoration of grouped items from scan_details array
            (data.items || []).forEach(item => {
                // Prefer scans_with_names appended by backend, fallback to scan_details
                const scans = item.scans_with_names && item.scans_with_names.length > 0
                    ? item.scans_with_names
                    : (Array.isArray(item.scan_details) ? item.scan_details : (item.scan_details ? [item.scan_details] : []));

                const selectedScans = scans.map(s => ({
                    id: item.id, // For tracking
                    scan_id: s.scan_id?.toString() || "",
                    scan_name: s.scan_name || (item.scan?.name && scans.length === 1 ? item.scan.name : "Scan"),
                    amount: s.amount || 0,
                }));

                groupedItems.push({
                    id: item.id,
                    item_reference: item.item_reference || "",
                    scan_type_id: item.scan_type_id?.toString() || "",
                    scan_type_name: item.scan_type?.name || "",
                    scan_id: "", // Reset to empty for the selector
                    selected_scans: selectedScans,
                    documents: (item.documents || []).map((path) => ({
                        name: typeof path === 'string' ? path.split("/").pop() : path.name,
                        path: typeof path === 'string' ? path : path.path
                    })),
                    remarks: item.remarks || "",
                    processing: false,
                    amount: selectedScans.reduce((total, s) => total + parseFloat(s.amount || 0), 0),
                });
            });
            form.items = groupedItems;

            // Sync invoice items initially - only if invoice exists
            if (data.invoice) {
                form.invoice_id = data.invoice.id;
                form.invoice_no = data.invoice.invoice_no || "";
                form.status = data.invoice.status || "pending";
                form.discount_amount = data.invoice.discount_amount || 0;
                form.tax_amount = data.invoice.tax_amount || 0;
                form.invoice_date = data.invoice.invoice_date ? data.invoice.invoice_date.split('T')[0] : "";
                form.notes = data.invoice.notes || "";

                if (!data.invoice.items || data.invoice.items.length === 0) {
                    form.invoice_items = [];
                } else {
                    console.log("[Diagnostic] Total Invoice Items:", data.invoice.items.length);
                    // If invoice exists with items, use those
                    form.invoice_items = (data.invoice.items || []).map(item => {
                        console.log("[Diagnostic] Processing Invoice Item:", item.description, "CRI ID:", item.case_report_item_id);
                        let key = resolveItemKey(item, data.items);
                        console.log("[Diagnostic] resolveItemKey result:", key);

                        // Fallback: If we can't resolve by name/details, but we have a cri match, try to guess
                        if (!key && item.case_report_item_id) {
                            const cri = (data.items || []).find(i => String(i.id) === String(item.case_report_item_id));
                            if (cri) {
                                console.log("[Diagnostic] Found CRI for fallback:", cri.id, "Scan Type:", cri.scan_type_id);
                                const scans = cri.scans_with_names && cri.scans_with_names.length > 0
                                    ? cri.scans_with_names
                                    : (Array.isArray(cri.scan_details) ? cri.scan_details : (cri.scan_details ? [cri.scan_details] : []));

                                console.log("[Diagnostic] CRI Scans:", scans);
                                const firstScan = scans[0];
                                if (firstScan) {
                                    key = `${String(cri.scan_type_id)}-${String(firstScan.scan_id || firstScan.id)}`;
                                    console.log("[Diagnostic] Fallback Key Generated:", key);
                                }
                            } else {
                                console.log("[Diagnostic] CRI NOT FOUND in data.items for ID:", item.case_report_item_id);
                            }
                        }

                        return {
                            id: item.id,
                            case_report_item_id: item.case_report_item_id,
                            scan_id: item.scan_id,
                            description: item.description,
                            scan_type_name: item.scan_type_name || null,
                            amount: item.amount,
                            _key: key
                        };
                    });
                    console.log("[Diagnostic] Final Form Invoice Items Keys:", form.invoice_items.map(i => i._key));
                }
            } else {
                form.invoice_id = null;
                form.invoice_no = "";
                form.invoice_items = [];
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
    const removeItem = (index) => {
        const item = form.items[index];
        if (item && item.id) {
            deletedCaseReportItemIds.value.push(item.id);
        }
        form.items.splice(index, 1);
    };

    const addInvoiceItem = (scan = null) => {
        if (!scan) {
            // Manual addition of an empty row
            form.invoice_items.push({
                case_report_item_id: null,
                scan_id: null,
                description: "",
                amount: 0,
            });
            return;
        }

        // Check if this scan is already in the invoice
        const key = scan.id
            ? `${String(scan.id)}-${String(scan.scan_type_id)}-${String(scan.scan_id)}`
            : `${String(scan.scan_type_id)}-${String(scan.scan_id)}`;

        const exists = form.invoice_items.some(inv => {
            if (inv._key && String(inv._key) === key) return true;
            if (scan.id && String(inv.case_report_item_id) === String(scan.id) && String(inv.scan_id) === String(scan.scan_id)) return true;
            return false;
        });

        if (!exists) {
            form.invoice_items.push({
                _key: key,
                case_report_item_id: scan.id || null,
                scan_id: scan.scan_id || null,
                description: scan.scan_name || "Scan",
                scan_type_name: scan.scan_type_name || "",
                amount: scan.amount || 0,
            });
        }
    };

    const removeInvoiceItem = (index) => {
        const item = form.invoice_items[index];
        if (item && item.id) {
            deletedInvoiceItemIds.value.push(item.id);
        }
        form.invoice_items.splice(index, 1);
    };

    const handleSubmit = async (options = {}) => {
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
                case_report_items: (() => {
                    const items = (form.items || []).map((itemBlock) => {
                        // Calculate total for this block
                        const blockTotal = (itemBlock.selected_scans || []).reduce((sum, s) => {
                            return sum + (parseFloat(s.amount) || 0);
                        }, 0);

                        // Send one CaseReportItem per block with all scans nested
                        return {
                            id: itemBlock.id || null,
                            case_report_item_id: itemBlock.id || null, // Shared naming convention
                            item_reference: itemBlock.item_reference,
                            scan_type_id: itemBlock.scan_type_id,
                            scans: (itemBlock.selected_scans || []).map(scan => ({
                                scan_id: scan.scan_id,
                                scan_name: scan.scan_name,
                                amount: scan.amount || 0,
                            })),
                            documents: (itemBlock.documents || []).map((d) => d.path || d),
                            remarks: itemBlock.remarks || "",
                            total_amount: blockTotal,
                            action: itemBlock.id ? 2 : 1 // 1: New, 2: Update
                        };
                    });

                    // Append deleted items with action 3
                    deletedCaseReportItemIds.value.forEach(id => {
                        items.push({
                            id: id,
                            case_report_item_id: id,
                            action: 3 // 3: Delete
                        });
                    });

                    return items;
                })(),
            };

            // Only send invoice fields if we are explicitly generating or if it already exists
            if (options.generateInvoice || form.invoice_id) {
                payload.invoice_date = form.invoice_date || (() => {
                    const d = new Date();
                    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
                })();
                payload.discount_amount = form.discount_amount || 0;
                payload.tax_amount = form.tax_amount || 0;
                payload.notes = form.notes || "";

                // Construct invoice items with action codes
                const invoiceItems = (form.invoice_items || []).map((item) => ({
                    id: item.id || null, // Map to id for backend
                    invoice_item_id: item.id || null, // Keeping both for compatibility with user request
                    case_report_item_id: item.case_report_item_id || null,
                    scan_id: item.scan_id || null,
                    description: item.description,
                    scan_type_name: item.scan_type_name || null,
                    amount: item.amount,
                    action: item.id ? 2 : 1 // 1: New, 2: Update
                }));

                // Append deleted items with action 3
                deletedInvoiceItemIds.value.forEach(id => {
                    invoiceItems.push({
                        id: id,
                        invoice_item_id: id,
                        action: 3 // 3: Delete
                    });
                });

                payload.invoice_items = invoiceItems;
            }

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
                    form.invoice_items = (updatedData.invoice.items || []).map(item => {
                        let key = resolveItemKey(item, updatedData.items);

                        if (!key && item.case_report_item_id) {
                            const cri = (updatedData.items || []).find(i => String(i.id) === String(item.case_report_item_id));
                            if (cri && cri.scan_details) {
                                const scans = Array.isArray(cri.scan_details) ? cri.scan_details : [cri.scan_details];
                                const firstScan = scans[0];
                                if (firstScan) {
                                    key = `${String(cri.id)}-${String(cri.scan_type_id)}-${String(item.scan_id || firstScan.scan_id || firstScan.id)}`;
                                }
                            }
                        }

                        return {
                            id: item.id,
                            case_report_item_id: item.case_report_item_id,
                            scan_id: item.scan_id,
                            description: item.description,
                            scan_type_name: item.scan_type_name || null,
                            amount: item.amount,
                            _key: key
                        };
                    });
                }

                if (!isEdit || !options.generateInvoice) {
                    router.push("/case-reports");
                } else if (options.generateInvoice || form.invoice_id) {
                    router.push("/invoices");
                }
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
