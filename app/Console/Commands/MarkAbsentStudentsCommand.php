<?php

namespace App\Console\Commands;

use App\Jobs\MarkAbsentStudentsJob;
use Illuminate\Console\Command;

class MarkAbsentStudentsCommand extends Command
{
    protected $signature = 'attendance:mark-absent';
    protected $description = 'Mark students as absent for periods with no attendance record (current month only)';

    public function handle(): int
    {
        $this->info('Marking absent students for current month...');
        
        (new MarkAbsentStudentsJob())->handle();
        
        $this->info('Absent marking completed successfully!');
        
        return Command::SUCCESS;
    }
}
