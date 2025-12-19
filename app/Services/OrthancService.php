<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\CaseReport;

class OrthancService
{
    protected string $orthancUrl;

    public function __construct()
    {
        $this->orthancUrl = rtrim(config('services.ohif.url'), '/');
    }

    public function uploadCaseReport(int $caseId): ?string
    {
        try {
            $report = CaseReport::with('items')->findOrFail($caseId);

            $documents = [];

            foreach ($report->items as $item) {
                if (is_array($item->documents)) {
                    foreach ($item->documents as $path) {
                        $documents[] = $path;
                    }
                }
            }

            $documents = array_unique($documents);

            if (empty($documents)) {
                throw new \Exception('No DICOM documents found');
            }

            $studyUid = null;

            foreach ($documents as $path) {
                $absolutePath = public_path('storage/' . $path);

                if (!file_exists($absolutePath)) {
                    Log::error('DICOM file not found', ['file' => $absolutePath, 'case_id' => $caseId]);
                    continue; // skip this file
                }

                $dicomData = file_get_contents($absolutePath);
                if ($dicomData === false) {
                    Log::error('Failed to read DICOM file', ['file' => $absolutePath, 'case_id' => $caseId]);
                    continue;
                }

                try {
                    $response = Http::withHeaders([
                        'Content-Type' => 'application/dicom',
                    ])->withBody($dicomData, 'application/dicom')
                        ->post($this->orthancUrl . '/instances');
                    Log::error('upload response', ['data' => $response]);

                    $instanceId = $response->json('ID');
                    $instanceResponse = Http::get($this->orthancUrl . "/instances/{$instanceId}")->json();
                    $parentSeriesId = $instanceResponse['ParentSeries'] ?? null;

                    // After uploading an instance
                    if ($parentSeriesId) {
                        $seriesResponse = Http::get($this->orthancUrl . "/series/{$parentSeriesId}")->json();
                        $parentStudyId = $seriesResponse['ParentStudy'] ?? null;

                        if ($parentStudyId) {
                            $studyResponse = Http::get($this->orthancUrl . "/studies/{$parentStudyId}");
                            $studyUid = $studyResponse->json('MainDicomTags.StudyInstanceUID');
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('HTTP request to Orthanc failed', [
                        'file' => $absolutePath,
                        'error' => $e->getMessage(),
                        'case_id' => $caseId,
                    ]);
                }
            }

            // ✅ SAVE INTO case_reports TABLE
            if ($studyUid) {
                $report->update([
                    'study_instance_uid' => $studyUid,
                ]);
            }

            return $studyUid;
        } catch (\Exception $e) {
            Log::error('Orthanc upload failed', [
                'case_id' => $caseId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
