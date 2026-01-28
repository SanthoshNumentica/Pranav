<?php

namespace App\Services;

use App\Models\CaseReport;
use Illuminate\Support\Facades\Log;

class OrthancService
{
    /**
     * Upload case report documents to Orthanc.
     */
    public function uploadCaseReport(int $caseReportId): void
    {
        try {
            $caseReport = CaseReport::with('items')->findOrFail($caseReportId);

            Log::info("OrthancService: Preparing upload for Case Report #{$caseReportId}");

            foreach ($caseReport->items as $item) {
                if (!empty($item->documents)) {
                    foreach ($item->documents as $docPath) {
                        $this->uploadToOrthanc($docPath, $caseReport, $item);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("OrthancService Error: " . $e->getMessage());
        }
    }

    /**
     * Upload a single file to Orthanc.
     */
    protected function uploadToOrthanc(string $filePath, CaseReport $caseReport, $item): void
    {
        // For now, this is a skeleton. 
        // Logic for actual CURL request to Orthanc /instances endpoint goes here.
        Log::info("OrthancService: Skeleton upload for file: {$filePath}");
    }
}
