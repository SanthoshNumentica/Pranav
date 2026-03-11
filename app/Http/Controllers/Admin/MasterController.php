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
    public function discounts(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('discounts-list'), 403);
        $query = \App\Models\Discount::with(['addedByUser', 'modifiedByUser'])->orderBy('name');

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

    public function storeDiscount(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('discounts-create'), 403);
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('discounts', 'name')->whereNull('deleted_at')
            ],
            'percentage' => 'required|numeric|min:0|max:100'
        ]);

        $discount = \App\Models\Discount::create([
            'name' => $request->name,
            'percentage' => $request->percentage,
            'status' => 'active',
            'added_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Discount created successfully',
            'data' => $discount
        ]);
    }

    public function updateDiscount(Request $request, $id): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('discounts-edit'), 403);
        $discount = \App\Models\Discount::findOrFail($id);
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('discounts', 'name')->ignore($id)->whereNull('deleted_at')
            ],
            'percentage' => 'required|numeric|min:0|max:100'
        ]);

        $discount->update([
            'name' => $request->name,
            'percentage' => $request->percentage,
            'modified_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Discount updated successfully',
            'data' => $discount
        ]);
    }

    public function destroyDiscount($id): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('discounts-delete'), 403);
        $discount = \App\Models\Discount::findOrFail($id);
        $discount->update(['status' => 'inactive', 'modified_by' => auth()->id()]);
        $discount->delete(); // Soft delete

        return response()->json([
            'success' => true,
            'message' => 'Discount deleted successfully'
        ]);
    }

    public function updateDiscountStatus(Request $request, $id): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('discounts-edit'), 403);
        $discount = \App\Models\Discount::findOrFail($id);
        $request->validate(['status' => 'required|in:active,inactive']);
        $discount->update([
            'status' => $request->status,
            'modified_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $discount
        ]);
    }

    public function refererTypes(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('referer-types-list'), 403);
        $query = \App\Models\RefererType::with(['addedByUser', 'modifiedByUser'])->orderBy('name');

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

    public function storeRefererType(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('referer-types-create'), 403);
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('referer_types', 'name')->whereNull('deleted_at')
            ]
        ]);

        $refererType = \App\Models\RefererType::create([
            'name' => $request->name,
            'status' => 'active',
            'added_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Referer type created successfully',
            'data' => $refererType
        ]);
    }

    public function updateRefererType(Request $request, $id): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('referer-types-edit'), 403);
        $refererType = \App\Models\RefererType::findOrFail($id);
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('referer_types', 'name')->ignore($id)->whereNull('deleted_at')
            ]
        ]);

        $refererType->update([
            'name' => $request->name,
            'modified_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Referer type updated successfully',
            'data' => $refererType
        ]);
    }

    public function destroyRefererType($id): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('referer-types-delete'), 403);
        $refererType = \App\Models\RefererType::findOrFail($id);
        $refererType->update(['status' => 'inactive', 'modified_by' => auth()->id()]);
        $refererType->delete(); // Soft delete

        return response()->json([
            'success' => true,
            'message' => 'Referer type deleted successfully'
        ]);
    }

    public function updateRefererTypeStatus(Request $request, $id): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('referer-types-edit'), 403);
        $refererType = \App\Models\RefererType::findOrFail($id);
        $request->validate(['status' => 'required|in:active,inactive']);
        $refererType->update([
            'status' => $request->status,
            'modified_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $refererType
        ]);
    }
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
            'scans.*.name' => 'string|max:255',
            'scans.*.amount' => 'nullable|numeric|min:0'
        ]);

        $scanType = ScanType::create([
            'name' => $request->name,
            'status' => 'active'
        ]);

        if ($request->has('scans')) {
            foreach ($request->scans as $scanData) {
                if (!empty($scanData['name'])) {
                    $scanType->scans()->create([
                        'name' => $scanData['name'],
                        'amount' => $scanData['amount'] ?? null
                    ]);
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
            'scans.*.name' => 'required|string|max:255',
            'scans.*.amount' => 'nullable|numeric|min:0'
        ]);

        $scanType->update(['name' => $request->name]);

        // Handing multiple scans
        if ($request->has('scans')) {
            $existingScanIds = collect($request->scans)->pluck('id')->filter()->toArray();
            $scanType->scans()->whereNotIn('id', $existingScanIds)->delete();

            foreach ($request->scans as $scanData) {
                if (isset($scanData['id'])) {
                    \App\Models\Scan::where('id', $scanData['id'])->update([
                        'name' => $scanData['name'],
                        'amount' => $scanData['amount'] ?? null
                    ]);
                } else {
                    $scanType->scans()->create([
                        'name' => $scanData['name'],
                        'amount' => $scanData['amount'] ?? null
                    ]);
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

    public function referers(Request $request): JsonResponse
    {
        $query = \App\Models\Referer::with(['addedByUser', 'modifiedByUser'])->orderBy('name');
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

    public function paymentMethods(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('payment-methods-list'), 403);
        try {
            $query = \App\Models\PaymentMethod::with(['addedByUser', 'modifiedByUser'])->orderBy('name');

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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function storePaymentMethod(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('payment-methods-create'), 403);
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('payment_methods', 'name')->whereNull('deleted_at')
            ]
        ]);

        $paymentMethod = \App\Models\PaymentMethod::create([
            'name' => $request->name,
            'status' => 'active',
            'added_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment method created successfully',
            'data' => $paymentMethod
        ]);
    }

    public function updatePaymentMethod(Request $request, $id): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('payment-methods-edit'), 403);
        $paymentMethod = \App\Models\PaymentMethod::findOrFail($id);
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('payment_methods', 'name')->ignore($id)->whereNull('deleted_at')
            ]
        ]);

        $paymentMethod->update([
            'name' => $request->name,
            'modified_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment method updated successfully',
            'data' => $paymentMethod
        ]);
    }

    public function destroyPaymentMethod($id): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('payment-methods-delete'), 403);
        $paymentMethod = \App\Models\PaymentMethod::findOrFail($id);
        $paymentMethod->update(['status' => 'inactive', 'modified_by' => auth()->id()]);
        $paymentMethod->delete(); // Soft delete

        return response()->json([
            'success' => true,
            'message' => 'Payment method deleted successfully'
        ]);
    }

    public function updatePaymentMethodStatus(Request $request, $id): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('payment-methods-edit'), 403);
        $paymentMethod = \App\Models\PaymentMethod::findOrFail($id);
        $request->validate(['status' => 'required|in:active,inactive']);
        $paymentMethod->update([
            'status' => $request->status,
            'modified_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $paymentMethod
        ]);
    }

    public function cities(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('cities-list'), 403);
        $query = \App\Models\City::with(['addedByUser', 'modifiedByUser'])->orderBy('name');

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

    public function storeCity(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('cities-create'), 403);
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('cities', 'name')->whereNull('deleted_at')
            ]
        ]);

        $city = \App\Models\City::create([
            'name' => $request->name,
            'status' => 'active',
            'added_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'City created successfully',
            'data' => $city
        ]);
    }

    public function updateCity(Request $request, $id): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('cities-edit'), 403);
        $city = \App\Models\City::findOrFail($id);
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('cities', 'name')->ignore($id)->whereNull('deleted_at')
            ]
        ]);

        $city->update([
            'name' => $request->name,
            'modified_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'City updated successfully',
            'data' => $city
        ]);
    }

    public function destroyCity($id): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('cities-delete'), 403);
        $city = \App\Models\City::findOrFail($id);
        $city->update(['status' => 'inactive', 'modified_by' => auth()->id()]);
        $city->delete(); // Soft delete

        return response()->json([
            'success' => true,
            'message' => 'City deleted successfully'
        ]);
    }

    public function updateCityStatus(Request $request, $id): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('cities-edit'), 403);
        $city = \App\Models\City::findOrFail($id);
        $request->validate(['status' => 'required|in:active,inactive']);
        $city->update([
            'status' => $request->status,
            'modified_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $city
        ]);
    }
}
