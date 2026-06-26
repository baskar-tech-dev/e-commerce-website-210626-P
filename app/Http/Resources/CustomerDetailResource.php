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
            'profile' => [
                'date_of_birth' => $this->customerProfile ? ($this->customerProfile->date_of_birth ? $this->customerProfile->date_of_birth->format('Y-m-d') : null) : null,
                'gender' => $this->customerProfile ? $this->customerProfile->gender : null,
                'total_orders' => $this->customerProfile ? $this->customerProfile->total_orders : 0,
                'total_spent' => $this->customerProfile ? $this->customerProfile->total_spent : '0.00',
                'last_order_at' => $this->customerProfile ? $this->customerProfile->last_order_at : null,
                'notes' => $this->customerProfile ? $this->customerProfile->notes : null,
                'email_subscribed' => $this->customerProfile ? $this->customerProfile->email_subscribed : false,
                'sms_subscribed' => $this->customerProfile ? $this->customerProfile->sms_subscribed : false,
            ],
            'addresses' => AddressResource::collection($this->whenLoaded('addresses')),
        ];
    }
}
