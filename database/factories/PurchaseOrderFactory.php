<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_name' => $this->faker->company(),
            'supplier_contact' => $this->faker->safeEmail(),
            'status' => 'draft',
            'total_amount' => $this->faker->randomFloat(2, 1000, 50000),
            'notes' => $this->faker->sentence(),
            'ordered_at' => null,
            'received_at' => null,
            'created_by' => User::factory(),
        ];
    }
}
