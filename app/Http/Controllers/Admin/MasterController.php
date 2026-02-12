<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScanType;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MasterController extends Controller
{
    public function roles(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Role::all(),
        ]);
    }

    public function scans(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => \App\Models\Scan::orderBy('scan_type_id')->paginate(10),
        ]);
    }
    public function scanTypes(Request $request): JsonResponse
    {
        $query = ScanType::with(['scans', 'addedByUser', 'modifiedByUser'])->orderBy('name');

        if ($request->has('status')) {
            if ($request->status === 'inactive') {
                $query->withTrashed()->where('status', 'inactive');
            } elseif ($request->status !== 'all') {
                $query->where('status', $request->status);
            }
        }

        $data = $request->has('nopaginate') ? $query->get() : $query->paginate(10);
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function storeScanType(Request $request): JsonResponse
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('scan_types', 'name')->whereNull('deleted_at')
            ],
            'scans' => 'array',
            'scans.*' => 'string|max:255'
        ]);

        $scanType = ScanType::create([
            'name' => $request->name,
            'status' => 'active'
        ]);

        if ($request->has('scans')) {
            foreach ($request->scans as $scanName) {
                if (!empty($scanName)) {
                    $scanType->scans()->create(['name' => $scanName]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Scan type created successfully',
            'data' => $scanType->load('scans')
        ]);
    }

    public function updateScanType(Request $request, $id): JsonResponse
    {
        $scanType = ScanType::findOrFail($id);
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('scan_types', 'name')->ignore($id)->whereNull('deleted_at')
            ],
            'scans' => 'array',
            'scans.*.id' => 'nullable|exists:scans,id',
            'scans.*.name' => 'required|string|max:255'
        ]);

        $scanType->update(['name' => $request->name]);

        // Handing multiple scans
        if ($request->has('scans')) {
            $existingScanIds = collect($request->scans)->pluck('id')->filter()->toArray();
            $scanType->scans()->whereNotIn('id', $existingScanIds)->delete();

            foreach ($request->scans as $scanData) {
                if (isset($scanData['id'])) {
                    \App\Models\Scan::where('id', $scanData['id'])->update(['name' => $scanData['name']]);
                } else {
                    $scanType->scans()->create(['name' => $scanData['name']]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Scan type updated successfully',
            'data' => $scanType->load('scans')
        ]);
    }

    public function destroyScanType($id): JsonResponse
    {
        $scanType = ScanType::findOrFail($id);
        $scanType->update(['status' => 'inactive']);
        $scanType->scans()->delete();
        $scanType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Scan type deleted successfully'
        ]);
    }

    public function updateScanTypeStatus(Request $request, $id): JsonResponse
    {
        $scanType = ScanType::findOrFail($id);
        $request->validate(['status' => 'required|in:active,inactive']);
        $scanType->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $scanType
        ]);
    }

    public function patients(Request $request): JsonResponse
    {
        $query = \App\Models\Patient::with(['addedByUser', 'modifiedByUser'])->orderBy('name');
        if ($request->has('status')) {
            if ($request->status === 'inactive') {
                $query->withTrashed()->where('status', 'inactive');
            } elseif ($request->status !== 'all') {
                $query->where('status', $request->status);
            }
        }
        $data = $request->has('nopaginate') ? $query->get() : $query->paginate(10);
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function doctors(Request $request): JsonResponse
    {
        $query = \App\Models\Doctor::with(['addedByUser', 'modifiedByUser'])->orderBy('name');
        if ($request->has('status')) {
            if ($request->status === 'inactive') {
                $query->withTrashed()->where('status', 'inactive');
            } elseif ($request->status !== 'all') {
                $query->where('status', $request->status);
            }
        }
        $data = $request->has('nopaginate') ? $query->get() : $query->paginate(10);
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function users(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => User::with(['addedByUser', 'modifiedByUser'])->all(),
        ]);
    }

    public function genders(Request $request): JsonResponse
    {
        $query = \App\Models\Gender::with(['addedByUser', 'modifiedByUser'])->orderBy('gender_name');
        if ($request->has('status')) {
            if ($request->status === 'inactive') {
                $query->withTrashed()->where('status', 'inactive');
            } elseif ($request->status !== 'all') {
                $query->where('status', $request->status);
            }
        }
        $data = $request->has('nopaginate') ? $query->get() : $query->paginate(10);
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function storeGender(Request $request): JsonResponse
    {
        $request->validate([
            'gender_name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('genders', 'gender_name')->whereNull('deleted_at')
            ]
        ]);
        $gender = \App\Models\Gender::create($request->all());
        return response()->json(['success' => true, 'message' => 'Gender created', 'data' => $gender]);
    }

    public function updateGender(Request $request, $id): JsonResponse
    {
        $gender = \App\Models\Gender::findOrFail($id);
        $request->validate([
            'gender_name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('genders', 'gender_name')->ignore($id)->whereNull('deleted_at')
            ]
        ]);
        $gender->update($request->all());
        return response()->json(['success' => true, 'message' => 'Gender updated', 'data' => $gender]);
    }

    public function destroyGender($id): JsonResponse
    {
        $gender = \App\Models\Gender::findOrFail($id);
        $gender->update(['status' => 'inactive']);
        $gender->delete();
        return response()->json(['success' => true, 'message' => 'Gender deleted']);
    }

    public function updateGenderStatus(Request $request, $id): JsonResponse
    {
        $gender = \App\Models\Gender::findOrFail($id);
        $request->validate(['status' => 'required|in:active,inactive']);
        $gender->update(['status' => $request->status]);
        return response()->json(['success' => true, 'message' => 'Status updated', 'data' => $gender]);
    }

    public function bloodGroups(Request $request): JsonResponse
    {
        $query = \App\Models\BloodGroup::with(['addedByUser', 'modifiedByUser'])->orderBy('name');
        if ($request->has('status')) {
            if ($request->status === 'inactive') {
                $query->withTrashed()->where('status', 'inactive');
            } elseif ($request->status !== 'all') {
                $query->where('status', $request->status);
            }
        }
        $data = $request->has('nopaginate') ? $query->get() : $query->paginate(10);
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function storeBloodGroup(Request $request): JsonResponse
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('blood_groups', 'name')->whereNull('deleted_at')
            ]
        ]);
        $bg = \App\Models\BloodGroup::create($request->all());
        return response()->json(['success' => true, 'message' => 'Blood group created', 'data' => $bg]);
    }

    public function updateBloodGroup(Request $request, $id): JsonResponse
    {
        $bg = \App\Models\BloodGroup::findOrFail($id);
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('blood_groups', 'name')->ignore($id)->whereNull('deleted_at')
            ]
        ]);
        $bg->update($request->all());
        return response()->json(['success' => true, 'message' => 'Blood group updated', 'data' => $bg]);
    }

    public function destroyBloodGroup($id): JsonResponse
    {
        $bg = \App\Models\BloodGroup::findOrFail($id);
        $bg->update(['status' => 'inactive']);
        $bg->delete();
        return response()->json(['success' => true, 'message' => 'Blood group deleted']);
    }

    public function updateBloodGroupStatus(Request $request, $id): JsonResponse
    {
        $bg = \App\Models\BloodGroup::findOrFail($id);
        $request->validate(['status' => 'required|in:active,inactive']);
        $bg->update(['status' => $request->status]);
        return response()->json(['success' => true, 'message' => 'Status updated', 'data' => $bg]);
    }

    public function titles(Request $request): JsonResponse
    {
        $query = \App\Models\Title::with(['addedByUser', 'modifiedByUser'])->orderBy('title_name');
        if ($request->has('status')) {
            if ($request->status === 'inactive') {
                $query->withTrashed()->where('status', 'inactive');
            } elseif ($request->status !== 'all') {
                $query->where('status', $request->status);
            }
        }
        $data = $request->has('nopaginate') ? $query->get() : $query->paginate(10);
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function storeTitle(Request $request): JsonResponse
    {
        $request->validate([
            'title_name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('titles', 'title_name')->whereNull('deleted_at')
            ]
        ]);
        $title = \App\Models\Title::create($request->all());
        return response()->json(['success' => true, 'message' => 'Title created', 'data' => $title]);
    }

    public function updateTitle(Request $request, $id): JsonResponse
    {
        $title = \App\Models\Title::findOrFail($id);
        $request->validate([
            'title_name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('titles', 'title_name')->ignore($id)->whereNull('deleted_at')
            ]
        ]);
        $title->update($request->all());
        return response()->json(['success' => true, 'message' => 'Title updated', 'data' => $title]);
    }

    public function destroyTitle($id): JsonResponse
    {
        $title = \App\Models\Title::findOrFail($id);
        $title->update(['status' => 'inactive']);
        $title->delete();
        return response()->json(['success' => true, 'message' => 'Title deleted']);
    }

    public function updateTitleStatus(Request $request, $id): JsonResponse
    {
        $title = \App\Models\Title::findOrFail($id);
        $request->validate(['status' => 'required|in:active,inactive']);
        $title->update(['status' => $request->status]);
        return response()->json(['success' => true, 'message' => 'Status updated', 'data' => $title]);
    }
}
