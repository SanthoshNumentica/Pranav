import axios from "axios";

export function useCaseReportApi(
    form,
    loading,
    fetching,
    error,
    authUser,
    addToast,
    router,
    isEdit,
    patients,
    referers,
    fetchNextPaymentId,
    fetchNextCaseId,
    fetchNextInvoiceNo,
    fetchMastersBase,
    subTotal,
    totalAmount,
    totalPaid,
    deletedCaseReportItemIds,
    deletedInvoiceItemIds,
    resolveItemKey,
    reportForWhatsapp,
    sendingWhatsapp,
    isWhatsappModalOpen,
    handleModalClose,
    validateForm
) {
    const fetchMasters = async () => {
        try {
            await fetchMastersBase();
            if (!form.payment_id) {
                await fetchNextPaymentId();
            }
            if (!isEdit) {
                if (authUser.value?.branch_id) {
                    form.branch_id = authUser.value.branch_id.toString();
                }
                if (!form.case_id) {
                    await fetchNextCaseId();
                }
                if (!form.invoice_no) {
                    await fetchNextInvoiceNo();
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

            if (data.patient && !patients.value.find(p => p.id === data.patient.id)) {
                patients.value.push(data.patient);
            }

            form.patient_fk_id = data.patient_fk_id?.toString() || "";
            form.title_fk_id = data.patient?.title_fk_id?.toString() || "";
            form.patient_name = data.patient?.name || "";
            form.patient_place = data.patient?.place || "";
            form.gender_fk_id = data.patient?.gender_fk_id?.toString() || "";
            form.age = data.patient?.age || "";

            if (data.referer && !referers.value.find(r => r.id === data.referer.id)) {
                referers.value.push(data.referer);
            }

            form.referer_fk_id = data.referer_fk_id?.toString() || "";
            form.title_id = data.referer?.title_id?.toString() || "";
            form.referer_type_id = data.referer?.referer_type_id?.toString() || "";
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
            form.scanning_date = data.scanning_date ? data.scanning_date.split('T')[0] : "";
            form.check_in = data.check_in || "";
            form.is_stat_case = data.is_stat_case ?? false;

            form.documents = (data.documents || []).map((path) => ({
                name: typeof path === 'string' ? path.split("/").pop() : path.name,
                path: typeof path === 'string' ? path : path.path
            }));
            const groupedItems = [];
            
            (data.items || []).forEach(item => {
                const scans = item.scans_with_names && item.scans_with_names.length > 0
                    ? item.scans_with_names
                    : (Array.isArray(item.scan_details) ? item.scan_details : (item.scan_details ? [item.scan_details] : []));

                const selectedScans = scans.map(s => ({
                    id: item.id,
                    scan_fk_id: s.scan_fk_id?.toString() || s.scan_id?.toString() || "",
                    scan_name: s.scan_name || (item.scan?.name && scans.length === 1 ? item.scan.name : "Scan"),
                    amount: s.amount || 0,
                }));

                groupedItems.push({
                    id: item.id,
                    scan_type_id: item.scan_type_id || "",
                    scan_types_fk_id: item.scan_types_fk_id?.toString() || item.scan_type_id?.toString() || "",
                    scan_type_name: item.scan_type_name || item.scan_type?.name || "",
                    scan_fk_id: "",
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

            if (data.invoice) {
                form.invoice_id = data.invoice.id;
                form.invoice_no = data.invoice.invoice_id || "";
                form.status = data.invoice.status || "pending";
                form.discount_fk_id = data.invoice.discount_fk_id ? data.invoice.discount_fk_id.toString() : "custom";
                form.discount_amount = data.invoice.discount_amount || 0;
                form.tax_amount = data.invoice.tax_amount || 0;
                form.invoice_total_amount = data.invoice.total_amount || 0;
                form.invoice_paid_amount = data.invoice.paid_amount || 0;
                form.invoice_date = data.invoice.invoice_date ? data.invoice.invoice_date.split('T')[0] : "";
                form.notes = data.invoice.notes || "";

                if (!data.invoice.items || data.invoice.items.length === 0) {
                    form.invoice_items = [];
                } else {
                    form.invoice_items = (data.invoice.items || []).map(item => {
                        let key = resolveItemKey(item, data.items);

                        if (!key && item.case_report_item_fk_id) {
                            const cri = (data.items || []).find(i => String(i.id) === String(item.case_report_item_fk_id));
                            if (cri) {
                                const scans = cri.scans_with_names && cri.scans_with_names.length > 0
                                    ? cri.scans_with_names
                                    : (Array.isArray(cri.scan_details) ? cri.scan_details : (cri.scan_details ? [cri.scan_details] : []));

                                const firstScan = scans[0];
                                if (firstScan) {
                                    key = `${String(cri.scan_types_fk_id)}-${String(firstScan.scan_fk_id || firstScan.id)}`;
                                }
                            }
                        }

                        return {
                            id: item.id,
                            case_report_item_fk_id: item.case_report_item_fk_id,
                            scan_fk_id: item.scan_fk_id,
                            description: item.description,
                            scan_type_name: item.scan_type_name || null,
                            amount: item.amount,
                            _key: key
                        };
                    });
                }

                if (data.invoice.payments && data.invoice.payments.length > 0) {
                    form.payment_history = [];
                    data.invoice.payments.forEach(paymentRecord => {
                        try {
                            const details = typeof paymentRecord.payment_details === 'string'
                                ? JSON.parse(paymentRecord.payment_details)
                                : paymentRecord.payment_details;

                            if (details && details.payments && Array.isArray(details.payments)) {
                                details.payments.forEach(p => {
                                    form.payment_history.push({
                                        payment_id: paymentRecord.payment_id,
                                        payment_method_fk_id: p.payment_method_fk_id?.toString() || "",
                                        amount: p.amount || 0,
                                        payment_date: paymentRecord.payment_date ? paymentRecord.payment_date.split('T')[0] : p.payment_date,
                                        notes: p.notes || details.notes || ""
                                    });
                                });
                            } else {
                                form.payment_history.push({
                                    payment_id: paymentRecord.payment_id,
                                    payment_method_fk_id: paymentRecord.payment_method_fk_id?.toString() || "",
                                    amount: paymentRecord.amount || 0,
                                    payment_date: paymentRecord.payment_date ? paymentRecord.payment_date.split('T')[0] : "",
                                    notes: ""
                                });
                            }
                        } catch (e) {
                            console.error("Failed to parse payment details JSON", e);
                        }
                    });

                    form.payments = [];
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

    const handleSubmit = async (options = {}) => {
        loading.value = true;
        error.value = null;

        const validationErrors = validateForm(form, authUser);

        if (!isEdit && !form.case_id) {
            await fetchNextCaseId();
            if (!form.case_id) validationErrors.push("System failed to generate Case Id.");
        }

        if (validationErrors.length > 0) {
            addToast({ title: "Validation Required", description: validationErrors[0], variant: "danger" });
            loading.value = false;
            return;
        }

        try {
            const caseBranchId = form.branch_id || authUser.value?.branch_id || "";

            const payload = {
                patient_details: {
                    patient_fk_id: form.patient_fk_id || null,
                    title_fk_id: form.title_fk_id || null,
                    name: form.patient_name || "",
                    mobile_no: form.whatsapp_no_patient || "",
                    whatsapp_no: form.send_whatsapp_patient ? form.whatsapp_no_patient : null,
                    gender_fk_id: form.gender_fk_id || null,
                    place: form.patient_place || "",
                    age: form.age || null
                },
                referer_details: {
                    referer_fk_id: form.referer_fk_id || null,
                    title_fk_id: form.title_id || null,
                    name: form.referer_name || "",
                    mobile_no: form.whatsapp_no_referer || "",
                    referer_type_id: form.referer_type_id || null,
                    hospital_name: form.hospital_name || ""
                },
                case_reports: {
                    case_id: form.case_id,
                    branch_fk_id: caseBranchId,
                    scanning_date: form.scanning_date || null,
                    check_in: form.check_in || "",
                    is_stat_case: form.is_stat_case ?? false,
                    patient_fk_id: form.patient_fk_id || null,
                    referer_fk_id: form.referer_fk_id || null,
                    description: form.description || "",
                    documents: (form.documents || []).map((d) => d.path || d),
                    case_report_items: (() => {
                        const items = (form.items || []).map((itemBlock) => {
                            const blockTotal = (itemBlock.selected_scans || []).reduce((sum, s) => {
                                return sum + (parseFloat(s.amount) || 0);
                            }, 0);

                            return {
                                id: itemBlock.id || null,
                                case_report_item_fk_id: itemBlock.id || null,
                                scan_type_id: itemBlock.scan_type_id,
                                scan_types_fk_id: itemBlock.scan_types_fk_id,
                                scans: (itemBlock.selected_scans || []).map(scan => ({
                                    scan_fk_id: scan.scan_fk_id,
                                    scan_name: scan.scan_name,
                                    amount: scan.amount || 0,
                                })),
                                documents: (itemBlock.documents || []).map((d) => d.path || d),
                                remarks: itemBlock.remarks || "",
                                total_amount: blockTotal,
                                action: itemBlock.id ? 2 : 1
                            };
                        });

                        deletedCaseReportItemIds.value.forEach(id => {
                            items.push({ id: id, case_report_item_fk_id: id, action: 3 });
                        });
                        return items;
                    })()
                }
            };

            const hasInvoiceItems = (form.invoice_items || []).length > 0;
            const validNewPayments = (form.payments || []).filter(p => parseFloat(p.amount) > 0 && p.payment_method_fk_id);

            if (hasInvoiceItems || options.generateInvoice || form.invoice_id) {
                const invoiceItems = (form.invoice_items || []).map((item) => ({
                    id: item.id || null,
                    invoice_fk_id: form.invoice_id || null,
                    case_report_item_fk_id: item.case_report_item_fk_id || null,
                    scan_fk_id: item.scan_fk_id || null,
                    description: item.description,
                    scan_type_name: item.scan_type_name || null,
                    amount: parseFloat(item.amount) || 0,
                    action: item.id ? 2 : 1
                }));

                deletedInvoiceItemIds.value.forEach(id => {
                    invoiceItems.push({ id: id, invoice_fk_id: form.invoice_id || id, action: 3 });
                });

                payload.invoice_details = {
                    invoice_id: form.invoice_no || null,
                    case_report_fk_id: form.id || null,
                    patient_fk_id: form.patient_fk_id || null,
                    branch_fk_id: caseBranchId,
                    sub_total: subTotal.value || 0,
                    discount_amount: form.discount_amount || 0,
                    discount_fk_id: form.discount_fk_id !== 'custom' ? form.discount_fk_id : null,
                    tax_amount: form.tax_amount || 0,
                    total_amount: totalAmount.value || 0,
                    paid_amount: totalPaid.value || 0,
                    status: form.status || "pending",
                    invoice_date: form.invoice_date || (() => {
                        const d = new Date();
                        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
                    })(),
                    notes: form.notes || "",
                    invoice_items: invoiceItems
                };

                if (validNewPayments.length > 0) {
                    payload.payment_details = {
                        payment_id: form.payment_id || null,
                        invoice_fk_id: form.invoice_id || null,
                        payment_date: validNewPayments[0].payment_date || new Date().toISOString().split('T')[0],
                        payment_details: {
                            notes: validNewPayments[0].notes || "",
                            payments: validNewPayments.map(p => ({
                                payment_method_fk_id: p.payment_method_fk_id,
                                amount: parseFloat(p.amount)
                            }))
                        }
                    };
                }
            }

            let response;
            if (isEdit) {
                response = await axios.put(`/api/v1/case-reports/${form.id}`, payload);
            } else {
                response = await axios.post("/api/v1/case-reports", payload);
            }

            if (response.data.success) {
                addToast({ title: "Success", description: isEdit ? "Case report updated successfully." : "Case report created successfully.", variant: "success" });

                const updatedData = response.data.data;
                if (updatedData.invoice) {
                    form.invoice_id = updatedData.invoice.id;
                    form.invoice_no = updatedData.invoice.invoice_no || "";
                    form.status = updatedData.invoice.status || "pending";
                    form.invoice_date = updatedData.invoice.invoice_date ? updatedData.invoice.invoice_date.split('T')[0] : "";
                    form.notes = updatedData.invoice.notes || "";
                    form.invoice_items = (updatedData.invoice.items || []).map(item => {
                        let key = resolveItemKey(item, updatedData.items);

                        if (!key && item.case_report_item_fk_id) {
                            const cri = (updatedData.items || []).find(i => String(i.id) === String(item.case_report_item_fk_id));
                            if (cri && cri.scan_details) {
                                const scans = Array.isArray(cri.scan_details) ? cri.scan_details : [cri.scan_details];
                                const firstScan = scans[0];
                                if (firstScan) {
                                    key = `${String(cri.id)}-${String(cri.scan_types_fk_id)}-${String(item.scan_fk_id || firstScan.scan_fk_id || firstScan.id)}`;
                                }
                            }
                        }

                        return {
                            id: item.id,
                            case_report_item_fk_id: item.case_report_item_fk_id,
                            scan_fk_id: item.scan_fk_id,
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

    return {
        fetchMasters,
        fetchCaseReport,
        handleSubmit,
        handleSendWhatsApp
    };
}
