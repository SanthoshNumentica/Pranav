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
            'file' => ['required', 'file', 'max:2097152'], // 2GB max
            'type' => ['required', 'in:document,dicom'],
            'relative_path' => ['nullable', 'string'],
        ]);

        $file = $request->file('file');
        $type = $request->input('type');
        $relativePath = $request->input('relative_path');
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());

        if ($type === 'document') {
            // Validation for documents (added doc, docx support)
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid document type. Only JPG, PNG, PDF, and Word allowed.'
                ], 422);
            }
            $targetDir = public_path('app/documents');
            $publicPath = 'app/documents/' . $filename;
        } else {
            // Validation for DICOM: Allow .dcm, .zip, or no extension
            if ($extension !== '' && $extension !== 'dcm' && $extension !== 'zip') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid DICOM file format. Allowed: .dcm, .zip'
                ], 422);
            }

            // Determine subdirectory from relative path
            $subDir = '';
            if ($relativePath) {
                // Sanitize path to prevent traversal
                $cleanPath = str_replace('..', '', $relativePath);
                $dirName = dirname($cleanPath);
                // dirname returns '.' if no slash found
                if ($dirName !== '.') {
                    $subDir = '/' . $dirName;
                }
            }

            $targetDir = public_path('app/case-reports' . $subDir);
            $publicPath = 'app/case-reports' . $subDir . '/' . $filename;
        }

        // Ensure directory exists (recursive)
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

    /**
     * Delete an uploaded file.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'path' => ['required', 'string'],
        ]);

        $path = $request->input('path');
        $fullPath = public_path($path);

        // Security check: Ensure the path is within allowed directories and doesn't contain traversal
        $allowedDirs = [
            public_path('app/documents'),
            public_path('app/case-reports'),
        ];

        $isAllowed = false;
        $realPath = realpath($fullPath);

        if ($realPath) {
            foreach ($allowedDirs as $dir) {
                $realDir = realpath($dir);
                if ($realDir && str_starts_with($realPath, $realDir)) {
                    $isAllowed = true;
                    break;
                }
            }
        }

        if (!$isAllowed || !file_exists($fullPath)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found or unauthorized access.'
            ], 404);
        }

        try {
            if (is_dir($fullPath)) {
                // Delete directory and its contents
                // We use standard PHP rmdir after verifying it's within allowed paths
                // But a safer way in Laravel is File::deleteDirectory
                // Since we don't have File facade imported yet, we can use a recursive function or import it.
                // Let's use a simple recursive deletion for now or assume File facade is available if we add use.
                // Actually, let's keep it simple and use a helper or just File facade if available.
                // Given the context, I will add `use Illuminate\Support\Facades\File;` to the top later/separately?
                // No, I can't add imports easily with this tool without replacing the top.
                // I will use a recursive unlink approach safe for this scope.

                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($files as $fileinfo) {
                    $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                    $todo($fileinfo->getRealPath());
                }

                if (rmdir($fullPath)) {
                    return response()->json(['success' => true, 'message' => 'Directory purged.']);
                }
            } elseif (@unlink($fullPath)) {
                return response()->json(['success' => true, 'message' => 'File purged.']);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting file/directory: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to delete content from disk.'
        ], 500);
    }
}
