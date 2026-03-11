export function useCaseReportValidation() {
    const validateForm = (form, authUser) => {
        const errors = [];
        const hasPatient = form.patient_fk_id || form.patient_name;
        if (!hasPatient) errors.push("Patient details are required");

        const hasReferer = form.referer_fk_id || form.referer_name;
        if (!hasReferer) errors.push("Referer details are required");

        if (!form.branch_id && !authUser?.value?.branch_id) {
            errors.push("Case information is required");
        }

        if (form.items.length === 0) {
            errors.push("Please add at least one scan item");
        }

        form.items.forEach((item, idx) => {
            if (!item.scan_types_fk_id) errors.push(`Scan Item #${idx + 1}: Scan Type is required.`);
            if (!item.selected_scans || item.selected_scans.length === 0) {
                errors.push(`Scan Item #${idx + 1}: At least one specific scan must be added.`);
            }
        });

        return errors;
    };

    return { validateForm };
}
