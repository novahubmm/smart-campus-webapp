<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->clearData();

        $this->call([
            RolePermissionSeeder::class,
            SettingSeeder::class,
            OneGradeDemoReadySeeder::class,
            MultiRoleUserSeeder::class,
        ]);
    }

    private function clearData(): void
    {
        $this->command?->warn('Clearing existing data...');

        Schema::disableForeignKeyConstraints();

        try {
            foreach (Schema::getTableListing(null, false) as $table) {
                if ($table === 'migrations') {
                    continue;
                }

                DB::table($table)->truncate();
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
