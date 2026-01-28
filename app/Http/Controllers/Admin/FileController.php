<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Handle asynchronous file upload.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:102400'], // 100MB max
            'type' => ['required', 'in:document,dicom'],
        ]);

        $file = $request->file('file');
        $type = $request->input('type');
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());

        if ($type === 'document') {
            // Validation for documents
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'pdf'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid document type. Only JPG, PNG, and PDF allowed.'
                ], 422);
            }
            $targetDir = public_path('app/documents');
            $publicPath = 'app/documents/' . $filename;
        } else {
            // Validation for DICOM
            if ($extension !== 'dcm') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid DICOM file. Only .dcm extension allowed.'
                ], 422);
            }
            $targetDir = public_path('app/case-reports');
            $publicPath = 'app/case-reports/' . $filename;
        }

        // Ensure directory exists
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $file->move($targetDir, $filename);

        return response()->json([
            'success' => true,
            'path' => $publicPath,
            'name' => $file->getClientOriginalName()
        ]);
    }
}
