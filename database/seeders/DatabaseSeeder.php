<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::firstOrNew(['email' => env('ADMIN_EMAIL', 'admin@boltmart.test')]);
        $admin->fill(['name' => 'Store Admin', 'phone' => '9876543210', 'password' => env('ADMIN_PASSWORD', 'ChangeMe@123')]);
        $admin->is_admin = true;
        $admin->save();

        $this->call(CatalogSeeder::class);
    }
}
