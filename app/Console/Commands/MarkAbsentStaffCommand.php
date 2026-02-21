<?php

namespace App\Console\Commands;

use App\Jobs\MarkAbsentStaffJob;
use Illuminate\Console\Command;

class MarkAbsentStaffCommand extends Command
{
    protected $signature = 'attendance:mark-absent-staff';
    protected $description = 'Mark staff as absent for dates with no attendance record (current month only)';

    public function handle(): int
    {
        $this->info('Marking absent staff for current month...');
        
        (new MarkAbsentStaffJob())->handle();
        
        $this->info('Staff absent marking completed successfully!');
        
        return Command::SUCCESS;
    }
}
