<?php

namespace Database\Seeders;

use App\Models\RuleCategory;
use App\Models\SchoolRule;
use Illuminate\Database\Seeder;

class SchoolRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            'Academic Rules' => [
                [
                    'sort_order' => 1,
                    'text' => 'Students must maintain 75% attendance',
                    'severity' => 'high',
                    'consequence' => 'Students below 75% attendance may not be allowed to sit for exams',
                ],
                [
                    'sort_order' => 2,
                    'text' => 'Assignments must be submitted on time',
                    'severity' => 'medium',
                    'consequence' => 'Late submissions will result in grade deduction',
                ],
                [
                    'sort_order' => 3,
                    'text' => 'Academic integrity must be maintained',
                    'severity' => 'high',
                    'consequence' => 'Cheating or plagiarism will result in disciplinary action',
                ],
                [
                    'sort_order' => 4,
                    'text' => 'Regular participation in class activities is required',
                    'severity' => 'low',
                    'consequence' => 'Participation marks will be affected',
                ],
            ],
            'Conduct & Discipline' => [
                [
                    'sort_order' => 1,
                    'text' => 'Respect all staff and fellow students',
                    'severity' => 'high',
                    'consequence' => 'Disrespectful behavior will result in disciplinary action',
                ],
                [
                    'sort_order' => 2,
                    'text' => 'No bullying or harassment tolerated',
                    'severity' => 'high',
                    'consequence' => 'Immediate suspension and possible expulsion',
                ],
                [
                    'sort_order' => 3,
                    'text' => 'Proper school uniform must be worn at all times',
                    'severity' => 'medium',
                    'consequence' => 'Students may be sent home to change',
                ],
                [
                    'sort_order' => 4,
                    'text' => 'Mobile phones must be switched off during class',
                    'severity' => 'low',
                    'consequence' => 'Phone may be confiscated until end of day',
                ],
            ],
            'Safety & Security' => [
                [
                    'sort_order' => 1,
                    'text' => 'ID cards must be visible at all times',
                    'severity' => 'medium',
                    'consequence' => 'Entry may be denied without valid ID',
                ],
                [
                    'sort_order' => 2,
                    'text' => 'Report suspicious activities immediately',
                    'severity' => 'high',
                    'consequence' => 'Failure to report may result in disciplinary action',
                ],
                [
                    'sort_order' => 3,
                    'text' => 'Follow emergency evacuation procedures',
                    'severity' => 'high',
                    'consequence' => 'Non-compliance endangers lives',
                ],
                [
                    'sort_order' => 4,
                    'text' => 'No unauthorized visitors on campus',
                    'severity' => 'medium',
                    'consequence' => 'Visitors must register at security office',
                ],
            ],
            'Facility Usage' => [
                [
                    'sort_order' => 1,
                    'text' => 'Keep classrooms and facilities clean',
                    'severity' => 'low',
                    'consequence' => 'May be assigned cleaning duties',
                ],
                [
                    'sort_order' => 2,
                    'text' => 'Return library books on time',
                    'severity' => 'low',
                    'consequence' => 'Late fees will be charged',
                ],
                [
                    'sort_order' => 3,
                    'text' => 'No food or drinks in computer labs',
                    'severity' => 'medium',
                    'consequence' => 'Access to lab may be restricted',
                ],
                [
                    'sort_order' => 4,
                    'text' => 'Sports equipment must be returned after use',
                    'severity' => 'low',
                    'consequence' => 'Replacement cost may be charged',
                ],
            ],
        ];

        foreach ($rules as $categoryTitle => $categoryRules) {
            $category = RuleCategory::where('title', $categoryTitle)->first();
            
            if (!$category) {
                $this->command->warn("Category '{$categoryTitle}' not found. Skipping...");
                continue;
            }

            foreach ($categoryRules as $rule) {
                SchoolRule::updateOrCreate(
                    [
                        'rule_category_id' => $category->id,
                        'text' => $rule['text'],
                    ],
                    [
                        'sort_order' => $rule['sort_order'],
                        'severity' => $rule['severity'],
                        'consequence' => $rule['consequence'],
                    ]
                );
            }
        }

        $this->command->info('School rules seeded successfully!');
    }
}
