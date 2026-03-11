<?php

namespace App\Http\Requests\Admin\CaseReport;

use Illuminate\Foundation\Http\FormRequest;

class StoreCaseReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_details' => ['required', 'array'],
            'patient_details.patient_fk_id' => ['nullable', 'exists:patients,id'],
            'patient_details.title_fk_id' => ['nullable'],
            'patient_details.name' => ['nullable', 'string', 'max:255'],
            'patient_details.mobile_no' => ['nullable', 'string', 'max:20'],
            'patient_details.whatsapp_no' => ['nullable', 'string', 'max:20'],
            'patient_details.gender_fk_id' => ['nullable'],
            'patient_details.place' => ['nullable', 'string', 'max:255'],

            'referer_details' => ['required', 'array'],
            'referer_details.referer_fk_id' => ['nullable', 'exists:referers,id'],
            'referer_details.title_fk_id' => ['nullable'],
            'referer_details.name' => ['nullable', 'string', 'max:255'],
            'referer_details.mobile_no' => ['nullable', 'string', 'max:20'],
            'referer_details.referer_type_id' => ['nullable'],
            'referer_details.hospital_name' => ['nullable', 'string', 'max:255'],

            'case_reports' => ['required', 'array'],
            'case_reports.case_id' => ['required', 'string', 'max:255'],
            'case_reports.branch_fk_id' => ['nullable', 'exists:branches,id'],
            'case_reports.scanning_date' => ['nullable', 'date'],
            'case_reports.check_in' => ['nullable', 'string'],
            'case_reports.is_stat_case' => ['nullable', 'boolean'],
            'case_reports.patient_fk_id' => ['nullable'],
            'case_reports.referer_fk_id' => ['nullable'],
            'case_reports.description' => ['nullable', 'string'],
            'case_reports.documents' => ['nullable', 'array'],
            'case_reports.documents.*' => ['required', 'string'],
            
            'case_reports.case_report_items' => ['required', 'array', 'min:1'],
            'case_reports.case_report_items.*.id' => ['nullable', 'exists:case_report_items,id'],
            'case_reports.case_report_items.*.case_report_item_fk_id' => ['nullable', 'exists:case_report_items,id'],
            'case_reports.case_report_items.*.action' => ['nullable', 'integer', 'in:1,2,3'],
            'case_reports.case_report_items.*.scan_types_fk_id' => ['required_unless:case_reports.case_report_items.*.action,3', 'nullable', 'exists:scan_types,id'],
            'case_reports.case_report_items.*.scans' => ['required_unless:case_reports.case_report_items.*.action,3', 'nullable', 'array', 'min:1'],
            'case_reports.case_report_items.*.scans.*.scan_fk_id' => ['required', 'exists:scans,id'],
            'case_reports.case_report_items.*.scans.*.scan_name' => ['nullable', 'string', 'max:255'],
            'case_reports.case_report_items.*.scans.*.amount' => ['nullable', 'numeric', 'min:0'],
            'case_reports.case_report_items.*.scan_type_id' => ['nullable', 'string', 'max:255'],
            'case_reports.case_report_items.*.documents' => ['nullable', 'array'],
            'case_reports.case_report_items.*.documents.*' => ['required', 'string'],
            'case_reports.case_report_items.*.remarks' => ['nullable', 'string'],
            'case_reports.case_report_items.*.total_amount' => ['nullable', 'numeric', 'min:0'],

            'invoice_details' => ['nullable', 'array'],
            'invoice_details.invoice_id' => ['nullable', 'string'],
            'invoice_details.case_report_fk_id' => ['nullable'],
            'invoice_details.patient_fk_id' => ['nullable'],
            'invoice_details.branch_fk_id' => ['nullable'],
            'invoice_details.sub_total' => ['nullable', 'numeric', 'min:0'],
            'invoice_details.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'invoice_details.discount_fk_id' => ['nullable'],
            'invoice_details.tax_amount' => ['nullable', 'numeric', 'min:0'],
            'invoice_details.total_amount' => ['nullable', 'numeric', 'min:0'],
            'invoice_details.paid_amount' => ['nullable', 'numeric', 'min:0'],
            'invoice_details.status' => ['nullable', 'string'],
            'invoice_details.invoice_date' => ['nullable', 'date'],
            'invoice_details.notes' => ['nullable', 'string'],
            
            'invoice_details.invoice_items' => ['nullable', 'array'],
            'invoice_details.invoice_items.*.id' => ['nullable', 'exists:invoice_items,id'],
            'invoice_details.invoice_items.*.invoice_item_fk_id' => ['nullable', 'exists:invoice_items,id'],
            'invoice_details.invoice_items.*.case_report_item_fk_id' => ['nullable'],
            'invoice_details.invoice_items.*.scan_fk_id' => ['nullable'],
            'invoice_details.invoice_items.*.action' => ['nullable', 'integer', 'in:1,2,3'],
            'invoice_details.invoice_items.*.description' => ['sometimes', 'required_unless:invoice_details.invoice_items.*.action,3', 'nullable', 'string'],
            'invoice_details.invoice_items.*.amount' => ['sometimes', 'required_unless:invoice_details.invoice_items.*.action,3', 'nullable', 'numeric', 'min:0'],

            'payment_details' => ['nullable', 'array'],
            'payment_details.payment_id' => ['nullable', 'string'],
            'payment_details.invoice_fk_id' => ['nullable'],
            'payment_details.payment_date' => ['nullable', 'date'],
            'payment_details.notes' => ['nullable', 'string'],
            'payment_details.payment_details' => ['nullable'],
        ];
    }
}
