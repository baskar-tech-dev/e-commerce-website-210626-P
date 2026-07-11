<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\CustomerProfile;
use App\Models\Address;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\OrderReturn;
use App\Models\ReturnItem;
use App\Models\Refund;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\BlogPost;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Setting;
use App\Models\AuditLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 12. Create Roles and Permissions first so we can assign them to users
        $p1 = Permission::create(['name' => 'manage_orders', 'description' => 'Allows managing orders and returns.']);
        $p2 = Permission::create(['name' => 'manage_products', 'description' => 'Allows managing product catalogs.']);
        $p3 = Permission::create(['name' => 'manage_reports', 'description' => 'Allows viewing business analytics reports.']);
        $p4 = Permission::create(['name' => 'manage_users', 'description' => 'Allows managing staff members and roles.']);
        $p5 = Permission::create(['name' => 'manage_settings', 'description' => 'Allows managing store configuration policies.']);

        $r1 = Role::create(['name' => 'super_admin', 'description' => 'Full administrative privileges across the entire store.']);
        $r2 = Role::create(['name' => 'sales_manager', 'description' => 'Manages order processing, return logistcs, and reports.']);
        $r3 = Role::create(['name' => 'editor', 'description' => 'Manages products inventory and content publications.']);
        
        $r_owner = Role::create(['name' => 'product_owner', 'description' => 'Product Owner with full catalog and business access.']);
        $r_staff = Role::create(['name' => 'staff', 'description' => 'Staff member with moderation permissions.']);
        $r_customer = Role::create(['name' => 'customer', 'description' => 'Regular customer.']);

        $r1->permissions()->sync([$p1->id, $p2->id, $p3->id, $p4->id, $p5->id]);
        $r2->permissions()->sync([$p1->id, $p3->id]);
        $r3->permissions()->sync([$p2->id]);
        $r_owner->permissions()->sync([$p1->id, $p2->id, $p3->id, $p4->id, $p5->id]);
        $r_staff->permissions()->sync([$p1->id, $p2->id]);

        // Seed administrative navigation menus
        $this->call(MenuSeeder::class);

        // 1. Create standard admin user
        $mainAdmin = User::factory()->create([
            'name' => 'Maya Sree Fashion',
            'email' => 'md@msf.com',
            'password' => bcrypt('123456'),
        ]);
        $mainAdmin->assignRole($r1);
        $mainAdmin->assignRole($r_owner);

        // 2. Create Categories
        $categories = [];
        $catNames = ['Apparel', 'Footwear', 'Accessories', 'Dresses', 'Activewear'];
        foreach ($catNames as $name) {
            $categories[] = Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => "Premium collection of {$name}.",
                'is_active' => true,
                'is_featured' => true,
            ]);
        }



        // 4. Create Tags
        $tags = [];
        $tagNames = ['Summer', 'Casual', 'Formal', 'Cotton', 'Premium', 'New Arrival'];
        foreach ($tagNames as $name) {
            $tags[] = Tag::create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        }

        // 5. Create Products with Variants
        $productNames = [
            'Apparel' => [
                ['Slim Fit Oxford Shirt', 1500, 999],
                ['Classic Denim Jacket', 3000, 2499],
                ['Casual Graphic Tee', 800, 499],
            ],
            'Footwear' => [
                ['Running Shoes Zoom', 5000, 4120],
                ['Minimalist Canvas Sneakers', 2500, 1850],
            ],
            'Accessories' => [
                ['Leather Backpack Pro', 4500, 2850],
                ['Sunglasses UV Protect', 2000, 1290],
            ],
            'Dresses' => [
                ['Floral Summer Maxi Dress', 2200, 1599],
                ['Elegant Silk Evening Gown', 8000, 6999],
            ]
        ];

        foreach ($productNames as $catName => $prods) {
            $category = collect($categories)->firstWhere('name', $catName);
            if (!$category) continue;

            foreach ($prods as $pData) {
                $product = Product::create([
                    'category_id' => $category->id,
                    'name' => $pData[0],
                    'slug' => Str::slug($pData[0] . '-' . Str::random(5)),
                    'short_description' => "High quality {$pData[0]} designed for comfort and style.",
                    'description' => "This premium {$pData[0]} is sourced from high-quality materials and matches the latest trends.",
                    'mrp' => $pData[1],
                    'selling_price' => $pData[2],
                    'cost_price' => $pData[2] * 0.55,
                    'is_active' => true,
                    'is_featured' => rand(0, 1) === 1,
                    'is_new_arrival' => rand(0, 1) === 1,
                    'is_bestseller' => rand(0, 1) === 1,
                ]);

                // Attach random tags
                $product->tags()->attach(collect($tags)->random(rand(2, 4))->pluck('id')->toArray());

                // Create variants (different sizes & colors)
                $sizes = ['S', 'M', 'L', 'XL'];
                $colors = ['Navy', 'Black', 'White', 'Olive'];

                foreach ($sizes as $idx => $size) {
                    $color = $colors[$idx];
                    $stock = rand(5, 50);
                    // Force some items to be low stock/OOS to make low stock alerts pop
                    if (rand(1, 3) === 1) {
                        $stock = rand(0, 3);
                    }

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => strtoupper(substr($catName, 0, 3)) . '-' . rand(100, 999) . '-' . $size . '-' . substr($color, 0, 3),
                        'size' => $size,
                        'color' => $color,
                        'color_code' => $color === 'White' ? '#ffffff' : ($color === 'Black' ? '#000000' : ($color === 'Navy' ? '#000080' : '#808000')),
                        'mrp' => $product->mrp,
                        'selling_price' => $product->selling_price,
                        'cost_price' => $product->cost_price,
                        'stock_quantity' => $stock,
                        'reserved_quantity' => 0,
                        'low_stock_threshold' => 5,
                        'is_active' => true,
                    ]);
                }
            }
        }

        // 6. Create Customer Users with Profiles & Addresses
        $customerNames = [
            ['Emma Johnson', 'emma@example.com'],
            ['Michael Smith', 'michael@example.com'],
            ['Sarah Williams', 'sarah@example.com'],
            ['David Brown', 'david@example.com'],
            ['Jessica Davis', 'jessica@example.com'],
            ['James Wilson', 'james@example.com'],
            ['Patricia Taylor', 'patricia@example.com']
        ];

        foreach ($customerNames as $cData) {
            $user = User::create([
                'name' => $cData[0],
                'email' => $cData[1],
                'password' => bcrypt('password'),
            ]);
            $user->assignRole($r_customer);

            $profile = CustomerProfile::create([
                'user_id' => $user->id,
                'gender' => rand(0, 1) ? 'female' : 'male',
                'date_of_birth' => now()->subYears(rand(18, 45))->format('Y-m-d'),
                'total_orders' => rand(1, 15),
                'total_spent' => rand(500, 25000),
            ]);

            Address::create([
                'user_id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => '+91 ' . rand(70000, 99999) . ' ' . rand(10000, 99999),
                'address_line_1' => rand(10, 500) . ', Main Street',
                'address_line_2' => 'Sector ' . rand(1, 22),
                'city' => rand(0, 1) ? 'Mumbai' : 'Delhi',
                'state' => rand(0, 1) ? 'Maharashtra' : 'Delhi',
                'postal_code' => (string)rand(110001, 400001),
                'is_default_billing' => true,
                'is_default_shipping' => true,
            ]);
        }

        // 7. Create Purchase Orders
        $suppliers = ['Global Garments', 'Apex Footwear Ltd', 'Fashion Fabrics Inc'];
        foreach ($suppliers as $supplier) {
            $po = PurchaseOrder::create([
                'supplier_name' => $supplier,
                'total_amount' => 0,
                'status' => rand(0, 1) ? 'ordered' : 'received',
                'ordered_at' => now()->subDays(rand(1, 15)),
                'received_at' => null,
            ]);

            // Add some items
            $variants = ProductVariant::inRandomOrder()->take(rand(2, 4))->get();
            $totalAmount = 0;
            foreach ($variants as $variant) {
                $qty = rand(10, 50);
                $cost = $variant->cost_price ?? ($variant->selling_price * 0.6);
                $lineTotal = $qty * $cost;
                $totalAmount += $lineTotal;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_variant_id' => $variant->id,
                    'quantity_ordered' => $qty,
                    'quantity_received' => $po->status === 'received' ? $qty : 0,
                    'unit_cost' => $cost,
                    'total_cost' => $lineTotal,
                ]);
            }

            $po->update(['total_amount' => $totalAmount]);

            if ($po->status === 'received') {
                $po->update(['received_at' => now()->subDays(rand(1, 5))]);
            }
        }

        // 8. Create Sample Orders
        $customers = User::whereHas('customerProfile')->get();
        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

        foreach ($statuses as $idx => $status) {
            $customer = $customers->get($idx % $customers->count());
            $address = $customer->addresses()->first();

            $order = Order::create([
                'user_id' => $customer->id,
                'status' => $status,
                'payment_status' => in_array($status, ['delivered', 'shipped', 'processing']) ? 'paid' : ($status === 'cancelled' ? 'failed' : 'pending'),
                'payment_method' => ['upi', 'credit_card', 'cod', 'debit_card'][$idx % 4],
                'subtotal' => 0.00,
                'discount_amount' => $status === 'delivered' ? 100.00 : 0.00,
                'tax_amount' => 0.00,
                'cgst_amount' => 0.00,
                'sgst_amount' => 0.00,
                'igst_amount' => 0.00,
                'shipping_amount' => 0.00,
                'grand_total' => 0.00,
                'total_items' => 0,

                'shipping_first_name' => $customer->first_name,
                'shipping_last_name' => $customer->last_name,
                'shipping_phone' => $address->phone ?? '9876543210',
                'shipping_address_line_1' => $address->address_line_1 ?? '123 MG Road',
                'shipping_address_line_2' => $address->address_line_2,
                'shipping_city' => $address->city ?? 'Mumbai',
                'shipping_state' => $address->state ?? 'Maharashtra',
                'shipping_postal_code' => $address->postal_code ?? '400001',
                'shipping_country' => $address->country ?? 'IN',

                'billing_first_name' => $customer->first_name,
                'billing_last_name' => $customer->last_name,
                'billing_phone' => $address->phone ?? '9876543210',
                'billing_address_line_1' => $address->address_line_1 ?? '123 MG Road',
                'billing_address_line_2' => $address->address_line_2,
                'billing_city' => $address->city ?? 'Mumbai',
                'billing_state' => $address->state ?? 'Maharashtra',
                'billing_postal_code' => $address->postal_code ?? '400001',
                'billing_country' => $address->country ?? 'IN',

                'courier_name' => in_array($status, ['shipped', 'delivered']) ? 'Delhivery' : null,
                'tracking_number' => in_array($status, ['shipped', 'delivered']) ? 'TRK' . rand(10000000, 99999999) : null,
                'estimated_delivery_at' => in_array($status, ['shipped', 'delivered']) ? now()->addDays(3) : null,
                'admin_notes' => "[System Seeder]: Order initialized in {$status} state.",
            ]);

            // Add 1-3 order items
            $variants = ProductVariant::with('product')->inRandomOrder()->take(rand(1, 3))->get();
            $subtotal = 0;
            $totalItems = 0;

            foreach ($variants as $vIdx => $variant) {
                $qty = rand(1, 2);
                $mrp = $variant->mrp ?? $variant->product->mrp;
                $price = $variant->selling_price ?? $variant->product->selling_price;
                $lineTotal = $qty * $price;

                $subtotal += $lineTotal;
                $totalItems += $qty;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_name' => ($variant->size ? "Size: {$variant->size}" : '') . ($variant->color ? ", Color: {$variant->color}" : ''),
                    'sku' => $variant->sku,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'unit_mrp' => $mrp,
                    'discount' => 0.00,
                    'tax_amount' => $lineTotal * 0.18,
                    'tax_rate' => 18.00,
                    'total_price' => $lineTotal,
                ]);
            }

            $discount = $order->discount_amount;
            $tax = ($subtotal - $discount) * 0.18;
            $cgst = $tax / 2;
            $sgst = $tax / 2;
            $grandTotal = $subtotal - $discount + $tax;

            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'cgst_amount' => $cgst,
                'sgst_amount' => $sgst,
                'grand_total' => $grandTotal,
                'total_items' => $totalItems,
            ]);

            // Create Payment record
            Payment::create([
                'order_id' => $order->id,
                'gateway' => $order->payment_method === 'cod' ? 'cod' : 'razorpay',
                'gateway_payment_id' => $order->payment_status === 'paid' ? 'pay_' . Str::random(14) : null,
                'gateway_order_id' => $order->payment_status === 'paid' ? 'order_' . Str::random(14) : null,
                'method' => $order->payment_method,
                'amount' => $order->grand_total,
                'status' => $order->payment_status === 'paid' ? 'captured' : 'pending',
                'paid_at' => $order->payment_status === 'paid' ? now()->subMinutes(rand(10, 60)) : null,
            ]);

            // Add initial status log
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => 'pending',
                'comment' => 'Order placed successfully.',
            ]);

            if ($status !== 'pending') {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => 'pending',
                    'to_status' => $status,
                    'comment' => "Order state changed to {$status}.",
                ]);
            }
        }

        // 9. Create Default Coupons
        Coupon::create([
            'code' => 'FIRST20',
            'name' => 'First Order Discount',
            'description' => 'Get 20% off on your first order.',
            'type' => 'percentage',
            'value' => 20.00,
            'max_discount' => 1000.00,
            'min_order_value' => 500.00,
            'max_uses_total' => null,
            'max_uses_per_user' => 1,
            'first_order_only' => true,
            'is_auto_apply' => false,
            'is_combinable' => false,
            'is_active' => true,
            'starts_at' => now()->subDays(10),
            'expires_at' => now()->addDays(30),
        ]);

        Coupon::create([
            'code' => 'SUMMER500',
            'name' => 'Summer Mid-Season Sale',
            'description' => 'Flat ₹500 off on order values above ₹2000.',
            'type' => 'flat',
            'value' => 500.00,
            'max_discount' => 500.00,
            'min_order_value' => 2000.00,
            'max_uses_total' => 200,
            'max_uses_per_user' => 1,
            'first_order_only' => false,
            'is_auto_apply' => false,
            'is_combinable' => false,
            'is_active' => true,
            'starts_at' => now()->subDays(2),
            'expires_at' => now()->addDays(15),
        ]);

        Coupon::create([
            'code' => 'FREESHIP',
            'name' => 'Free Shipping Privilege',
            'description' => 'Waive all shipping fees.',
            'type' => 'free_shipping',
            'value' => 0.00,
            'min_order_value' => 999.00,
            'max_uses_total' => 100,
            'max_uses_per_user' => 2,
            'first_order_only' => false,
            'is_auto_apply' => true,
            'is_combinable' => true,
            'is_active' => true,
            'starts_at' => now()->subDays(1),
            'expires_at' => now()->addDays(45),
        ]);

        // 10. Create Return Requests
        $deliveredOrder = Order::where('status', 'delivered')->first();
        if ($deliveredOrder) {
            $item = $deliveredOrder->items()->first();

            // 1. Requested return
            $ret1 = OrderReturn::create([
                'order_id' => $deliveredOrder->id,
                'user_id' => $deliveredOrder->user_id,
                'status' => 'requested',
                'reason' => 'wrong_size',
                'description' => 'The size is too small, I need one size larger.',
                'refund_amount' => $item->unit_price * $item->quantity,
            ]);

            ReturnItem::create([
                'return_id' => $ret1->id,
                'order_item_id' => $item->id,
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
                'reason' => 'wrong_size',
                'photos' => ['https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=400&q=80'],
            ]);

            // 2. QC passed return
            $ret2 = OrderReturn::create([
                'order_id' => $deliveredOrder->id,
                'user_id' => $deliveredOrder->user_id,
                'status' => 'qc_passed',
                'reason' => 'defective',
                'description' => 'Stitch tearing on the sleeve.',
                'refund_amount' => $item->unit_price * $item->quantity,
                'pickup_scheduled_at' => now()->subDays(2),
                'picked_up_at' => now()->subDays(1),
                'qc_completed_at' => now(),
            ]);

            ReturnItem::create([
                'return_id' => $ret2->id,
                'order_item_id' => $item->id,
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
                'reason' => 'defective',
                'qc_status' => 'passed',
                'qc_notes' => 'Minor tear confirmed, stock return logged.',
            ]);

            // 3. Fully Refunded return
            $ret3 = OrderReturn::create([
                'order_id' => $deliveredOrder->id,
                'user_id' => $deliveredOrder->user_id,
                'status' => 'refunded',
                'reason' => 'not_as_described',
                'description' => 'Color looks different in real life.',
                'refund_amount' => $item->unit_price * $item->quantity,
                'pickup_scheduled_at' => now()->subDays(4),
                'picked_up_at' => now()->subDays(3),
                'qc_completed_at' => now()->subDays(2),
                'approved_at' => now()->subDays(4),
            ]);

            ReturnItem::create([
                'return_id' => $ret3->id,
                'order_item_id' => $item->id,
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
                'reason' => 'not_as_described',
                'qc_status' => 'passed',
            ]);

            // Create refund entry for this fully refunded return
            $paymentObj = $deliveredOrder->payments()->first();
            if ($paymentObj) {
                Refund::create([
                    'payment_id' => $paymentObj->id,
                    'order_id' => $deliveredOrder->id,
                    'return_id' => $ret3->id,
                    'gateway_refund_id' => 'REF-' . strtoupper(Str::random(10)),
                    'amount' => $ret3->refund_amount,
                    'reason' => 'Customer return request completed.',
                    'status' => 'processed',
                    'processed_at' => now()->subDays(2),
                ]);
            }
        }

        // 11. Create Blog Posts and Tags
        $cat1 = BlogCategory::create([
            'name' => 'Fashion Trends',
            'slug' => 'fashion-trends',
            'description' => 'Latest insights and guidelines in garment fashion.',
            'sort_order' => 1,
        ]);

        $cat2 = BlogCategory::create([
            'name' => 'Style Guide',
            'slug' => 'style-guide',
            'description' => 'Pro styling recommendations for casual and formals.',
            'sort_order' => 2,
        ]);

        $tag1 = BlogTag::create(['name' => 'Summer', 'slug' => 'summer']);
        $tag2 = BlogTag::create(['name' => 'Winter', 'slug' => 'winter']);
        $tag3 = BlogTag::create(['name' => 'Formal', 'slug' => 'formal']);

        $admin = User::first();

        $post1 = BlogPost::create([
            'blog_category_id' => $cat1->id,
            'author_id' => $admin ? $admin->id : null,
            'title' => 'Top Summer Fashion Trends in 2026',
            'slug' => 'top-summer-fashion-trends-in-2026',
            'excerpt' => 'An in-depth review of fabrics, textures, and color highlights for summer wear.',
            'content' => '<p>Summers in 2026 are all about lightweight linen and breathability. Vibrant neon accents paired with pastel bases are dominating the runways.</p>',
            'status' => 'published',
            'is_featured' => true,
            'published_at' => now()->subDays(5),
        ]);
        $post1->tags()->sync([$tag1->id, $tag3->id]);

        $post2 = BlogPost::create([
            'blog_category_id' => $cat2->id,
            'author_id' => $admin ? $admin->id : null,
            'title' => 'How to Style a Casual Blazer Elegantly',
            'slug' => 'how-to-style-a-casual-blazer-elegantly',
            'excerpt' => 'Master the high-low look by combining structured blazers with clean denim jeans.',
            'content' => '<p>Blazers are no longer restricted to corporate boardrooms. Pairing a navy single-breasted blazer with white slim-fit denim creates an effortlessly chic styling statement.</p>',
            'status' => 'draft',
            'is_featured' => false,
        ]);
        $post2->tags()->sync([$tag3->id]);

        // 12. Roles and Permissions were seeded at the start of the seeder

        // 13. Create Default System Settings
        Setting::create(['group' => 'general', 'key' => 'store_name', 'value' => 'Vibe Store', 'type' => 'text']);
        Setting::create(['group' => 'general', 'key' => 'contact_email', 'value' => 'support@vibe.com', 'type' => 'text']);
        Setting::create(['group' => 'general', 'key' => 'contact_phone', 'value' => '+91 98765 43210', 'type' => 'text']);
        Setting::create(['group' => 'general', 'key' => 'currency', 'value' => 'INR', 'type' => 'text']);
        Setting::create(['group' => 'general', 'key' => 'store_address', 'value' => '123 MG Road, Mumbai, India', 'type' => 'text']);

        Setting::create(['group' => 'payment', 'key' => 'cod_active', 'value' => '1', 'type' => 'boolean']);
        Setting::create(['group' => 'payment', 'key' => 'razorpay_active', 'value' => '0', 'type' => 'boolean']);
        Setting::create(['group' => 'payment', 'key' => 'razorpay_key', 'value' => 'rzp_test_key', 'type' => 'text']);
        Setting::create(['group' => 'payment', 'key' => 'razorpay_secret', 'value' => 'rzp_test_secret', 'type' => 'text']);

        Setting::create(['group' => 'email', 'key' => 'smtp_host', 'value' => 'smtp.mailtrap.io', 'type' => 'text']);
        Setting::create(['group' => 'email', 'key' => 'smtp_port', 'value' => '2525', 'type' => 'text']);
        Setting::create(['group' => 'email', 'key' => 'smtp_username', 'value' => 'mailtrap_user', 'type' => 'text']);
        Setting::create(['group' => 'email', 'key' => 'smtp_password', 'value' => 'mailtrap_pass', 'type' => 'text']);
        Setting::create(['group' => 'email', 'key' => 'sender_name', 'value' => 'Maya Sree Fashion', 'type' => 'text']);
        Setting::create(['group' => 'email', 'key' => 'sender_email', 'value' => 'noreply@vibe.com', 'type' => 'text']);

        // 14. Create Sample Audit Logs
        $sampleProd = Product::first();
        $sampleOrder = Order::first();

        if ($sampleProd) {
            AuditLog::create([
                'user_id' => $mainAdmin ? $mainAdmin->id : null,
                'auditable_type' => Product::class,
                'auditable_id' => $sampleProd->id,
                'action' => 'update',
                'old_values' => ['price' => 1999.00, 'selling_price' => 1499.00],
                'new_values' => ['price' => 1999.00, 'selling_price' => 1299.00],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ]);
        }

        if ($sampleOrder) {
            AuditLog::create([
                'user_id' => $mainAdmin ? $mainAdmin->id : null,
                'auditable_type' => Order::class,
                'auditable_id' => $sampleOrder->id,
                'action' => 'update',
                'old_values' => ['status' => 'pending'],
                'new_values' => ['status' => 'processing'],
                'ip_address' => '192.168.1.15',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
            ]);
        }
    }
}
