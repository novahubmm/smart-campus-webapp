<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearPayrollData extends Command
{
    protected $signature = 'payroll:clear';
    protected $description = 'Clear all payroll data from the database';

    public function handle()
    {
        if (!$this->confirm('Are you sure you want to delete all payroll data? This cannot be undone.')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        try {
            $count = DB::table('payrolls')->count();
            DB::table('payrolls')->delete();
            
            $this->info("Successfully deleted {$count} payroll records.");
            $this->info('You can now test the payroll system with fresh data.');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to clear payroll data: ' . $e->getMessage());
            $this->info('Make sure the database is not in use and try again.');
            return 1;
        }
    }
}
