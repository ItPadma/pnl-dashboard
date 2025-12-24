<?php

namespace Database\Seeders;

use App\Models\AccessGroup;
use Illuminate\Database\Seeder;

class AccessGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user's email (assuming it's the admin)
        $adminEmail = \App\Models\User::first()?->email ?? 'admin@example.com';

        // Create default access groups
        $groups = [
            [
                'name' => 'Super Admin',
                'description' => 'Full system access with all permissions',
                'default_access_level' => AccessGroup::LEVEL_ADMIN,
                'is_active' => true,
                'created_by' => $adminEmail,
            ],
            [
                'name' => 'Administrator',
                'description' => 'Administrative access with full CRUD permissions',
                'default_access_level' => AccessGroup::LEVEL_FULL,
                'is_active' => true,
                'created_by' => $adminEmail,
            ],
            [
                'name' => 'Manager',
                'description' => 'Manager level access with read and write permissions',
                'default_access_level' => AccessGroup::LEVEL_READ_WRITE,
                'is_active' => true,
                'created_by' => $adminEmail,
            ],
            [
                'name' => 'Staff',
                'description' => 'Staff level access with read-only permissions',
                'default_access_level' => AccessGroup::LEVEL_READ,
                'is_active' => true,
                'created_by' => $adminEmail,
            ],
            [
                'name' => 'Finance Team',
                'description' => 'Access for finance department',
                'default_access_level' => AccessGroup::LEVEL_READ_WRITE,
                'is_active' => true,
                'created_by' => $adminEmail,
            ],
            [
                'name' => 'Tax Team',
                'description' => 'Access for tax processing team',
                'default_access_level' => AccessGroup::LEVEL_READ_WRITE,
                'is_active' => true,
                'created_by' => $adminEmail,
            ],
        ];

        foreach ($groups as $groupData) {
            AccessGroup::create($groupData);
        }

        $this->command->info('Access groups seeded successfully!');
    }
}
