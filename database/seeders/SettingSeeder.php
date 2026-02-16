<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Seed the application's settings.
     */
    public function run(): void
    {
        Setting::firstOrCreate(
            ['id' => '00000000-0000-0000-0000-000000000001'],
            [
                'school_name' => 'Yar Khinn Shin Thar School',
                'school_email' => 'info@yarkhinshinthar.com',
                'school_phone' => '+95 9 000 000 000',
                'school_address' => 'Your school address here',
                'school_website' => 'Website',
                'school_about_us' => 'About Us',
                'principal_name' => 'Principal Name',
                'school_logo_path' => null,
                'school_short_logo_path' => null,
                'setup_completed_school_info' => false,
                'setup_completed_academic' => false,
                'setup_completed_event_and_announcements' => false,
                'setup_completed_time_table_and_attendance' => false,
                'setup_completed_finance' => false,
                // Timetable settings
                'number_of_periods_per_day' => 7,
                'minute_per_period' => 45,
                'break_duration' => 15,
                'school_start_time' => '08:00',
                'school_end_time' => '13:30',
                'week_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            ]
        );

        // Update existing settings if timetable fields are null
        $setting = Setting::first();
        if ($setting) {
            $updates = [];
            if ($setting->number_of_periods_per_day === null) {
                $updates['number_of_periods_per_day'] = 7;
            }
            if ($setting->minute_per_period === null) {
                $updates['minute_per_period'] = 45;
            }
            if ($setting->break_duration === null) {
                $updates['break_duration'] = 15;
            }
            if ($setting->school_start_time === null) {
                $updates['school_start_time'] = '08:00';
            }
            if ($setting->school_end_time === null) {
                $updates['school_end_time'] = '13:30';
            }
            if ($setting->week_days === null) {
                $updates['week_days'] = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
            }
            
            if (!empty($updates)) {
                $setting->update($updates);
            }
        }
    }
}
