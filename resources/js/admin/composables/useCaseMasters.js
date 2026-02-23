import { ref } from "vue";
import axios from "axios";

export function useCaseMasters() {
    const patients = ref([]);
    const referers = ref([]);
    const scanTypes = ref([]);
    const branches = ref([]);
    const discounts = ref([]);
    const paymentMethods = ref([]);

    const fetchNextCaseId = async (form) => {
        try {
            const response = await axios.get("/api/v1/case-reports/next-id");
            if (response.data.success) {
                form.case_id = response.data.next_case_id;
            }
        } catch (err) {
            console.error("Failed to fetch next case ID", err);
        }
    };

    const fetchMasters = async () => {
        try {
            const [sRes, bRes, pmRes, dRes] = await Promise.all([
                axios.get("/api/v1/masters/scan-types?status=active&nopaginate=1"),
                axios.get("/api/v1/masters/branches?status=active&nopaginate=1"),
                axios.get("/api/v1/masters/payment-methods?status=active&nopaginate=1"),
                axios.get("/api/v1/masters/discounts?status=active&nopaginate=1"),
            ]);

            scanTypes.value = Array.isArray(sRes.data.data)
                ? sRes.data.data
                : sRes.data.data?.data || [];
            branches.value = Array.isArray(bRes.data.data)
                ? bRes.data.data
                : bRes.data.data?.data || [];
            paymentMethods.value = Array.isArray(pmRes.data.data)
                ? pmRes.data.data
                : pmRes.data.data?.data || [];
            discounts.value = Array.isArray(dRes.data.data)
                ? dRes.data.data
                : dRes.data.data?.data || [];

        } catch (err) {
            console.error("Failed to fetch master data", err);
        }
    };

    return {
        patients,
        referers,
        scanTypes,
        branches,
        paymentMethods,
        discounts,
        fetchMasters,
        fetchNextCaseId
    };
}
