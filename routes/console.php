<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the announcement publishing command
Schedule::command('announcements:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Generate monthly fees on the 1st of every month at 1:00 AM
Schedule::command('fees:generate-monthly')
    ->monthlyOn(1, '01:00')
    ->withoutOverlapping()
    ->runInBackground();

// Mark overdue fees daily at 2:00 AM
Schedule::command('fees:mark-overdue')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();

// Payment System: Generate monthly invoices on the 1st of every month at 12:00 AM
Schedule::job(new \App\Jobs\PaymentSystem\MonthlyInvoiceGenerationJob())
    ->monthlyOn(1, '00:00')
    ->withoutOverlapping();

// Payment System: Generate one-time fee invoices daily at 6:00 AM
Schedule::command('payment:generate-one-time-invoices')
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->runInBackground();

// Update event statuses daily at midnight
Schedule::job(new \App\Jobs\UpdateEventStatusJob())
    ->dailyAt('00:00')
    ->withoutOverlapping();

// Update exam statuses daily at midnight
Schedule::job(new \App\Jobs\UpdateExamStatusJob())
    ->dailyAt('00:00')
    ->withoutOverlapping();

// Mark absent students for missing attendance records daily at 1:00 AM
Schedule::job(new \App\Jobs\MarkAbsentStudentsJob())
    ->dailyAt('01:00')
    ->withoutOverlapping();

// Mark absent teachers for missing attendance records daily at 1:10 AM
Schedule::job(new \App\Jobs\MarkAbsentTeachersJob())
    ->dailyAt('01:10')
    ->withoutOverlapping();

// Mark absent staff for missing attendance records daily at 1:20 AM
Schedule::job(new \App\Jobs\MarkAbsentStaffJob())
    ->dailyAt('01:20')
    ->withoutOverlapping();

// Send weekly payment reminders every Monday at 9:00 AM
Schedule::command('payments:send-weekly-reminders')
    ->weeklyOn(1, '09:00')
    ->withoutOverlapping()
    ->runInBackground();

Artisan::command('preflight:check {--quick : Skip database connectivity check}', function () {
    $results = [];
    $failed = 0;

    $appKey = config('app.key');
    $appKeyOk = !empty($appKey) && $appKey !== 'SomeRandomString';
    $results[] = [
        'Item' => 'App key',
        'Status' => $appKeyOk ? '[OK]' : '[FAIL]',
        'Details' => $appKeyOk ? 'APP_KEY is set' : 'APP_KEY missing; run php artisan key:generate',
    ];
    $failed += $appKeyOk ? 0 : 1;

    if (!$this->option('quick')) {
        try {
            DB::connection()->getPdo();
            $hasMigrations = Schema::hasTable('migrations');
            $results[] = [
                'Item' => 'Database',
                'Status' => '[OK]',
                'Details' => $hasMigrations ? 'Connected and migrations table found' : 'Connected; run migrations',
            ];
        } catch (\Throwable $e) {
            $failed++;
            $results[] = [
                'Item' => 'Database',
                'Status' => '[FAIL]',
                'Details' => $e->getMessage(),
            ];
        }
    } else {
        $results[] = [
            'Item' => 'Database',
            'Status' => '[SKIP]',
            'Details' => 'Skipped (quick mode)',
        ];
    }

    $queue = config('queue.default');
    $queueConfig = $queue ? config("queue.connections.$queue") : null;
    $queueOk = (bool) $queueConfig;
    $results[] = [
        'Item' => 'Queue',
        'Status' => $queueOk ? '[OK]' : '[FAIL]',
        'Details' => $queueOk ? "Driver: $queue" : 'Queue driver missing; check QUEUE_CONNECTION',
    ];
    $failed += $queueOk ? 0 : 1;

    $mail = config('mail.default');
    $mailerConfig = $mail ? config("mail.mailers.$mail") : null;
    $fromAddress = config('mail.from.address');
    $mailOk = $mailerConfig && $fromAddress;
    $results[] = [
        'Item' => 'Mail',
        'Status' => $mailOk ? '[OK]' : '[WARN]',
        'Details' => $mailOk ? "Mailer: $mail" : 'Mail config incomplete; set MAIL_MAILER and MAIL_FROM_ADDRESS',
    ];

    $storageLink = File::exists(public_path('storage'));
    $results[] = [
        'Item' => 'Storage link',
        'Status' => $storageLink ? '[OK]' : '[FAIL]',
        'Details' => $storageLink ? 'public/storage symlink present' : 'Run php artisan storage:link',
    ];
    $failed += $storageLink ? 0 : 1;

    $buildManifest = File::exists(public_path('build/manifest.json'));
    $hot = File::exists(public_path('hot'));
    $assetsOk = $buildManifest || $hot;
    $results[] = [
        'Item' => 'Assets',
        'Status' => $assetsOk ? '[OK]' : '[WARN]',
        'Details' => $assetsOk ? ($hot ? 'Vite dev server (hot) detected' : 'Build manifest found') : 'No Vite build; run npm run build or npm run dev',
    ];

    $locale = config('app.locale');
    $results[] = [
        'Item' => 'Locale',
        'Status' => $locale ? '[OK]' : '[WARN]',
        'Details' => $locale ? "Default locale: $locale" : 'APP_LOCALE not set; falling back to en',
    ];

    $this->table(['Item', 'Status', 'Details'], $results);

    if ($failed > 0) {
        $this->error('Preflight failed. Please address items marked [FAIL].');
        return 1;
    }

    $this->info('Preflight complete. Warnings can be addressed later.');
    return 0;
})->purpose('Validate environment configuration for Smart Campus');
