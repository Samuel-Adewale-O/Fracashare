<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $roles = [
            'admin' => 'System Administrator',
            'asset_manager' => 'Asset Manager',
            'investor' => 'Investor',
        ];

        foreach ($roles as $key => $description) {
            Role::create([
                'name' => $key,
                'guard_name' => 'web',
            ]);
        }
    }
}