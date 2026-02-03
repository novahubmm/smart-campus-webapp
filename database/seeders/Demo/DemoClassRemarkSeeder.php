<?php

namespace Database\Seeders\Demo;

use App\Models\ClassRemark;

class DemoClassRemarkSeeder extends DemoBaseSeeder
{
    private array $positiveRemarks = [
        'Students showed excellent participation today.',
        'Great class discussion on the topic.',
        'All students completed their work on time.',
        'Impressive improvement in understanding.',
        'Students demonstrated good teamwork.',
        'Active engagement throughout the lesson.',
        'Students asked thoughtful questions.',
        'Good behavior and focus during class.',
        'Students helped each other effectively.',
        'Excellent problem-solving skills shown.',
    ];

    private array $noteRemarks = [
        'Covered chapter sections as planned.',
        'Introduced new topic successfully.',
        'Reviewed previous lesson material.',
        'Completed practice exercises.',
        'Discussed homework assignments.',
        'Conducted group activity.',
        'Administered short quiz.',
        'Explained key concepts.',
        'Worked through example problems.',
        'Assigned reading for next class.',
    ];

    private array $concernRemarks = [
        'Some students need extra help with the material.',
        'Class was a bit noisy today.',
        'A few students were distracted.',
        'Need to review this topic again.',
        'Some students missed the previous lesson.',
        'Homework completion rate was low.',
        'Students struggled with the new concept.',
        'Need more practice on this topic.',
        'Some students arrived late.',
        'Technical issues affected the lesson.',
    ];

    public function run(array $periods): void
    {
        $this->command->info('Creating Class Remarks (Class Records)...');

        $workingDays = $this->getWorkingDaysArray();
        $remarkCount = 0;

        // Track created remarks to avoid duplicates
        $createdRemarks = [];

        foreach ($workingDays as $workingDay) {
            $dayOfWeek = strtolower($workingDay->format('l'));

            foreach ($periods as $periodData) {
                $period = $periodData['period'];
                $class = $periodData['class'];

                // Skip if no teacher
                if (!$period->teacher_profile_id) {
                    continue;
                }

                // Skip if not matching day
                if ($period->day_of_week !== $dayOfWeek) {
                    continue;
                }

                // Create unique key to avoid duplicates
                $remarkKey = "{$class->id}_{$period->subject_id}_{$period->id}_{$workingDay->format('Y-m-d')}";
                if (isset($createdRemarks[$remarkKey])) {
                    continue;
                }

                // Determine remark type (70% note, 20% positive, 10% concern)
                $rand = rand(1, 100);
                if ($rand <= 70) {
                    $type = 'note';
                    $remark = $this->noteRemarks[array_rand($this->noteRemarks)];
                } elseif ($rand <= 90) {
                    $type = 'positive';
                    $remark = $this->positiveRemarks[array_rand($this->positiveRemarks)];
                } else {
                    $type = 'concern';
                    $remark = $this->concernRemarks[array_rand($this->concernRemarks)];
                }

                ClassRemark::create([
                    'class_id' => $class->id,
                    'subject_id' => $period->subject_id,
                    'period_id' => $period->id,
                    'teacher_id' => $period->teacher_profile_id,
                    'date' => $workingDay,
                    'remark' => $remark,
                    'type' => $type,
                ]);

                $createdRemarks[$remarkKey] = true;
                $remarkCount++;
            }
        }

        $this->command->info("  → Created {$remarkCount} class remarks");
    }
}
