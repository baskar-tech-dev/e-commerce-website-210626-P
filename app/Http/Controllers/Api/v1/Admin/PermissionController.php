<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $permissions = Permission::orderBy('name', 'asc')->get();

        $moduleDefinitions = [
            'Overview' => [
                ['key' => 'dashboard', 'label' => 'Dashboard Overview', 'icon' => 'LayoutDashboard'],
                ['key' => 'reports', 'label' => 'Financial & Analytics Reports', 'icon' => 'TrendingUp', 'confidential' => true],
            ],
            'Sales' => [
                ['key' => 'orders', 'label' => 'Customer Orders', 'icon' => 'ShoppingCart'],
                ['key' => 'returns', 'label' => 'Returns & Refunds', 'icon' => 'RefreshCcw'],
                ['key' => 'couriers', 'label' => 'Couriers & Logistics', 'icon' => 'Truck'],
                ['key' => 'purchase_orders', 'label' => 'Purchase Orders', 'icon' => 'ClipboardList'],
            ],
            'Catalog' => [
                ['key' => 'products', 'label' => 'Products Management', 'icon' => 'ShoppingBag'],
                ['key' => 'categories', 'label' => 'Categories', 'icon' => 'Folder'],
                ['key' => 'tags', 'label' => 'Product Tags', 'icon' => 'Tags'],
                ['key' => 'inventory', 'label' => 'Inventory Stock Ledger', 'icon' => 'Package'],
                ['key' => 'stock_entry', 'label' => 'Quick Stock Matrix Entry', 'icon' => 'Boxes'],
                ['key' => 'colors', 'label' => 'Color Master', 'icon' => 'Palette'],
                ['key' => 'sizes', 'label' => 'Size Master', 'icon' => 'Layers'],
                ['key' => 'inward', 'label' => 'Stock Inward (Goods Receipt)', 'icon' => 'PackagePlus'],
                ['key' => 'occasions', 'label' => 'Shop by Occasions', 'icon' => 'Sparkles'],
                ['key' => 'section_badges', 'label' => 'The Maya Sree Edit Badges', 'icon' => 'Award'],
                ['key' => 'reviews', 'label' => 'Product Reviews', 'icon' => 'Star'],
            ],
            'Marketing & Content' => [
                ['key' => 'coupons', 'label' => 'Coupons & Promos', 'icon' => 'Ticket'],
                ['key' => 'reels', 'label' => 'Instagram Reels & Videos', 'icon' => 'Film'],
                ['key' => 'blog', 'label' => 'Fashion Blog Posts', 'icon' => 'FileText'],
            ],
            'Administration (Confidential)' => [
                ['key' => 'customers', 'label' => 'Customers Directory', 'icon' => 'Users', 'confidential' => true],
                ['key' => 'users', 'label' => 'Staff & User Accounts', 'icon' => 'Shield', 'confidential' => true],
                ['key' => 'roles', 'label' => 'Roles & Permissions', 'icon' => 'Key', 'confidential' => true],
            ],
            'Configuration (Confidential)' => [
                ['key' => 'settings', 'label' => 'Store Settings & Policies', 'icon' => 'Settings', 'confidential' => true],
            ],
        ];

        $matrix = [];
        foreach ($moduleDefinitions as $groupName => $modules) {
            $groupList = [];
            foreach ($modules as $mod) {
                $modKey = $mod['key'];
                $actions = [];
                foreach (['view', 'create', 'edit', 'delete'] as $act) {
                    $perm = $permissions->first(function ($p) use ($modKey, $act) {
                        return ($p->module === $modKey && $p->action === $act) || $p->name === "{$modKey}.{$act}";
                    });
                    if ($perm) {
                        $actions[$act] = [
                            'id' => $perm->id,
                            'name' => $perm->name,
                            'description' => $perm->description,
                        ];
                    }
                }
                if (!empty($actions)) {
                    $groupList[] = [
                        'key' => $modKey,
                        'label' => $mod['label'],
                        'icon' => $mod['icon'] ?? 'Layers',
                        'confidential' => $mod['confidential'] ?? false,
                        'actions' => $actions,
                    ];
                }
            }
            if (!empty($groupList)) {
                $matrix[$groupName] = $groupList;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $permissions,
            'matrix' => $matrix,
        ]);
    }
}
