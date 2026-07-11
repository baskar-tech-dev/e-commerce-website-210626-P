<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderReturn;
use App\Models\ReturnItem;
use App\Models\Refund;
use App\Models\Payment;
use App\Models\Order;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class ReturnController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display a listing of return requests.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = OrderReturn::with(['user', 'order']);

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('reason')) {
                $query->where('reason', $request->input('reason'));
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('return_number', 'like', "%{$search}%")
                      ->orWhereHas('order', function ($oq) use ($search) {
                          $oq->where('order_number', 'like', "%{$search}%");
                      })
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            $perPage = $request->input('per_page', 15);
            $returns = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Return requests loaded successfully',
                'data' => $returns->items(),
                'meta' => [
                    'current_page' => $returns->currentPage(),
                    'last_page' => $returns->lastPage(),
                    'per_page' => $returns->perPage(),
                    'total' => $returns->total(),
                ]
            ]);
        } catch (Exception $e) {
            Log::error('ReturnController@index failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load return requests',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Display detailed return request view.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $returnRequest = OrderReturn::with([
                'user',
                'order.items',
                'items.orderItem',
                'items.variant.product',
                'refund'
            ])->find($id);

            if (!$returnRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Return request not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Return details loaded successfully',
                'data' => $returnRequest
            ]);
        } catch (Exception $e) {
            Log::error('ReturnController@show failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load return details',
                'error_code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Update the status of a return request.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:approved,rejected,pickup_scheduled,picked_up,qc_passed,qc_failed',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:500',
            'qc_items' => 'nullable|array', // e.g. [{item_id: 1, qc_status: 'passed', qc_notes: 'notes'}]
        ]);

        DB::beginTransaction();
        try {
            $returnRequest = OrderReturn::find($id);
            if (!$returnRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Return request not found',
                ], 404);
            }

            $currentStatus = $returnRequest->status;
            $newStatus = $validated['status'];

            // Simple transition validations
            if ($newStatus === 'approved' || $newStatus === 'rejected') {
                if ($currentStatus !== 'requested') {
                    throw new Exception("Only requested returns can be approved or rejected.");
                }
                if ($newStatus === 'approved') {
                    $returnRequest->approved_at = now();
                } else {
                    $returnRequest->rejected_at = now();
                    $returnRequest->rejection_reason = $validated['rejection_reason'];
                }
            } elseif ($newStatus === 'pickup_scheduled') {
                if ($currentStatus !== 'approved') {
                    throw new Exception("Returns must be approved before scheduling pickup.");
                }
                $returnRequest->pickup_scheduled_at = now();
            } elseif ($newStatus === 'picked_up') {
                if ($currentStatus !== 'pickup_scheduled') {
                    throw new Exception("Pickup must be scheduled before marking as picked up.");
                }
                $returnRequest->picked_up_at = now();
            } elseif ($newStatus === 'qc_passed' || $newStatus === 'qc_failed') {
                if ($currentStatus !== 'picked_up') {
                    throw new Exception("Returns must be picked up before performing QC check.");
                }
                $returnRequest->qc_completed_at = now();

                // Process item level QC statuses
                if (!empty($validated['qc_items'])) {
                    foreach ($validated['qc_items'] as $qcItem) {
                        $retItem = ReturnItem::where('return_id', $id)
                            ->where('id', $qcItem['item_id'])
                            ->first();
                        
                        if ($retItem) {
                            $retItem->update([
                                'qc_status' => $qcItem['qc_status'],
                                'qc_notes' => $qcItem['qc_notes'] ?? null,
                            ]);

                            // Return stock to inventory if QC passed
                            if ($qcItem['qc_status'] === 'passed') {
                                $this->inventoryService->postLedgerEntry(
                                    $retItem->product_variant_id,
                                    'RETURN',
                                    'IN',
                                    $retItem->quantity,
                                    null, // $unitCost
                                    'ReturnItem',
                                    $retItem->id
                                );
                            }
                        }
                    }
                }
            }

            $returnRequest->status = $newStatus;
            
            // Auto calculate tentative refund amount if approved
            if ($newStatus === 'approved' || $newStatus === 'qc_passed') {
                $refundTotal = 0;
                foreach ($returnRequest->items as $item) {
                    // snapped price * quantity
                    $refundTotal += ($item->orderItem->unit_price * $item->quantity);
                }
                $returnRequest->refund_amount = $refundTotal;
            }

            $returnRequest->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Return request status updated to {$newStatus} successfully",
                'data' => $returnRequest
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ReturnController@updateStatus failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Add administrative note.
     */
    public function addAdminNote(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'note' => 'required|string|max:1000'
        ]);

        try {
            $returnRequest = OrderReturn::find($id);
            if (!$returnRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Return request not found',
                ], 404);
            }

            $timeStr = now()->format('Y-m-d H:i:s');
            $userEmail = auth()->user()->email ?? 'Admin Staff';
            $formattedNote = "[{$timeStr} - {$userEmail}]: " . $validated['note'];

            $returnRequest->admin_notes = trim($returnRequest->admin_notes . "\n" . $formattedNote);
            $returnRequest->save();

            return response()->json([
                'success' => true,
                'message' => 'Internal note recorded successfully',
                'data' => $returnRequest
            ]);
        } catch (Exception $e) {
            Log::error('ReturnController@addAdminNote failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to log administrative comment',
            ], 500);
        }
    }

    /**
     * Process a refund for approved returns.
     */
    public function processRefund(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $returnRequest = OrderReturn::with('order.payments')->find($id);
            if (!$returnRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Return request not found',
                ], 404);
            }

            if (!in_array($returnRequest->status, ['qc_passed', 'approved'])) {
                throw new Exception("Refunds can only be processed for approved or QC-passed returns.");
            }

            // Find or create original payment record
            $payment = $returnRequest->order->payments()->first();
            if (!$payment) {
                // Generate cod mock payment
                $payment = Payment::create([
                    'order_id' => $returnRequest->order_id,
                    'gateway' => $returnRequest->order->payment_method === 'cod' ? 'cod' : 'razorpay',
                    'method' => $returnRequest->order->payment_method,
                    'amount' => $returnRequest->order->grand_total,
                    'status' => 'captured',
                    'paid_at' => now(),
                ]);
            }

            // Create refund record
            $refund = Refund::create([
                'payment_id' => $payment->id,
                'order_id' => $returnRequest->order_id,
                'return_id' => $returnRequest->id,
                'gateway_refund_id' => 'REF-' . strtoupper(Str::random(10)),
                'amount' => $validated['amount'],
                'reason' => $validated['reason'],
                'status' => 'processed',
                'processed_at' => now(),
            ]);

            // Update parent return status
            $returnRequest->status = 'refunded';
            $returnRequest->refund_amount = $validated['amount'];
            $returnRequest->save();

            // Update parent order and payment statuses
            $order = $returnRequest->order;
            $order->status = 'returned';
            $order->payment_status = 'refunded';
            $order->save();

            $payment->status = 'refunded';
            $payment->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'data' => [
                    'return' => $returnRequest,
                    'refund' => $refund,
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ReturnController@processRefund failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
