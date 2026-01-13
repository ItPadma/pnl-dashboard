<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define menus based on existing routes in web.php
        // Structure Flattened to match Sidebar visual (Root -> Child)
        $menus = [
            // Dashboard
            [
                'name' => 'Dashboard',
                'slug' => 'dashboard',
                'route_name' => 'dashboard.index',
                'icon' => 'fas fa-home',
                'parent_id' => null,
                'order' => 1,
                'is_active' => true,
                'type' => 'item',
            ],

            // Section: Pajak
            [
                'name' => 'Pajak',
                'slug' => 'section-pajak',
                'route_name' => null,
                'icon' => null,
                'parent_id' => null,
                'order' => 5, // Between Dashboard(1) and Reguler(10)
                'is_active' => true,
                'type' => 'section',
            ],

            // Reguler Group
            [
                'name' => 'Reguler',
                'slug' => 'reguler',
                'route_name' => null,
                'icon' => 'fas fa-layer-group',
                'parent_id' => null,
                'order' => 10,
                'is_active' => true,
                'type' => 'item',
            ],
            // ... (Reguler Children) ...
            [
                'name' => 'Pajak Keluaran',
                'slug' => 'reguler-pajak-keluaran',
                'route_name' => 'pnl.reguler.pajak-keluaran.index',
                'icon' => 'far fa-file-alt',
                'parent_id' => null, 
                'order' => 1,
                'is_active' => true,
                'type' => 'item',
            ],
            [
                'name' => 'Pajak Keluaran (DB Only)',
                'slug' => 'reguler-pajak-keluaran-db',
                'route_name' => 'pnl.reguler.pajak-keluaran-db.index',
                'icon' => 'far fa-file-alt',
                'parent_id' => null, 
                'order' => 2,
                'is_active' => true,
                'type' => 'item',
            ],
            [
                'name' => 'Pajak Masukan',
                'slug' => 'reguler-pajak-masukan',
                'route_name' => 'pnl.reguler.pajak-masukan.index',
                'icon' => 'far fa-file-alt',
                'parent_id' => null, 
                'order' => 2,
                'is_active' => true,
                'type' => 'item',
            ],
            [
                'name' => 'Upload CSV Coretax',
                'slug' => 'reguler-upload-csv',
                'route_name' => 'pnl.reguler.pajak-masukan.uploadcsv',
                'icon' => 'fas fa-upload',
                'parent_id' => null,
                'order' => 3,
                'is_active' => true,
                'type' => 'item',
            ],

            // Non-Reguler Group
            [
                'name' => 'Non-Reguler',
                'slug' => 'non-reguler',
                'route_name' => null,
                'icon' => 'fas fa-layer-group',
                'parent_id' => null,
                'order' => 20,
                'is_active' => true,
                'type' => 'item',
            ],
            // ... (NonReguler Children) ...
            [
                'name' => 'Pajak Keluaran',
                'slug' => 'non-reguler-pajak-keluaran',
                'route_name' => 'pnl.non-reguler.pajak-keluaran.index',
                'icon' => 'far fa-file-alt',
                'parent_id' => null,
                'order' => 1,
                'is_active' => true,
                'type' => 'item',
            ],
            [
                'name' => 'Pajak Masukan',
                'slug' => 'non-reguler-pajak-masukan',
                'route_name' => 'pnl.non-reguler.pajak-masukan.index',
                'icon' => 'far fa-file-alt',
                'parent_id' => null,
                'order' => 2,
                'is_active' => true,
                'type' => 'item',
            ],

            // Section: Master
            [
                'name' => 'Master',
                'slug' => 'section-master',
                'route_name' => null,
                'icon' => null,
                'parent_id' => null,
                'order' => 25, // Between Non-Reguler(20) and Import(30)
                'is_active' => true,
                'type' => 'section',
            ],

            // Master Data (Import)
            [
                'name' => 'Import',
                'slug' => 'master-data',
                'route_name' => null,
                'icon' => 'fas fa-file-import',
                'parent_id' => null,
                'order' => 30,
                'is_active' => true,
                'type' => 'item',
            ],
            [
                'name' => 'PKP',
                'slug' => 'master-data-import-pkp',
                'route_name' => 'pnl.master-data.index.master-pkp',
                'icon' => 'fas fa-user-tag',
                'parent_id' => null,
                'order' => 1,
                'is_active' => true,
                'type' => 'item',
            ],

            // Section: Settings
            [
                'name' => 'Settings',
                'slug' => 'section-settings',
                'route_name' => null,
                'icon' => null,
                'parent_id' => null,
                'order' => 85, // Before Access Control(90)
                'is_active' => true,
                'type' => 'section',
            ],

            // Settings
            [
                'name' => 'Access Control',
                'slug' => 'access-control',
                'route_name' => null,
                'icon' => 'fas fa-shield-alt',
                'parent_id' => null,
                'order' => 90,
                'is_active' => true,
                'type' => 'item',
            ],
            [
                'name' => 'Access Groups',
                'slug' => 'admin-access-groups',
                'route_name' => 'admin.access-groups.index',
                'icon' => 'fas fa-users-cog',
                'parent_id' => null,
                'order' => 1,
                'is_active' => true,
                'type' => 'item',
            ],
            [
                'name' => 'Menu Management',
                'slug' => 'admin-menu-management',
                'route_name' => 'admin.menus.index',
                'icon' => 'fas fa-list',
                'parent_id' => null,
                'order' => 2,
                'is_active' => true,
                'type' => 'item',
            ],
            [
                'name' => 'User Manager',
                'slug' => 'user-manager',
                'route_name' => 'pnl.setting.userman.index',
                'icon' => 'fas fa-users',
                'parent_id' => null,
                'order' => 95,
                'is_active' => true,
                'type' => 'item',
            ],
        ];

        // Create menus
        $createdMenus = [];

        foreach ($menus as $menuData) {
            $menu = Menu::updateOrCreate(
                ['slug' => $menuData['slug']],
                $menuData
            );
            $createdMenus[$menuData['slug']] = $menu;
        }

        // Relationships Map (Child Slug => Parent Slug)
        $relationships = [
            'reguler-pajak-keluaran' => 'reguler',
            'reguler-pajak-keluaran-db' => 'reguler',
            'reguler-pajak-masukan' => 'reguler',
            'reguler-upload-csv' => 'reguler',
            'non-reguler-pajak-keluaran' => 'non-reguler',
            'non-reguler-pajak-masukan' => 'non-reguler',
            'master-data-import-pkp' => 'master-data',
            'admin-access-groups' => 'access-control',
            'admin-menu-management' => 'access-control',
            // User Manager is root in this config or we can group it. 
            // In sidebar it's a standalone Link item.
        ];

        foreach ($relationships as $childSlug => $parentSlug) {
            if (isset($createdMenus[$childSlug]) && isset($createdMenus[$parentSlug])) {
                $createdMenus[$childSlug]->update([
                    'parent_id' => $createdMenus[$parentSlug]->id,
                ]);
            }
        }

        $this->command->info('Menus seeded successfully!');
    }
}
