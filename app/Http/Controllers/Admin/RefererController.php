<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RefererController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Referer::with(['refererType', 'title', 'addedByUser', 'modifiedByUser'])->orderBy('name');

        if ($request->has('status')) {
            if ($request->status === 'inactive') {
                $query->withTrashed()->where('status', 'inactive');
            } elseif ($request->status !== 'all') {
                $query->where('status', $request->status);
            }
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile_no', 'like', "%{$search}%")
                    ->orWhere('place', 'like', "%{$search}%");
            });
        }

        $data = $request->has('nopaginate') ? $query->get() : $query->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'referer_type_id' => 'required|exists:referer_types,id',
            'title_id' => 'nullable|exists:titles,id',
            'name' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:20',
            'email_id' => 'nullable|email|max:255',
            'place' => 'nullable|string|max:255',
            'hospital_name' => 'nullable|string|max:255',
            'hospital_id' => 'nullable|string|max:100', // Adjusted max length
        ]);

        $referer = Referer::create([
            'referer_type_id' => $request->referer_type_id,
            'title_id' => $request->title_id,
            'name' => $request->name,
            'mobile_no' => $request->mobile_no,
            'email_id' => $request->email_id,
            'place' => $request->place,
            'hospital_name' => $request->hospital_name,
            'hospital_id' => $request->hospital_id,
            'status' => 'active',
            'added_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Referer created successfully',
            'data' => $referer
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $referer = Referer::findOrFail($id);

        $request->validate([
            'referer_type_id' => 'sometimes|nullable|exists:referer_types,id',
            'title_id' => 'sometimes|nullable|exists:titles,id',
            'name' => 'sometimes|required|string|max:255',
            'mobile_no' => 'sometimes|required|string|max:20',
            'email_id' => 'sometimes|nullable|email|max:255',
            'place' => 'sometimes|nullable|string|max:255',
            'hospital_name' => 'sometimes|nullable|string|max:255',
            'hospital_id' => 'sometimes|nullable|string|max:100',
        ]);

        $updateData = array_filter([
            'modified_by' => auth()->id(),
        ], fn($v) => $v !== null);

        if ($request->has('referer_type_id'))
            $updateData['referer_type_id'] = $request->referer_type_id;
        if ($request->has('title_id'))
            $updateData['title_id'] = $request->title_id;
        if ($request->has('name'))
            $updateData['name'] = $request->name;
        if ($request->has('mobile_no'))
            $updateData['mobile_no'] = $request->mobile_no;
        if ($request->has('email_id'))
            $updateData['email_id'] = $request->email_id;
        if ($request->has('place'))
            $updateData['place'] = $request->place;
        if ($request->has('hospital_name'))
            $updateData['hospital_name'] = $request->hospital_name;
        if ($request->has('hospital_id'))
            $updateData['hospital_id'] = $request->hospital_id;

        $referer->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Referer updated successfully',
            'data' => $referer
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $referer = Referer::findOrFail($id);
        $referer->update(['status' => 'inactive', 'modified_by' => auth()->id()]);
        $referer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Referer deleted successfully'
        ]);
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $referer = Referer::findOrFail($id);
        $request->validate(['status' => 'required|in:active,inactive']);

        $referer->update([
            'status' => $request->status,
            'modified_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $referer
        ]);
    }
}
