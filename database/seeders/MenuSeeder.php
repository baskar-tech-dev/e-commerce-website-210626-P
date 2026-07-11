<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Permission;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Menu::truncate();

        $menus = [
            [
                'name' => 'Dashboard',
                'path' => '/admin',
                'icon' => 'LayoutDashboard',
                'group' => 'Overview',
                'permission_name' => null, // Available to all admins
            ],
            [
                'name' => 'Orders',
                'path' => '/admin/orders',
                'icon' => 'ShoppingCart',
                'group' => 'Sales',
                'permission_name' => 'manage_orders',
            ],
            [
                'name' => 'Returns',
                'path' => '/admin/returns',
                'icon' => 'RefreshCcw',
                'group' => 'Sales',
                'permission_name' => 'manage_orders',
            ],
            [
                'name' => 'Reports',
                'path' => '/admin/reports',
                'icon' => 'TrendingUp',
                'group' => 'Overview',
                'permission_name' => 'manage_reports',
            ],
            [
                'name' => 'Blog',
                'path' => '/admin/blog/posts',
                'icon' => 'FileText',
                'group' => 'Content',
                'permission_name' => 'manage_products', // Reusing manage_products for editor role
            ],
            [
                'name' => 'Insta Reels',
                'path' => '/admin/instagram-reels',
                'icon' => 'Film',
                'group' => 'Content',
                'permission_name' => 'manage_products',
            ],
            [
                'name' => 'Users',
                'path' => '/admin/users',
                'icon' => 'Users',
                'group' => 'Administration',
                'permission_name' => 'manage_users',
            ],
            [
                'name' => 'Roles & Permissions',
                'path' => '/admin/roles',
                'icon' => 'Key',
                'group' => 'Administration',
                'permission_name' => 'manage_users',
            ],
            [
                'name' => 'Products',
                'path' => '/admin/products',
                'icon' => 'ShoppingBag',
                'group' => 'Catalog',
                'permission_name' => 'manage_products',
            ],
            [
                'name' => 'Categories',
                'path' => '/admin/categories',
                'icon' => 'Folder',
                'group' => 'Catalog',
                'permission_name' => 'manage_products',
            ],
            [
                'name' => 'Inventory',
                'path' => '/admin/inventory',
                'icon' => 'Package',
                'group' => 'Catalog',
                'permission_name' => 'manage_products',
            ],
            [
                'name' => 'Purchase Orders',
                'path' => '/admin/purchase-orders',
                'icon' => 'ClipboardList',
                'group' => 'Sales',
                'permission_name' => 'manage_products',
            ],
            [
                'name' => 'Customers',
                'path' => '/admin/customers',
                'icon' => 'Users',
                'group' => 'Administration',
                'permission_name' => 'manage_users',
            ],
            [
                'name' => 'Coupons',
                'path' => '/admin/coupons',
                'icon' => 'Ticket',
                'group' => 'Marketing',
                'permission_name' => 'manage_products',
            ],
            [
                'name' => 'Tags',
                'path' => '/admin/tags',
                'icon' => 'Tags',
                'group' => 'Catalog',
                'permission_name' => 'manage_products',
            ],
            [
                'name' => 'Settings',
                'path' => '/admin/settings',
                'icon' => 'Settings',
                'group' => 'Configuration',
                'permission_name' => 'manage_settings',
            ],
        ];

        foreach ($menus as $index => $menu) {
            Menu::create(array_merge($menu, ['order' => $index]));
        }
    }
}
