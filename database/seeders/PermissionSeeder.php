<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Seed all granular menu-wise permissions.
     */
    public function run(): void
    {
        $modules = [
            'dashboard' => 'Dashboard Overview',
            'reports' => 'Financial & Analytics Reports',
            'orders' => 'Customer Orders',
            'returns' => 'Returns & Refunds',
            'couriers' => 'Couriers & Logistics',
            'purchase_orders' => 'Purchase Orders',
            'products' => 'Products Management',
            'categories' => 'Categories',
            'tags' => 'Product Tags',
            'colors' => 'Color Master',
            'sizes' => 'Size Master',
            'inventory' => 'Inventory Stock Ledger',
            'stock_entry' => 'Quick Stock Matrix Entry',
            'inward' => 'Stock Inward (Goods Receipt)',
            'occasions' => 'Shop by Occasions',
            'section_badges' => 'The Maya Sree Edit Badges',
            'reviews' => 'Product Reviews',
            'coupons' => 'Coupons & Promos',
            'reels' => 'Instagram Reels & Videos',
            'blog' => 'Fashion Blog Posts',
            'customers' => 'Customers Directory',
            'users' => 'Staff & User Accounts',
            'roles' => 'Roles & Permissions',
            'settings' => 'Store Settings & Policies',
        ];

        $actions = [
            'view' => 'View and inspect records for',
            'create' => 'Create and add new entries in',
            'edit' => 'Update and modify details in',
            'delete' => 'Delete and remove records from',
        ];

        $allCreatedPermissionIds = [];

        foreach ($modules as $moduleKey => $moduleLabel) {
            foreach ($actions as $actionKey => $actionDesc) {
                $permName = "{$moduleKey}.{$actionKey}";
                $description = "{$actionDesc} {$moduleLabel}";

                $perm = Permission::updateOrCreate(
                    ['name' => $permName],
                    [
                        'module' => $moduleKey,
                        'action' => $actionKey,
                        'description' => $description,
                    ]
                );

                $allCreatedPermissionIds[] = $perm->id;
            }
        }

        // Also ensure coarse legacy permissions exist for backward compatibility
        $legacy = [
            'manage_orders' => 'Allows managing orders, returns, and couriers.',
            'manage_products' => 'Allows managing product catalogs, tags, inventory, and masters.',
            'manage_reports' => 'Allows viewing business analytics reports.',
            'manage_users' => 'Allows managing staff members, roles, and customers.',
            'manage_settings' => 'Allows managing store configuration policies.',
        ];

        foreach ($legacy as $name => $desc) {
            $p = Permission::updateOrCreate(['name' => $name], ['description' => $desc]);
            $allCreatedPermissionIds[] = $p->id;
        }

        // Sync all permissions to super_admin role
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminRole->permissions()->sync($allCreatedPermissionIds);
        }

        // Sync product owner with catalog and order permissions
        $productOwnerRole = Role::where('name', 'product_owner')->first();
        if ($productOwnerRole) {
            $ownerPerms = Permission::whereIn('module', [
                'dashboard', 'orders', 'returns', 'couriers', 'purchase_orders',
                'products', 'categories', 'tags', 'colors', 'sizes',
                'inventory', 'stock_entry', 'inward', 'occasions', 'section_badges',
                'reviews', 'coupons', 'reels', 'blog', 'reports', 'customers'
            ])->pluck('id')->toArray();

            $productOwnerRole->permissions()->sync(array_unique(array_merge($ownerPerms, [$superAdminRole?->id])));
        }

        // Sync sales_manager role with order and report permissions
        $salesManagerRole = Role::where('name', 'sales_manager')->first();
        if ($salesManagerRole) {
            $salesPerms = Permission::whereIn('module', [
                'dashboard', 'orders', 'returns', 'couriers', 'reports', 'customers'
            ])->pluck('id')->toArray();
            $salesManagerRole->permissions()->sync($salesPerms);
        }

        // Sync editor role with catalog/content permissions
        $editorRole = Role::where('name', 'editor')->first();
        if ($editorRole) {
            $editorPerms = Permission::whereIn('module', [
                'dashboard', 'products', 'categories', 'tags', 'colors', 'sizes',
                'inventory', 'stock_entry', 'inward', 'reviews', 'reels', 'blog'
            ])->pluck('id')->toArray();
            $editorRole->permissions()->sync($editorPerms);
        }
    }
}
