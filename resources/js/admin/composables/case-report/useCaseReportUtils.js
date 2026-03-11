import { watch } from "vue";

export function useCaseReportUtils(form, patients, referers, scanTypes, fetching, deletedCaseReportItemIds, isWhatsappModalOpen, router) {
    const createNewItem = () => ({
        scan_type_id: "",
        scan_types_fk_id: "",
        scan_type_name: "",
        scan_fk_id: "",
        selected_scans: [], // { scan_fk_id, scan_name, amount }
        documents: [],
        folderName: "",
        remarks: "",
        processing: false,
        amount: "",
    });

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

    const handleModalClose = () => {
        isWhatsappModalOpen.value = false;
        router.push("/case-reports");
    };

    const setupFieldWatchers = () => {
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

        watch(() => form.referer_fk_id, (newVal) => {
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
    };

    return {
        createNewItem,
        getScans,
        addItem,
        removeItem,
        handleModalClose,
        setupFieldWatchers
    };
}
