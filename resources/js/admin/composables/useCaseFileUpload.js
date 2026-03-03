import { ref, reactive } from "vue";
import axios from "axios";
import JSZip from "jszip";

export function useCaseFileUpload(addToast) {
    const processingGeneral = ref(false);

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
        pendingFiles: [],
        pendingItemIndex: null,
    });

    const getFileName = (path) => {
        if (!path) return "";
        return path.split("/").pop();
    };

    const getUniqueFolders = (documents, returnRootFiles = false) => {
        if (!documents || documents.length === 0) return [];
        const folders = {};
        const rootFiles = [];
        documents.forEach((doc) => {
            const path = doc.path || doc;
            if (path) {
                const parts = path.split("/");
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
        return Object.keys(folders).map((name) => ({ name, count: folders[name] }));
    };

    // --- General Document Handling ---
    const handleGeneralFiles = async (event, form, caseReportId = null) => {
        const files = Array.from(event.target.files);
        if (files.length === 0) return;
        event.target.value = "";
        processingGeneral.value = true;
        try {
            for (const file of files) {
                const formData = new FormData();
                formData.append("file", file);
                formData.append("type", "document");
                if (caseReportId) {
                    formData.append("case_report_id", caseReportId);
                }
                const response = await axios.post("/api/v1/files/upload", formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                });
                if (response.data.success) {
                    form.documents.push({ name: response.data.name, path: response.data.path });
                }
            }
        } catch (err) {
            console.error("Upload failed", err);
            addToast({ title: "Error", description: "Failed to upload document.", variant: "error" });
        } finally {
            processingGeneral.value = false;
        }
    };

    const removeGeneralDoc = async (index, form) => {
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

    // --- DICOM / Folder Handling ---
    const removeFolder = async (index, folderName, form) => {
        const item = form.items[index];
        if (!folderName) return;
        try {
            await axios.delete("/api/v1/files/delete", { data: { path: `app/case-reports/${folderName}` } });
        } catch (e) {
            console.error("Failed to delete folder from server", e);
        }
        item.documents = item.documents.filter((doc) => {
            const path = doc.path || doc;
            return !path.includes(`/case-reports/${folderName}/`);
        });
    };

    const removeDoc = async (itemIndex, docIndex, form) => {
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

    const scanFiles = async (items) => {
        const files = [];
        const traverse = (entry, path = "") => {
            return new Promise((resolve) => {
                if (entry.isFile) {
                    entry.file((file) => { file.startPath = path + file.name; files.push(file); resolve(); });
                } else if (entry.isDirectory) {
                    const dirReader = entry.createReader();
                    const readEntries = () => {
                        dirReader.readEntries(async (entries) => {
                            if (entries.length === 0) { resolve(); } else {
                                await Promise.all(entries.map((e) => traverse(e, path + entry.name + "/")));
                                readEntries();
                            }
                        });
                    };
                    readEntries();
                } else { resolve(); }
            });
        };
        await Promise.all(items.map((item) => { const entry = item.webkitGetAsEntry(); return entry ? traverse(entry) : Promise.resolve(); }));
        return files;
    };

    const uploadDicomFiles = async (files, index, form, isUsingModal = false, caseReportId = null) => {
        if (files.length === 0) return;
        const item = form.items[index];
        item.processing = true;
        try {
            const folders = {};
            const rootFiles = [];
            for (const file of files) {
                const relPath = file.startPath || file.webkitRelativePath;
                if (relPath) {
                    const parts = relPath.split("/");
                    if (parts.length > 1) { const fn = parts[0]; if (!folders[fn]) folders[fn] = []; folders[fn].push(file); }
                    else { rootFiles.push(file); }
                } else { rootFiles.push(file); }
            }

            for (let i = 0; i < rootFiles.length; i++) {
                const file = rootFiles[i];
                if (isUsingModal) { uploadModal.currentFileName = file.name; uploadModal.progress = (i / files.length) * 100; }
                const fd = new FormData(); fd.append("file", file); fd.append("type", "dicom");
                if (caseReportId) {
                    fd.append("case_report_id", caseReportId);
                }
                const res = await axios.post("/api/v1/files/upload", fd, { headers: { "Content-Type": "multipart/form-data" } });
                if (res.data.success) item.documents.push({ name: res.data.name, path: res.data.path });
            }

            let processedCount = rootFiles.length;
            for (const folderName of Object.keys(folders)) {
                const folderFiles = folders[folderName];
                const zip = new JSZip();
                folderFiles.forEach(file => {
                    const relPath = file.startPath || file.webkitRelativePath;
                    let zipPath = file.name;
                    if (relPath) { const parts = relPath.split('/'); if (parts.length > 1) zipPath = parts.slice(1).join('/'); }
                    zip.file(zipPath, file, { compression: "DEFLATE", compressionOptions: { level: 9 } });
                });
                if (isUsingModal) uploadModal.currentFileName = `Compressing ${folderName} (${folderFiles.length} files)...`;
                const zipBlob = await zip.generateAsync({ type: "blob", compression: "DEFLATE", compressionOptions: { level: 9 } });
                const zipFile = new File([zipBlob], `${folderName}.zip`, { type: "application/zip" });
                if (isUsingModal) uploadModal.currentFileName = `Uploading ${folderName} (${folderFiles.length} files)...`;
                const fd = new FormData(); fd.append("file", zipFile); fd.append("type", "dicom");
                if (caseReportId) {
                    fd.append("case_report_id", caseReportId);
                }
                const res = await axios.post("/api/v1/files/upload", fd, {
                    headers: { "Content-Type": "multipart/form-data" },
                    onUploadProgress: (pe) => {
                        if (isUsingModal) {
                            const segmentWeight = folderFiles.length / files.length;
                            uploadModal.progress = Math.min(100, (processedCount / files.length) * 100 + (pe.loaded / pe.total) * segmentWeight * 100);
                        }
                    }
                });
                if (res.data.success) item.documents.push({ name: res.data.name, path: res.data.path });
                processedCount += folderFiles.length;
                if (isUsingModal) uploadModal.progress = (processedCount / files.length) * 100;
            }
        } catch (err) {
            console.error("DICOM Upload failed", err);
            const msg = err.message || "Failed to upload DICOM file. Please try again.";
            if (isUsingModal) { uploadModal.state = "alert"; uploadModal.variant = "danger"; uploadModal.title = "Upload Error"; uploadModal.description = msg; return; }
            addToast({ title: "Error", description: msg, variant: "error" });
        } finally { item.processing = false; }
    };

    const handleFiles = async (event, index, form) => {
        const files = Array.from(event.target.files);
        if (files.length === 0) return;
        event.target.value = "";
        if (files.length > 1 || event.target.webkitdirectory) {
            uploadModal.pendingFiles = files;
            uploadModal.pendingItemIndex = index;
            uploadModal.fileCount = files.length;
            uploadModal.state = "confirm";
            uploadModal.isOpen = true;
        } else {
            await uploadDicomFiles(files, index, form, false, form.id);
        }
    };

    const handleDrop = async (event, index, form) => {
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
            await uploadDicomFiles(files, index, form, false, form.id);
        }
    };

    const startBatchedUpload = async (form) => {
        const files = uploadModal.pendingFiles;
        const index = uploadModal.pendingItemIndex;
        uploadModal.state = "uploading";
        uploadModal.progress = 0;
        uploadModal.totalFiles = files.length;
        await uploadDicomFiles(files, index, form, true, form.id);
        uploadModal.isOpen = false;
    };

    return {
        processingGeneral,
        uploadModal,
        getFileName,
        getUniqueFolders,
        handleGeneralFiles,
        removeGeneralDoc,
        removeDoc,
        removeFolder,
        handleFiles,
        handleDrop,
        startBatchedUpload,
    };
}
