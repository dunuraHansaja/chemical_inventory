<?php

namespace App\Support;

class PermissionCatalog
{
    /**
     * @return array<string, array<string, string>>
     */
    public static function groups(): array
    {
        return [
            'Dashboard' => [
                'dashboard.view' => 'View dashboard',
            ],
            'Materials' => [
                'materials.view' => 'View materials',
                'materials.create' => 'Add materials',
                'materials.edit' => 'Edit materials',
                'materials.delete' => 'Delete materials',
                'materials.import' => 'Import materials',
                'materials.export' => 'Export and print materials',
            ],
            'Finished goods' => [
                'finished_goods.view' => 'View finished goods',
            ],
            'Semi finished goods' => [
                'semi_finished_goods.view' => 'View semi finished goods',
            ],
            'Lots & expiry' => [
                'lots.view' => 'View lots',
                'lots.create' => 'Receive lots',
            ],
            'Products' => [
                'production.view' => 'View products',
                'production.create' => 'Define products and recipes',
            ],
            'Purchases' => [
                'purchases.view' => 'View purchases',
                'purchases.create' => 'Create purchases',
                'purchases.edit' => 'Edit draft purchases',
                'purchases.delete' => 'Delete draft purchases',
                'purchases.receive' => 'Receive purchases',
            ],
            'Suppliers' => [
                'suppliers.view' => 'View suppliers',
                'suppliers.create' => 'Add suppliers',
                'suppliers.edit' => 'Edit suppliers',
                'suppliers.delete' => 'Delete suppliers',
            ],
            'Categories' => [
                'categories.view' => 'View categories',
                'categories.create' => 'Add categories',
                'categories.edit' => 'Edit categories',
                'categories.delete' => 'Delete categories',
            ],
            'Storage locations' => [
                'storage_locations.view' => 'View storage locations',
                'storage_locations.create' => 'Add storage locations',
                'storage_locations.edit' => 'Edit storage locations',
                'storage_locations.delete' => 'Delete storage locations',
            ],
            'Stock' => [
                'movements.view' => 'View stock movements',
                'adjustments.create' => 'Adjust stock',
            ],
            'Settings' => [
                'settings.view' => 'View settings',
                'settings.update' => 'Update settings',
            ],
            'Access management' => [
                'roles.manage' => 'Manage roles and permissions',
                'users.manage' => 'Manage user accounts',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return array_keys(array_merge(...array_values(self::groups())));
    }

    /**
     * @param list<string> $permissions
     * @return list<string>
     */
    public static function missingViewPermissions(array $permissions): array
    {
        $requirements = [
            'materials.create' => 'materials.view',
            'materials.edit' => 'materials.view',
            'materials.delete' => 'materials.view',
            'materials.import' => 'materials.view',
            'materials.export' => 'materials.view',
            'lots.create' => 'lots.view',
            'production.create' => 'production.view',
            'purchases.create' => 'purchases.view',
            'purchases.edit' => 'purchases.view',
            'purchases.delete' => 'purchases.view',
            'purchases.receive' => 'purchases.view',
            'suppliers.create' => 'suppliers.view',
            'suppliers.edit' => 'suppliers.view',
            'suppliers.delete' => 'suppliers.view',
            'categories.create' => 'categories.view',
            'categories.edit' => 'categories.view',
            'categories.delete' => 'categories.view',
            'storage_locations.create' => 'storage_locations.view',
            'storage_locations.edit' => 'storage_locations.view',
            'storage_locations.delete' => 'storage_locations.view',
            'adjustments.create' => 'movements.view',
            'settings.update' => 'settings.view',
        ];
        $missing = [];

        foreach ($requirements as $action => $view) {
            if (in_array($action, $permissions, true) && ! in_array($view, $permissions, true)) {
                $missing[] = $view;
            }
        }

        return array_values(array_unique($missing));
    }
}
