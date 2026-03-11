import { useRouter } from "vue-router";
import { useToast } from "./useToast";
import { useAuth } from "./useAuth";
import { useCaseMasters } from "./useCaseMasters";
import { useCaseFileUpload } from "./useCaseFileUpload";

import { useCaseReportState } from "./case-report/useCaseReportState";
import { useCaseReportUtils } from "./case-report/useCaseReportUtils";
import { useCaseReportInvoice } from "./case-report/useCaseReportInvoice";
import { useCaseReportPayment } from "./case-report/useCaseReportPayment";
import { useCaseReportValidation } from "./case-report/useCaseReportValidation";
import { useCaseReportApi } from "./case-report/useCaseReportApi";

export function useCaseReportForm(isEdit = false) {
    const router = useRouter();
    const { addToast } = useToast();
    const { user: authUser } = useAuth();

    // --- Master Data Composable ---
    const {
        patients,
        referers,
        scanTypes,
        branches,
        paymentMethods,
        discounts,
        fetchMasters: fetchMastersBase,
        fetchNextCaseId: fetchNextIdBase,
        fetchNextInvoiceNo: fetchNextInvoiceNoBase,
        fetchNextPaymentId: fetchNextPaymentIdBase
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

    // 1. State
    const state = useCaseReportState(isEdit, authUser, branches);
    const {
        loading, fetching, error,
        isWhatsappModalOpen, reportForWhatsapp, sendingWhatsapp, initialRecipients,
        form, deletedInvoiceItemIds, deletedCaseReportItemIds,
        loggedInUserIsSuperAdmin, filteredBranches
    } = state;

    // 2. Utils
    const utils = useCaseReportUtils(
        form, patients, referers, scanTypes, fetching, deletedCaseReportItemIds, isWhatsappModalOpen, router
    );
    const { getScans, addItem, removeItem, handleModalClose, setupFieldWatchers } = utils;
    setupFieldWatchers(); // Initialize watchers

    // 3. Invoice
    const invoice = useCaseReportInvoice(form, discounts, deletedInvoiceItemIds);
    const { subTotal, totalAmount, resolveItemKey, addInvoiceItem, removeInvoiceItem, setupInvoiceSyncWatchers } = invoice;
    setupInvoiceSyncWatchers(fetching); // Initialize watcher

    // 4. Payment
    const payment = useCaseReportPayment(form, totalAmount);
    const { totalPaid, addPaymentRow, removePaymentRow, setupPaymentWatchers } = payment;
    setupPaymentWatchers(); // Initialize watcher

    // 5. Validation
    const { validateForm } = useCaseReportValidation();

    // Wrapper methods to pass 'form' context for File Handling
    const fetchNextCaseId = () => fetchNextIdBase(form);
    const handleGeneralFiles = (e) => handleGeneralFilesBase(e, form, form.id);
    const removeGeneralDoc = (idx) => removeGeneralDocBase(idx, form);
    const removeDoc = (iIdx, dIdx) => removeDocBase(iIdx, dIdx, form);
    const removeFolder = (idx, name) => removeFolderBase(idx, name, form);
    const handleFiles = (e, idx) => handleFilesBase(e, idx, form);
    const handleDrop = (e, idx) => handleDropBase(e, idx, form);
    const startBatchedUpload = () => startBatchedUploadBase(form);
    const getUniqueFolders = (docs, ret) => getUniqueFoldersBase(docs, ret);
    const fetchNextInvoiceNo = () => fetchNextInvoiceNoBase(form);
    const fetchNextPaymentId = () => fetchNextPaymentIdBase(form);

    // 6. API
    const api = useCaseReportApi(
        form, loading, fetching, error, authUser, addToast, router, isEdit,
        patients, referers, fetchNextPaymentId, fetchNextCaseId, fetchNextInvoiceNo,
        fetchMastersBase, subTotal, totalAmount, totalPaid,
        deletedCaseReportItemIds, deletedInvoiceItemIds, resolveItemKey,
        reportForWhatsapp, sendingWhatsapp, isWhatsappModalOpen, handleModalClose,
        validateForm
    );
    const { fetchMasters, fetchCaseReport, handleSubmit, handleSendWhatsApp } = api;

    return {
        // State
        loading, fetching, error, form, patients, referers, scanTypes,
        processingGeneral, uploadModal, isWhatsappModalOpen, reportForWhatsapp,
        sendingWhatsapp, initialRecipients, branches, paymentMethods, discounts,
        loggedInUserIsSuperAdmin, filteredBranches, 

        // Computed totals
        subTotal, totalAmount, totalPaid,

        // Core Actions
        fetchMasters, fetchCaseReport, handleSubmit, handleSendWhatsApp, handleModalClose,
        
        // Items and scans
        getScans, addItem, removeItem, 
        
        // Invoices
        addInvoiceItem, removeInvoiceItem,
        
        // Payments
        addPaymentRow, removePaymentRow, 

        // File Operations
        getFileName, handleGeneralFiles, removeGeneralDoc, handleDrop, handleFiles,
        startBatchedUpload, removeFolder, removeDoc, getUniqueFolders,
        
        // Master Id generators
        fetchNextInvoiceNo, fetchNextPaymentId
    };
}
