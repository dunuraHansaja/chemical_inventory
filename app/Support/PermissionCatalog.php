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
            'Lots & expiry' => [
                'lots.view' => 'View lots',
                'lots.create' => 'Receive lots',
            ],
            'Production' => [
                'production.view' => 'View production',
                'production.create' => 'Record production',
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
