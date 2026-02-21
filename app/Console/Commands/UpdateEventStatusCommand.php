<?php

namespace App\Console\Commands;

use App\Jobs\UpdateEventStatusJob;
use Illuminate\Console\Command;

class UpdateEventStatusCommand extends Command
{
    protected $signature = 'events:update-status';
    protected $description = 'Update event statuses based on current date';

    public function handle(): int
    {
        $this->info('Updating event statuses...');
        
        (new UpdateEventStatusJob())->handle();
        
        $this->info('Event statuses updated successfully!');
        
        return Command::SUCCESS;
    }
}
