<?php

namespace Database\Seeders\Demo;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

abstract class DemoBaseSeeder extends Seeder
{
    protected static array $maleFirstNames = [
        'Aung', 'Min', 'Zaw', 'Htet', 'Kyaw', 'Myo', 'Tun', 'Naing', 'Htun', 'Thiha',
        'Pyae', 'Kaung', 'Ye', 'Wai', 'Hein', 'Phyo', 'Nay', 'Ko', 'Moe', 'Thura',
        'Zin', 'Lin', 'Soe', 'Thant', 'Yan', 'Khant', 'Paing', 'Shine', 'Hlaing', 'Nyein',
        'Sithu', 'Zaww', 'Myat', 'Thein', 'Htoo', 'Lwin', 'Oo', 'Aung Aung', 'Min Min', 'Zaw Zaw'
    ];

    protected static array $femaleFirstNames = [
        'Aye', 'Su', 'Thin', 'May', 'Khin', 'Ei', 'Phyu', 'Hnin', 'Thida', 'Sandar',
        'Myat', 'Yadanar', 'Chaw', 'Hay', 'Nwe', 'Zin', 'Thiri', 'Wai', 'Moe', 'Mon',
        'Zar', 'Noe', 'Hnin Hnin', 'Su Su', 'May May', 'Khin Khin', 'Ei Ei', 'Phyu Phyu',
        'Thazin', 'Yadana', 'Shwe', 'Hla', 'Nilar', 'Pwint', 'Yamin', 'Thandar', 'Wutt', 'Yin'
    ];

    protected static array $lastNames = [
        'Kyaw', 'Thu', 'Win', 'Aung', 'Zin', 'Min', 'Tun', 'Lin', 'Htun', 'Zaw',
        'Sone', 'Myat', 'Yint', 'Yan', 'Htet', 'Wai', 'Myo', 'Ko', 'Aye', 'Su',
        'Thin', 'Thu', 'Mar', 'Ei', 'Phyu', 'Hnin', 'Wai', 'Moe', 'Mon', 'Zar',
        'Noe', 'Oo', 'Myat', 'Hnin', 'Nwe', 'Mar', 'Lwin', 'Soe', 'Naing', 'Hlaing'
    ];

    protected static array $usedNames = [];
    public static ?string $hashedPassword = null;
    public static ?Carbon $schoolOpenDate = null;
    public static ?Carbon $today = null;
    public static array $workingDays = [];

    public static function init(): void
    {
        self::$hashedPassword = Hash::make('password');
        self::$today = Carbon::today();
        self::$schoolOpenDate = self::calculateSchoolOpenDate();
        self::$workingDays = self::getWorkingDays();
    }

    protected static function calculateSchoolOpenDate(): Carbon
    {
        $today = Carbon::today();
        return $today->dayOfWeek === Carbon::MONDAY
            ? $today->copy()->subWeek()
            : $today->copy()->previous(Carbon::MONDAY);
    }

    protected static function getWorkingDays(): array
    {
        $days = [];
        $current = self::$schoolOpenDate->copy();
        while ($current->lte(self::$today)) {
            if (!$current->isWeekend()) {
                $days[] = $current->copy();
            }
            $current->addDay();
        }
        return $days;
    }

    protected function generateUniqueName(string $gender = 'male'): string
    {
        $maxAttempts = 100;
        $attempt = 0;
        do {
            $firstNames = $gender === 'male' ? self::$maleFirstNames : self::$femaleFirstNames;
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = self::$lastNames[array_rand(self::$lastNames)];
            $name = $firstName . ' ' . $lastName;
            $attempt++;
            if ($attempt > $maxAttempts) {
                $name = $firstName . ' ' . $lastName . ' ' . rand(1, 999);
            }
        } while (in_array($name, self::$usedNames) && $attempt <= $maxAttempts + 50);
        self::$usedNames[] = $name;
        return $name;
    }

    protected function generateEmployeeId(string $prefix): string
    {
        return $prefix . '-' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
    }

    protected function getHashedPassword(): string
    {
        return self::$hashedPassword;
    }

    protected function getSchoolOpenDate(): Carbon
    {
        return self::$schoolOpenDate;
    }

    protected function getToday(): Carbon
    {
        return self::$today;
    }

    protected function getWorkingDaysArray(): array
    {
        return self::$workingDays;
    }
}
