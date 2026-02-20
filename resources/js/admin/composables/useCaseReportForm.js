import { ref, reactive, watch, computed } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "./useToast";
import { useAuth } from "./useAuth";
import axios from "axios";
import JSZip from "jszip";

export function useCaseReportForm(isEdit = false) {
    const router = useRouter();
    const { addToast } = useToast();
    const { user: authUser } = useAuth();

    const loading = ref(false);
    const fetching = ref(true);
    const error = ref(null);

    const patients = ref([]);
    const referers = ref([]);
    const scanTypes = ref([]);
    const branches = ref([]);
    const paymentMethods = ref([]);
    const processingGeneral = ref(false);

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
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        })(),
        rct_hour: new Date().toTimeString().slice(0, 5),
        is_stat: false,
        patient_type: "out_patient",

        // Invoice Details
        invoice_date: (() => {
            const d = new Date();
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        })(),
        discount_amount: 0,
        tax_amount: 0,
        notes: "",
    });

    // Default item structure
    const createNewItem = () => ({
        scan_type_id: "",
        scan_id: "",
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

    // Upload Modal State
    const uploadModal = reactive({
        isOpen: false,
        state: "confirm",
        fileCount: 0,
        progress: 0,
        currentFileIndex: 0,
        totalFiles: 0,
        currentFileName: "",
        title: "",
        description: "",
        variant: "primary",
        pendingFiles: [], // Files waiting to be uploaded after confirmation
        pendingItemIndex: null,
    });

    const fetchNextCaseId = async () => {
        console.log("Fetching next case ID...");
        try {
            const response = await axios.get("/api/v1/case-reports/next-id");
            console.log("Next case ID response:", response.data);
            if (response.data.success) {
                form.case_id = response.data.next_case_id;
                console.log("Set form.case_id to:", form.case_id);
            }
        } catch (err) {
            console.error("Failed to fetch next case ID", err);
        }
    };

    // --- Master Data Fetching ---
    const fetchMasters = async () => {
        try {
            const [pRes, rRes, sRes, bRes, pmRes] = await Promise.all([
                axios.get("/api/v1/masters/patients?status=active&nopaginate=1"),
                axios.get("/api/v1/masters/referers?status=active&nopaginate=1"),
                axios.get("/api/v1/masters/scan-types?status=active&nopaginate=1"),
                axios.get("/api/v1/masters/branches?status=active&nopaginate=1"),
                axios.get("/api/v1/masters/payment-methods?status=active&nopaginate=1"),
            ]);

            patients.value = Array.isArray(pRes.data.data)
                ? pRes.data.data
                : pRes.data.data?.data || [];
            referers.value = Array.isArray(rRes.data.data)
                ? rRes.data.data
                : rRes.data.data?.data || [];
            scanTypes.value = Array.isArray(sRes.data.data)
                ? sRes.data.data
                : sRes.data.data?.data || [];
            branches.value = Array.isArray(bRes.data.data)
                ? bRes.data.data
                : bRes.data.data?.data || [];
            paymentMethods.value = Array.isArray(pmRes.data.data)
                ? pmRes.data.data
                : pmRes.data.data?.data || [];

            // Auto-assign branch for non-super-admins on creation
            if (!isEdit) {
                console.log("Creation mode identified. User:", authUser.value);
                if (authUser.value?.branch_id) {
                    form.branch_id = authUser.value.branch_id.toString();
                    console.log("Auto-assigned branch_id:", form.branch_id);
                }
                console.log("Initial case_id is:", form.case_id);
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
            form.patient_fk_id = data.patient_fk_id?.toString() || "";
            form.patient_name = data.patient?.name || "";
            form.patient_place = data.patient?.place || "";
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

            // If invoice exists (one-to-one with case report in this context)
            if (data.invoice) {
                form.discount_amount = data.invoice.discount_amount || 0;
                form.tax_amount = data.invoice.tax_amount || 0;
                form.invoice_date = data.invoice.invoice_date ? data.invoice.invoice_date.split('T')[0] : "";
                form.notes = data.invoice.notes || "";
            }

            form.documents = (data.documents || []).map((path) => ({
                name: typeof path === 'string' ? path.split("/").pop() : path.name,
                path: typeof path === 'string' ? path : path.path
            }));

            form.items = data.items.map((item) => ({
                scan_type_id: item.scan_type_id?.toString() || "",
                scan_id: item.scan_id?.toString() || "",
                documents: (item.documents || []).map((path) => ({
                    name: typeof path === 'string' ? path.split("/").pop() : path.name,
                    path: typeof path === 'string' ? path : path.path
                })),
                remarks: item.remarks || "",
                processing: false,
                amount: item.amount || "",
            }));

        } catch (err) {
            console.error("Failed to fetch case report", err);
            error.value = "Failed to load case data. Please refresh.";
        } finally {
            fetching.value = false;
        }
    };

    // --- Watchers for Auto-filling ---
    watch(
        () => form.patient_fk_id,
        (newVal) => {
            if (!newVal || fetching.value) return;
            const list = Array.isArray(patients.value)
                ? patients.value
                : patients.value.data || [];
            const p = list.find((p) => String(p.id) === String(newVal));
            if (p) {
                form.patient_name = p.name || "";
                form.patient_place = p.place || "";
                form.whatsapp_no_patient = p.whatsapp_no || p.mobile_no || "";
            }
        },
    );

    watch(
        () => form.referer_id,
        (newVal) => {
            if (!newVal || fetching.value) return;
            const list = Array.isArray(referers.value)
                ? referers.value
                : referers.value.data || [];
            const r = list.find((d) => String(d.id) === String(newVal));
            if (r) {
                form.referer_name = r.name || "";
                form.whatsapp_no_referer = r.mobile_no || "";
                form.hospital_name = r.hospital_name || "";
                form.hospital_id = r.hospital_id || "";
            }
        },
    );

    // --- Helpers ---
    const getScans = (typeId) => {
        if (!typeId) return [];
        const type = scanTypes.value.find((t) => t.id == typeId);
        return type ? type.scans : [];
    };

    const getFileName = (path) => {
        if (!path) return "";
        return path.split("/").pop();
    };

    const addItem = () => {
        form.items.push(createNewItem());
    };

    const removeItem = (index) => {
        form.items.splice(index, 1);
    };

    const getUniqueFolders = (documents, returnRootFiles = false) => {
        if (!documents || documents.length === 0) return [];

        const folders = {};
        const rootFiles = [];

        documents.forEach((doc) => {
            const path = doc.path || doc; // Handle object or string
            if (path) {
                const parts = path.split("/");
                // Assuming structure: app/case-reports/[Folder?]/[File]
                if (parts.length > 3) {
                    const folderName = parts[2];
                    if (!folders[folderName]) folders[folderName] = 0;
                    folders[folderName]++;
                } else {
                    rootFiles.push(doc);
                }
            }
        });

        if (returnRootFiles) return rootFiles;

        return Object.keys(folders).map((name) => ({
            name,
            count: folders[name],
        }));
    };

    // --- File Handling (General) ---
    const handleGeneralFiles = async (event) => {
        const files = Array.from(event.target.files);
        if (files.length === 0) return;

        // Reset input value so the same file can be selected again
        event.target.value = "";

        processingGeneral.value = true;
        error.value = null;

        try {
            for (const file of files) {
                const formData = new FormData();
                formData.append("file", file);
                formData.append("type", "document");

                const response = await axios.post("/api/v1/files/upload", formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                });

                if (response.data.success) {
                    form.documents.push({
                        name: response.data.name,
                        path: response.data.path,
                    });
                }
            }
        } catch (err) {
            console.error("Upload failed", err);
            error.value = "Failed to upload document. Please try again.";
        } finally {
            processingGeneral.value = false;
        }
    };

    const removeGeneralDoc = async (index) => {
        const file = form.documents[index];
        if (file?.path) {
            try {
                await axios.delete("/api/v1/files/delete", { data: { path: file.path } });
            } catch (e) {
                console.error("Failed to delete file from server", e);
            }
        }
        form.documents.splice(index, 1);
    };

    // --- DICOM Handling (Recursive & Batched) ---
    const removeFolder = async (index, folderName) => {
        const item = form.items[index];
        if (!folderName) return;

        const folderPath = `app/case-reports/${folderName}`;
        try {
            await axios.delete("/api/v1/files/delete", {
                data: { path: folderPath },
            });
        } catch (e) {
            console.error("Failed to delete folder from server", e);
        }

        item.documents = item.documents.filter((doc) => {
            const path = doc.path || doc;
            return !path.includes(`/case-reports/${folderName}/`);
        });
    };

    const removeDoc = async (itemIndex, docIndex) => {
        const file = form.items[itemIndex].documents[docIndex];
        if (file?.path) {
            try {
                await axios.delete("/api/v1/files/delete", { data: { path: file.path } });
            } catch (e) {
                console.error("Failed to delete file from server", e);
            }
        }
        form.items[itemIndex].documents.splice(docIndex, 1);
    };

    // Recursive File Scanner
    const scanFiles = async (items) => {
        const files = [];

        const traverse = (entry, path = "") => {
            return new Promise((resolve) => {
                if (entry.isFile) {
                    entry.file((file) => {
                        file.startPath = path + file.name;
                        files.push(file);
                        resolve();
                    });
                } else if (entry.isDirectory) {
                    const dirReader = entry.createReader();
                    const readEntries = () => {
                        dirReader.readEntries(async (entries) => {
                            if (entries.length === 0) {
                                resolve();
                            } else {
                                const promises = entries.map((e) =>
                                    traverse(e, path + entry.name + "/"),
                                );
                                await Promise.all(promises);
                                readEntries();
                            }
                        });
                    };
                    readEntries();
                } else {
                    resolve();
                }
            });
        };

        const promises = items.map((item) => {
            const entry = item.webkitGetAsEntry();
            if (entry) return traverse(entry);
            return Promise.resolve();
        });

        await Promise.all(promises);
        return files;
    };

    // --- Computed Values ---
    const subTotal = computed(() => {
        return form.items.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
    });

    const totalAmount = computed(() => {
        return (subTotal.value - (parseFloat(form.discount_amount) || 0)) + (parseFloat(form.tax_amount) || 0);
    });

    const handleFiles = async (event, index) => {
        const files = Array.from(event.target.files);
        if (files.length === 0) return;

        // Reset input value so the same folder/files can be selected again
        event.target.value = "";

        if (files.length > 1 || event.target.webkitdirectory) {
            uploadModal.pendingFiles = files;
            uploadModal.pendingItemIndex = index;
            uploadModal.fileCount = files.length;
            uploadModal.state = "confirm";
            uploadModal.isOpen = true;
        } else {
            await uploadDicomFiles(files, index);
        }
    };

    const handleDrop = async (event, index) => {
        const items = Array.from(event.dataTransfer.items);
        const files = await scanFiles(items);

        if (files.length === 0) return;

        if (files.length > 1) {
            uploadModal.pendingFiles = files;
            uploadModal.pendingItemIndex = index;
            uploadModal.fileCount = files.length;
            uploadModal.state = "confirm";
            uploadModal.isOpen = true;
        } else {
            await uploadDicomFiles(files, index);
        }
    };

    const startBatchedUpload = async () => {
        const files = uploadModal.pendingFiles;
        const index = uploadModal.pendingItemIndex;

        uploadModal.state = "uploading";
        uploadModal.progress = 0;
        uploadModal.totalFiles = files.length;

        await uploadDicomFiles(files, index, true);
        uploadModal.isOpen = false;
    };

    const uploadDicomFiles = async (files, index, isUsingModal = false) => {
        if (files.length === 0) return;

        const item = form.items[index];
        item.processing = true;
        error.value = null;

        try {
            // 1. Group files by folder name
            const folders = {};
            const rootFiles = [];

            for (const file of files) {
                const relPath = file.startPath || file.webkitRelativePath;
                if (relPath) {
                    const parts = relPath.split("/");
                    if (parts.length > 1) {
                        const folderName = parts[0];
                        if (!folders[folderName]) folders[folderName] = [];
                        folders[folderName].push(file);
                    } else {
                        rootFiles.push(file);
                    }
                } else {
                    rootFiles.push(file);
                }
            }

            // 2. Upload Root Files (Individual)
            for (let i = 0; i < rootFiles.length; i++) {
                const file = rootFiles[i];
                if (isUsingModal) {
                    uploadModal.currentFileName = file.name;
                    // Approximate progress
                    uploadModal.progress = (i / files.length) * 100;
                }

                const formData = new FormData();
                formData.append("file", file);
                formData.append("type", "dicom");

                const response = await axios.post("/api/v1/files/upload", formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                });

                if (response.data.success) {
                    item.documents.push({
                        name: response.data.name,
                        path: response.data.path,
                    });
                }
            }

            // 3. Process and Upload Folders (Zipped)
            const folderNames = Object.keys(folders);
            let processedCount = rootFiles.length;

            for (const folderName of folderNames) {
                const folderFiles = folders[folderName];
                const currentFolderSize = folderFiles.length;
                const zip = new JSZip();

                // Add files to zip
                folderFiles.forEach(file => {
                    // Try to preserve relative structure inside the folder?
                    // User request: "compress that files inside that folder"
                    // Usually we want the folder structure inside the zip or just flat?
                    // Let's keep it flat inside the zip for now, or relative to the folder.
                    // If the path is "Study1/Series1/Image1", and we zip "Study1",
                    // inside the zip it should probably be "Series1/Image1" or just "Image1".
                    // Let's use the full relative path minus the root folder name to keep structure.

                    const relPath = file.startPath || file.webkitRelativePath;
                    let zipPath = file.name; // Default fallback
                    if (relPath) {
                        // Remove the root folder name from the path
                        // e.g. "Study1/Series/Img.dcm" -> "Series/Img.dcm"
                        const parts = relPath.split('/');
                        if (parts.length > 1) {
                            zipPath = parts.slice(1).join('/');
                        }
                    }
                    zip.file(zipPath, file, {
                        compression: "DEFLATE",
                        compressionOptions: {
                            level: 9
                        }
                    });
                });

                if (isUsingModal) {
                    uploadModal.currentFileName = `Compressing ${folderName} (${currentFolderSize} files)...`;
                }

                // Generate Zip
                const zipBlob = await zip.generateAsync({
                    type: "blob",
                    compression: "DEFLATE",
                    compressionOptions: {
                        level: 9
                    }
                }, (metadata) => {
                    if (isUsingModal) {
                        // Update progress during compression? 
                        // It might be too fast to matter much or conflict with upload progress.
                    }
                });

                const zipFile = new File([zipBlob], `${folderName}.zip`, { type: "application/zip" });

                // Upload Zip
                if (isUsingModal) {
                    uploadModal.currentFileName = `Uploading ${folderName} (${currentFolderSize} files)...`;
                }

                const formData = new FormData();
                formData.append("file", zipFile);
                formData.append("type", "dicom");
                // We might want to indicate this is a zipped study. 
                // The backend should handle extraction or storage.
                // Assuming backend treats .zip as a valid file type for now.

                const response = await axios.post("/api/v1/files/upload", formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                    onUploadProgress: (progressEvent) => {
                        if (isUsingModal) {
                            const zipPercent = progressEvent.loaded / progressEvent.total; // 0 to 1
                            const segmentWeight = currentFolderSize / files.length;
                            const baseProgress = (processedCount / files.length) * 100;
                            const currentSegmentProgress = zipPercent * segmentWeight * 100;

                            uploadModal.progress = Math.min(100, baseProgress + currentSegmentProgress);
                        }
                    }
                });

                if (response.data.success) {
                    item.documents.push({
                        name: response.data.name,
                        path: response.data.path,
                    });
                }

                processedCount += currentFolderSize; // Count actual files processed
                if (isUsingModal) {
                    uploadModal.progress = (processedCount / files.length) * 100;
                }
            }

        } catch (err) {
            console.error("DICOM Upload failed", err);
            const msg = err.message || "Failed to upload DICOM file. Please try again.";
            error.value = msg;

            if (isUsingModal) {
                uploadModal.state = "alert";
                uploadModal.variant = "danger";
                uploadModal.title = "Upload Error";
                uploadModal.description = msg;
                return;
            }
        } finally {
            item.processing = false;
        }
    };

    // --- Submission ---
    const handleSubmit = async () => {
        loading.value = true;
        error.value = null;

        // Validation
        const validationErrors = [];
        if (!form.patient_fk_id) validationErrors.push("Patient selection is required.");
        if (!form.referer_id) validationErrors.push("Referer selection is required.");

        form.items.forEach((item, idx) => {
            if (!item.scan_type_id) validationErrors.push(`Scan Item #${idx + 1}: Scan Type is required.`);
            if (!item.scan_id) validationErrors.push(`Scan Item #${idx + 1}: Specific Scan is required.`);
            // DICOM files are now optional at creation/edit stage (can be added later)
        });

        if (!isEdit && !form.case_id) {
            await fetchNextCaseId();
            if (!form.case_id) {
                console.error("Case ID generation failed in handleSubmit");
                validationErrors.push("System failed to generate SRF No (Case ID). Please refresh the page.");
            }
        }
        console.log("Submitting Case Report with ID:", form.case_id);

        if (validationErrors.length > 0) {
            uploadModal.state = "alert";
            uploadModal.variant = "danger";
            uploadModal.title = "Validation Required";
            uploadModal.description = validationErrors[0];
            uploadModal.isOpen = true;
            loading.value = false;
            return;
        }

        try {
            const payload = {
                case_id: form.case_id,
                patient_fk_id: form.patient_fk_id,
                referer_id: form.referer_id,
                description: form.description,
                documents: form.documents.map((d) => d.path || d), // Handle objects/strings
                items: form.items.map((item) => ({
                    scan_type_id: item.scan_type_id,
                    scan_id: item.scan_id,
                    documents: item.documents.map((d) => d.path || d),
                    remarks: item.remarks,
                    amount: item.amount,
                })),
                branch_id: form.branch_id,
                rct_date: form.rct_date,
                rct_hour: form.rct_hour,
                is_stat: form.is_stat,
                patient_type: form.patient_type,
            };

            let response;
            if (isEdit) {
                response = await axios.put(`/api/v1/case-reports/${form.id}`, payload);
            } else {
                // If branch_id is still empty, try to fallback to user's branch
                if (!payload.branch_id && authUser.value?.branch_id) {
                    payload.branch_id = authUser.value.branch_id;
                }
                response = await axios.post("/api/v1/case-reports", payload);
            }

            if (response.data.success) {
                addToast({
                    title: "Success",
                    description: isEdit ? "Case report updated successfully." : "Case report created successfully.",
                    variant: "success",
                });

                // reportForWhatsapp.value = response.data.data;
                // isWhatsappModalOpen.value = true;
                router.push("/case-reports");
            }
        } catch (err) {
            console.error("Save failed", err);
            const originalError = err.response?.data?.message || "Failed to save case report.";
            error.value = originalError;

            uploadModal.state = "alert";
            uploadModal.variant = "danger";
            uploadModal.title = "Save Error";
            uploadModal.description = originalError;
            uploadModal.isOpen = true;
        } finally {
            loading.value = false;
        }
    };

    const handleSendWhatsApp = async (recipients) => {
        if (!reportForWhatsapp.value) return;

        sendingWhatsapp.value = true;
        try {
            const response = await axios.post(
                `/api/v1/case-reports/${reportForWhatsapp.value.id}/whatsapp`,
                { recipients },
            );
            if (response.data.success) {
                addToast({
                    title: "Success",
                    description: "WhatsApp notification sent successfully.",
                    variant: "success",
                });
                handleModalClose();
            }
        } catch (err) {
            console.error("WhatsApp failed", err);
            addToast({
                title: "Error",
                description: err.response?.data?.message || "Failed to send notification.",
                variant: "error",
            });
        } finally {
            sendingWhatsapp.value = false;
        }
    };

    const handleModalClose = () => {
        isWhatsappModalOpen.value = false;
        router.push("/case-reports");
    };

    const loggedInUserIsSuperAdmin = computed(
        () => authUser.value?.role?.name.toLowerCase() === "super-admin",
    );

    const filteredBranches = computed(() => {
        if (loggedInUserIsSuperAdmin.value) return branches.value;
        if (!authUser.value?.branch_id) return [];
        return branches.value.filter(
            (b) => b.id.toString() === authUser.value.branch_id.toString(),
        );
    });


    return {
        // State
        loading,
        fetching,
        error,
        form,
        patients,
        referers,
        scanTypes,
        processingGeneral,
        uploadModal,
        isWhatsappModalOpen,
        reportForWhatsapp,
        sendingWhatsapp,
        initialRecipients,
        branches,
        paymentMethods,
        loggedInUserIsSuperAdmin,
        filteredBranches,
        subTotal,
        totalAmount,

        // Methods
        fetchMasters,
        fetchCaseReport,
        getScans,
        getFileName,
        addItem,
        removeItem,
        getUniqueFolders,
        // File Handlers
        handleGeneralFiles,
        removeGeneralDoc,
        handleDrop,
        handleFiles,
        startBatchedUpload,
        removeFolder,
        removeDoc,
        // Submission
        handleSubmit,
        handleSendWhatsApp,
        handleModalClose,
    };
}
