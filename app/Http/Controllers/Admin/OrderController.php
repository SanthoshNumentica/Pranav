<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rules\Enum;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->listOrders(
            $request->only(['status', 'search']),
            $request->get('limit', 15)
        );

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->getOrder($id);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Update the status of the specified order.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', new Enum(OrderStatus::class)],
        ]);

        $order = $this->orderService->getOrder($id);
        $status = OrderStatus::from($request->status);

        $updatedOrder = $this->orderService->updateStatus($order, $status);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully.',
            'data' => $updatedOrder,
        ]);
    }
}
