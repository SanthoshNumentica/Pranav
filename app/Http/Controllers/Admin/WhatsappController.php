<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\WhatsAppService;

class WhatsappController extends Controller
{
    /**
     * Display a listing of whatsapp logs.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);

        $query = WhatsappLog::latest();

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('recipient_mobile_no', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate($limit),
        ]);
    }

    public function resend($id, WhatsAppService $whatsAppService): JsonResponse
    {
        $log = WhatsappLog::find($id);
        if (!$log) {
            return response()->json(['success' => false, 'message' => 'Log not found'], 404);
        }

        $data = ['id' => $id];
        $result = $whatsAppService->send($data);

        return response()->json(['success' => $result['status'], 'message' => $result['message']]);
    }

    public function count(): JsonResponse
    {
        $count = WhatsappLog::whereDate('created_at', today())->count();
        return response()->json(['success' => true, 'count' => $count]);
    }
}
