<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $orders = $this->orders ? $this->orders->sortByDesc('created_at') : $this->orders()->orderBy('created_at', 'desc')->get();

        $totalPurchases = $orders->count();
        $paidCount = $orders->where('payment_status', 'paid')->count();
        $pendingCount = $orders->where('payment_status', 'pending')->count();
        $failedCount = $orders->where('payment_status', 'failed')->count();
        $refundedCount = $orders->whereIn('payment_status', ['refunded', 'partial_refunded'])->count();

        $totalSpent = round((float) $orders->where('status', '!=', 'cancelled')->sum('grand_total'), 2);
        $paidSpent = round((float) $orders->where('payment_status', 'paid')->where('status', '!=', 'cancelled')->sum('grand_total'), 2);

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'stats' => [
                'total_purchases' => $totalPurchases,
                'paid_count' => $paidCount,
                'pending_count' => $pendingCount,
                'failed_count' => $failedCount,
                'refunded_count' => $refundedCount,
                'total_spent' => $totalSpent,
                'paid_spent' => $paidSpent,
            ],
            'profile' => [
                'date_of_birth' => $this->customerProfile ? ($this->customerProfile->date_of_birth ? $this->customerProfile->date_of_birth->format('Y-m-d') : null) : null,
                'gender' => $this->customerProfile ? $this->customerProfile->gender : null,
                'total_orders' => $totalPurchases,
                'paid_count' => $paidCount,
                'total_spent' => number_format($totalSpent, 2, '.', ''),
                'paid_spent' => number_format($paidSpent, 2, '.', ''),
                'last_order_at' => $this->customerProfile ? $this->customerProfile->last_order_at : null,
                'notes' => $this->customerProfile ? $this->customerProfile->notes : null,
                'email_subscribed' => $this->customerProfile ? $this->customerProfile->email_subscribed : false,
                'sms_subscribed' => $this->customerProfile ? $this->customerProfile->sms_subscribed : false,
            ],
            'addresses' => AddressResource::collection($this->whenLoaded('addresses')),
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_items' => $order->total_items ?: 1,
                    'grand_total' => (float) $order->grand_total,
                    'payment_status' => $order->payment_status,
                    'payment_method' => $order->payment_method ?: 'ONLINE',
                    'status' => $order->status,
                    'status_label' => $order->status_label ?? ucfirst(str_replace('_', ' ', $order->status)),
                    'date' => $order->created_at ? $order->created_at->format('M d, Y, h:i A') : '—',
                ];
            })->values(),
        ];
    }
}
