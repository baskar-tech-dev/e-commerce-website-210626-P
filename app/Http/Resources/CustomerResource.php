<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalOrders = $this->total_orders_count ?? ($this->customerProfile ? $this->customerProfile->total_orders : $this->orders()->count());
        $paidOrders = $this->paid_orders_count ?? $this->orders()->where('payment_status', 'paid')->count();
        $paidSpent = isset($this->paid_spent) 
            ? (float) $this->paid_spent 
            : (float) $this->orders()->where('payment_status', 'paid')->where('status', '!=', 'cancelled')->sum('grand_total');

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
            'total_orders' => (int) $totalOrders,
            'paid_orders_count' => (int) $paidOrders,
            'total_spent' => number_format((float) $paidSpent, 2, '.', ''),
            'paid_spent' => number_format((float) $paidSpent, 2, '.', ''),
            'last_order_at' => $this->customerProfile ? $this->customerProfile->last_order_at : null,
            'notes' => $this->customerProfile ? $this->customerProfile->notes : null,
            'email_subscribed' => $this->customerProfile ? $this->customerProfile->email_subscribed : false,
            'sms_subscribed' => $this->customerProfile ? $this->customerProfile->sms_subscribed : false,
        ];
    }
}
