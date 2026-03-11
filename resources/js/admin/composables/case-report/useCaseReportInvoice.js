import { computed, watch } from "vue";

export function useCaseReportInvoice(form, discounts, deletedInvoiceItemIds) {
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
        if (!form.discount_fk_id || form.discount_fk_id === "custom" || !discounts.value?.length) return;
        const d = discounts.value.find((item) => String(item.id) === String(form.discount_fk_id));
        if (d && d.percentage > 0) {
            form.discount_amount = (subTotal.value * parseFloat(d.percentage)) / 100;
        }
    };

    watch(() => form.discount_fk_id, calculateDiscount);
    watch(subTotal, calculateDiscount);

    const resolveItemKey = (invoiceItem, caseReportItems) => {
        if (!invoiceItem || !invoiceItem.case_report_item_fk_id) return null;

        const crId = String(invoiceItem.case_report_item_fk_id);
        const crItem = (caseReportItems || []).find(cri => String(cri.id) === crId);
        if (!crItem) return null;

        if (invoiceItem.scan_fk_id) {
            return `${String(crItem.id)}-${String(crItem.scan_types_fk_id)}-${String(invoiceItem.scan_fk_id)}`;
        }

        const scans = crItem.scans_with_names && crItem.scans_with_names.length > 0
            ? crItem.scans_with_names
            : (Array.isArray(crItem.scan_details) ? crItem.scan_details : (crItem.scan_details ? [crItem.scan_details] : []));

        const descMatch = (invoiceItem.description || "").trim().toLowerCase();

        const match = scans.find(s => {
            const sName = (s.scan_name || s.name || "").trim().toLowerCase();
            return sName === descMatch;
        });

        if (match) {
            return `${String(crItem.id)}-${String(crItem.scan_types_fk_id)}-${String(match.scan_fk_id || match.id)}`;
        }
        return null;
    };

    const addInvoiceItem = (scan = null) => {
        if (!scan) {
            form.invoice_items.push({
                case_report_item_fk_id: null,
                scan_fk_id: null,
                description: "",
                amount: 0,
            });
            return;
        }

        const key = scan.id
            ? `${String(scan.id)}-${String(scan.scan_types_fk_id)}-${String(scan.scan_fk_id)}`
            : `${String(scan.scan_types_fk_id)}-${String(scan.scan_fk_id)}`;

        const exists = form.invoice_items.some(inv => {
            if (inv._key && String(inv._key) === key) return true;
            if (scan.id && String(inv.case_report_item_fk_id) === String(scan.id) && String(inv.scan_fk_id) === String(scan.scan_fk_id)) return true;
            return false;
        });

        if (!exists) {
            form.invoice_items.push({
                _key: key,
                case_report_item_fk_id: scan.id || null,
                scan_fk_id: scan.scan_fk_id || null,
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

    const setupInvoiceSyncWatchers = (fetching) => {
        watch(() => form.items, (newItems) => {
            if (fetching.value) return;

            const currentScans = [];
            newItems.forEach(item => {
                (item.selected_scans || []).forEach(scan => {
                    const key = item.id
                        ? `${String(item.id)}-${String(item.scan_types_fk_id)}-${String(scan.scan_fk_id)}`
                        : `${String(item.scan_types_fk_id)}-${String(scan.scan_fk_id)}`;

                    currentScans.push({
                        key,
                        case_report_item_fk_id: item.id || null,
                        scan_fk_id: scan.scan_fk_id,
                        description: `${scan.scan_name} (${item.scan_type_name || 'Scan'})`,
                        amount: scan.amount,
                        scan_type_name: item.scan_type_name
                    });
                });
            });

            currentScans.forEach(scan => {
                const index = form.invoice_items.findIndex(inv =>
                    inv._key === scan.key ||
                    (inv.case_report_item_fk_id && String(inv.case_report_item_fk_id) === String(scan.case_report_item_fk_id) && String(inv.scan_fk_id) === String(scan.scan_fk_id))
                );

                if (index === -1) {
                    form.invoice_items.push({
                        _key: scan.key,
                        case_report_item_fk_id: scan.case_report_item_fk_id,
                        scan_fk_id: scan.scan_fk_id,
                        description: scan.description,
                        scan_type_name: scan.scan_type_name,
                        amount: scan.amount,
                    });
                } else {
                    const invItem = form.invoice_items[index];
                    if (invItem.amount !== scan.amount) {
                        invItem.amount = scan.amount;
                    }
                }
            });

            for (let i = form.invoice_items.length - 1; i >= 0; i--) {
                const invItem = form.invoice_items[i];
                if (invItem.scan_fk_id) {
                    const stillExists = currentScans.some(scan =>
                        scan.key === invItem._key ||
                        (scan.case_report_item_fk_id && String(scan.case_report_item_fk_id) === String(invItem.case_report_item_fk_id) && String(scan.scan_fk_id) === String(invItem.scan_fk_id))
                    );

                    if (!stillExists) {
                        removeInvoiceItem(i);
                    }
                }
            }
        }, { deep: true });
    };

    return {
        subTotal,
        totalAmount,
        resolveItemKey,
        addInvoiceItem,
        removeInvoiceItem,
        setupInvoiceSyncWatchers
    };
}
