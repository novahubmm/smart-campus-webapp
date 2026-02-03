<?php

namespace Database\Seeders\Demo;

use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Period;
use App\Models\StudentProfile;

class DemoHomeworkSeeder extends DemoBaseSeeder
{
    private array $homeworkTitles = [
        'Myanmar' => [
            'စာပိုဒ်ဖတ်ခြင်း လေ့ကျင့်ခန်း',
            'သဒ္ဒါလေ့ကျင့်ခန်း',
            'စာစီစာကုံး ရေးသားခြင်း',
            'ကဗျာ အလွတ်ကျက်ခြင်း',
            'စာလုံးပေါင်း လေ့ကျင့်ခန်း',
        ],
        'English' => [
            'Reading Comprehension Exercise',
            'Grammar Practice Worksheet',
            'Essay Writing Assignment',
            'Vocabulary Building Exercise',
            'Listening and Speaking Practice',
        ],
        'Mathematics' => [
            'Problem Solving Practice',
            'Algebra Worksheet',
            'Geometry Exercise',
            'Number Operations Practice',
            'Word Problems Assignment',
        ],
        'Science' => [
            'Lab Report Writing',
            'Chapter Review Questions',
            'Experiment Observation Notes',
            'Scientific Method Practice',
            'Research Assignment',
        ],
        'General Science' => [
            'Nature Observation Journal',
            'Simple Experiment Report',
            'Science Vocabulary Practice',
            'Drawing and Labeling Exercise',
            'Science Quiz Preparation',
        ],
        'History' => [
            'Timeline Creation',
            'Historical Event Analysis',
            'Chapter Summary Writing',
            'Map Study Exercise',
            'Biography Research',
        ],
        'Geography' => [
            'Map Reading Exercise',
            'Climate Study Assignment',
            'Country Profile Research',
            'Physical Features Worksheet',
            'Environmental Study',
        ],
        'Physics' => [
            'Formula Practice Problems',
            'Lab Experiment Report',
            'Concept Application Exercise',
            'Numerical Problems Set',
            'Theory Questions Review',
        ],
        'Chemistry' => [
            'Chemical Equations Balancing',
            'Lab Safety Report',
            'Element Study Worksheet',
            'Reaction Types Practice',
            'Periodic Table Exercise',
        ],
        'Biology' => [
            'Cell Diagram Labeling',
            'Classification Exercise',
            'Life Cycle Study',
            'Anatomy Worksheet',
            'Ecosystem Research',
        ],
        'Art' => [
            'Sketch Practice',
            'Color Theory Exercise',
            'Creative Drawing Assignment',
            'Art History Research',
            'Portfolio Piece Creation',
        ],
        'Art & Craft' => [
            'Craft Project',
            'Drawing Practice',
            'Coloring Exercise',
            'Creative Design',
            'Handwork Assignment',
        ],
        'Physical Education' => [
            'Fitness Log',
            'Sports Rules Study',
            'Health and Nutrition Report',
            'Exercise Routine Planning',
            'Team Sports Research',
        ],
        'Social Studies' => [
            'Community Study',
            'Civic Responsibility Essay',
            'Current Events Analysis',
            'Cultural Research',
            'Social Issues Discussion',
        ],
    ];

    private array $descriptions = [
        'Complete all exercises in the worksheet.',
        'Read the chapter and answer the questions.',
        'Practice the problems and show your work.',
        'Write a detailed response with examples.',
        'Review the material and prepare for discussion.',
    ];

    public function run(array $periods, array $studentProfiles): void
    {
        $this->command->info('Creating Homework...');

        $workingDays = $this->getWorkingDaysArray();
        $homeworkCount = 0;
        $submissionCount = 0;

        // Track which subjects have homework for each class on each day
        // One subject = one homework per day per class
        $classSubjectDayHomework = [];

        foreach ($workingDays as $workingDay) {
            $dayOfWeek = strtolower($workingDay->format('l'));

            foreach ($periods as $periodData) {
                $period = $periodData['period'];
                $class = $periodData['class'];

                // Skip if no subject or teacher
                if (!$period->subject_id || !$period->teacher_profile_id) {
                    continue;
                }

                // Skip if not matching day
                if ($period->day_of_week !== $dayOfWeek) {
                    continue;
                }

                $classId = $class->id;
                $subjectId = $period->subject_id;
                $dateKey = $workingDay->format('Y-m-d');
                $trackKey = "{$classId}_{$subjectId}_{$dateKey}";

                // One subject = one homework per day per class
                if (isset($classSubjectDayHomework[$trackKey])) {
                    continue;
                }

                // Get subject name for title
                $subjectName = $period->subject->name ?? 'General';
                $titles = $this->homeworkTitles[$subjectName] ?? $this->homeworkTitles['English'];
                $title = $titles[array_rand($titles)];
                $description = $this->descriptions[array_rand($this->descriptions)];

                // Due date is 1-3 days after assigned date
                $dueDate = $workingDay->copy()->addDays(rand(1, 3));

                $homework = Homework::create([
                    'title' => $title,
                    'description' => $description,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'teacher_id' => $period->teacher_profile_id,
                    'period_id' => $period->id,
                    'assigned_date' => $workingDay,
                    'due_date' => $dueDate,
                    'priority' => ['low', 'medium', 'high'][array_rand(['low', 'medium', 'high'])],
                    'status' => $dueDate->isPast() ? 'completed' : 'active',
                ]);

                $classSubjectDayHomework[$trackKey] = true;
                $homeworkCount++;

                // Create submissions for students in this class
                $classStudents = $this->getStudentsInClass($studentProfiles, $classId);
                
                foreach ($classStudents as $student) {
                    // 70-90% submission rate
                    if (rand(1, 100) <= rand(70, 90)) {
                        $submittedAt = $workingDay->copy()->addDays(rand(0, 2))->addHours(rand(14, 20));

                        $status = 'submitted';
                        
                        // Grade completed homework
                        $grade = null;
                        $feedback = null;
                        $gradedAt = null;
                        
                        if ($homework->status === 'completed' && rand(1, 100) <= 80) {
                            $status = 'graded';
                            $grade = rand(60, 100);
                            $feedback = $this->getGradeFeedback($grade);
                            $gradedAt = $submittedAt->copy()->addDays(rand(1, 2));
                        }

                        HomeworkSubmission::create([
                            'homework_id' => $homework->id,
                            'student_id' => $student->id,
                            'content' => 'Completed assignment',
                            'status' => $status,
                            'grade' => $grade,
                            'feedback' => $feedback,
                            'submitted_at' => $submittedAt,
                            'graded_at' => $gradedAt,
                            'graded_by' => $gradedAt ? $period->teacher->user_id : null,
                        ]);
                        $submissionCount++;
                    }
                }
            }
        }

        $this->command->info("  → Created {$homeworkCount} homework assignments");
        $this->command->info("  → Created {$submissionCount} homework submissions");
    }

    private function getStudentsInClass(array $studentProfiles, string $classId): array
    {
        return array_filter($studentProfiles, function ($student) use ($classId) {
            return $student->class_id === $classId;
        });
    }

    private function getGradeFeedback(int $grade): string
    {
        if ($grade >= 90) {
            return 'Excellent work! Keep it up!';
        } elseif ($grade >= 80) {
            return 'Good job! Well done.';
        } elseif ($grade >= 70) {
            return 'Satisfactory work. Keep improving.';
        } else {
            return 'Needs improvement. Please review the material.';
        }
    }
}
