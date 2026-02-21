<?php

namespace App\Console\Commands;

use App\Jobs\MarkAbsentTeachersJob;
use Illuminate\Console\Command;

class MarkAbsentTeachersCommand extends Command
{
    protected $signature = 'attendance:mark-absent-teachers';
    protected $description = 'Mark teachers as absent for dates with no attendance record (current month only)';

    public function handle(): int
    {
        $this->info('Marking absent teachers for current month...');
        
        (new MarkAbsentTeachersJob())->handle();
        
        $this->info('Teacher absent marking completed successfully!');
        
        return Command::SUCCESS;
    }
}
