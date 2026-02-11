<?php

namespace Database\Seeders;

use App\Models\RuleCategory;
use App\Models\SchoolRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RuleCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        SchoolRule::truncate();
        RuleCategory::truncate();

        $categories = [
            [
                'id' => '019c45b4-d7b1-73b5-b03c-b1cff25f05d7',
                'title' => 'Academic Rules',
                'title_mm' => 'ပညာရေးစည်းမျဉ်းများ',
                'description' => 'Rules related to academic performance and conduct',
                'description_mm' => 'ပညာရေးစွမ်းဆောင်ရည် နှင့် အပြုအမူ ဆိုင်ရာ စည်းမျဉ်းများ',
                'icon' => '📚',
                'icon_color' => '#1E88E5',
                'icon_bg_color' => '#E3F2FD',
                'icon_background_color' => '#E3F2FD',
                'priority' => 1,
                'is_active' => true,
                'rules' => [
                    [
                        'id' => 'rule-1',
                        'title' => 'Attendance Requirement',
                        'title_mm' => 'တက်ရောက်မှု လိုအပ်ချက်',
                        'description' => 'Students must maintain 75% attendance',
                        'description_mm' => 'ကျောင်းသားများသည် ၇၅% တက်ရောက်မှု ထိန်းသိမ်းရမည်',
                        'severity' => 'high',
                        'order' => 1,
                    ],
                    [
                        'id' => 'rule-2',
                        'title' => 'Assignment Submission',
                        'title_mm' => '과제 တင်သွင်းခြင်း',
                        'description' => 'Assignments must be submitted on time',
                        'description_mm' => '과제များကို အချိန်မီ တင်သွင်းရမည်',
                        'severity' => 'medium',
                        'order' => 2,
                    ],
                    [
                        'id' => 'rule-3',
                        'title' => 'Academic Integrity',
                        'title_mm' => 'ပညာရေး သမာဓိ',
                        'description' => 'Academic integrity is strictly enforced',
                        'description_mm' => 'ပညာရေး သမာဓိကို တင်းကြပ်စွာ လိုက်နာရမည်',
                        'severity' => 'high',
                        'order' => 3,
                    ],
                    [
                        'id' => 'rule-4',
                        'title' => 'Plagiarism Policy',
                        'title_mm' => 'ခိုးယူမှု မူဝါဒ',
                        'description' => 'Plagiarism will result in disciplinary action',
                        'description_mm' => 'ခိုးယူမှုသည် စည်းကမ်းရေးရာ အရေးယူခြင်း ခံရမည်',
                        'severity' => 'high',
                        'order' => 4,
                    ],
                ],
            ],
            [
                'id' => '019c45b4-d7b2-71d4-b424-443f4e1b520e',
                'title' => 'Conduct & Discipline',
                'title_mm' => 'အပြုအမူ နှင့် စည်းကမ်း',
                'description' => 'Rules for student behavior and discipline',
                'description_mm' => 'ကျောင်းသား အပြုအမူ နှင့် စည်းကမ်း ဆိုင်ရာ စည်းမျဉ်းများ',
                'icon' => '⚠️',
                'icon_color' => '#EF5350',
                'icon_bg_color' => '#FFEBEE',
                'icon_background_color' => '#FFEBEE',
                'priority' => 2,
                'is_active' => true,
                'rules' => [
                    [
                        'id' => 'rule-5',
                        'title' => 'Respect',
                        'title_mm' => 'လေးစားမှု',
                        'description' => 'Respect all staff and fellow students',
                        'description_mm' => 'ဆရာ/ဆရာမများ နှင့် အတန်းဖော်များကို လေးစားရမည်',
                        'severity' => 'high',
                        'order' => 1,
                    ],
                    [
                        'id' => 'rule-6',
                        'title' => 'No Bullying',
                        'title_mm' => 'အနိုင်ကျင့်ခြင်း မပြုရ',
                        'description' => 'No bullying or harassment tolerated',
                        'description_mm' => 'အနိုင်ကျင့်ခြင်း သို့မဟုတ် နှောင့်ယှက်ခြင်း မပြုရ',
                        'severity' => 'high',
                        'order' => 2,
                    ],
                    [
                        'id' => 'rule-7',
                        'title' => 'Mobile Phone Policy',
                        'title_mm' => 'မိုဘိုင်းဖုန်း မူဝါဒ',
                        'description' => 'Mobile phones must be switched off in class',
                        'description_mm' => 'အတန်းထဲတွင် မိုဘိုင်းဖုန်းကို ပိတ်ထားရမည်',
                        'severity' => 'medium',
                        'order' => 3,
                    ],
                    [
                        'id' => 'rule-8',
                        'title' => 'Uniform Policy',
                        'title_mm' => 'ယူနီဖောင်း မူဝါဒ',
                        'description' => 'Proper uniform must be worn at all times',
                        'description_mm' => 'သင့်လျော်သော ယူနီဖောင်းကို အမြဲတမ်း ဝတ်ဆင်ရမည်',
                        'severity' => 'medium',
                        'order' => 4,
                    ],
                ],
            ],
            [
                'id' => '019c45b4-d7b3-730b-be63-a432876fd0e3',
                'title' => 'Safety & Security',
                'title_mm' => 'ဘေးကင်းလုံခြုံရေး',
                'description' => 'Safety and security guidelines',
                'description_mm' => 'ဘေးကင်းလုံခြုံရေး လမ်းညွှန်ချက်များ',
                'icon' => '🛡️',
                'icon_color' => '#F9A825',
                'icon_bg_color' => '#FFF8E1',
                'icon_background_color' => '#FFF8E1',
                'priority' => 3,
                'is_active' => true,
                'rules' => [
                    [
                        'id' => 'rule-9',
                        'title' => 'ID Card',
                        'title_mm' => 'မှတ်ပုံတင်ကတ်',
                        'description' => 'ID cards must be visible at all times',
                        'description_mm' => 'မှတ်ပုံတင်ကတ်ကို အမြဲတမ်း မြင်နိုင်အောင် ဝတ်ဆင်ရမည်',
                        'severity' => 'medium',
                        'order' => 1,
                    ],
                    [
                        'id' => 'rule-10',
                        'title' => 'Report Suspicious Activities',
                        'title_mm' => 'သံသယဖြစ်ဖွယ် လုပ်ရပ်များ အစီရင်ခံရန်',
                        'description' => 'Report suspicious activities immediately',
                        'description_mm' => 'သံသယဖြစ်ဖွယ် လုပ်ရပ်များကို ချက်ချင်း အစီရင်ခံရမည်',
                        'severity' => 'high',
                        'order' => 2,
                    ],
                    [
                        'id' => 'rule-11',
                        'title' => 'Emergency Procedures',
                        'title_mm' => 'အရေးပေါ် လုပ်ထုံးလုပ်နည်းများ',
                        'description' => 'Follow emergency evacuation procedures',
                        'description_mm' => 'အရေးပေါ် ထွက်ခွာရေး လုပ်ထုံးလုပ်နည်းများကို လိုက်နာရမည်',
                        'severity' => 'high',
                        'order' => 3,
                    ],
                    [
                        'id' => 'rule-12',
                        'title' => 'Visitor Policy',
                        'title_mm' => 'ဧည့်သည် မူဝါဒ',
                        'description' => 'No unauthorized visitors on campus',
                        'description_mm' => 'ခွင့်ပြုချက်မရှိသော ဧည့်သည်များ ကျောင်းဝင်းထဲ မဝင်ရ',
                        'severity' => 'medium',
                        'order' => 4,
                    ],
                ],
            ],
            [
                'id' => '019c45b4-d7b4-7198-96f1-40371e76afd7',
                'title' => 'Facilities Usage',
                'title_mm' => 'အဆောက်အအုံ အသုံးပြုခြင်း',
                'description' => 'Guidelines for using school facilities',
                'description_mm' => 'ကျောင်းအဆောက်အအုံများ အသုံးပြုရန် လမ်းညွှန်ချက်များ',
                'icon' => '🏫',
                'icon_color' => '#43A047',
                'icon_bg_color' => '#E8F5E9',
                'icon_background_color' => '#E8F5E9',
                'priority' => 4,
                'is_active' => true,
                'rules' => [
                    [
                        'id' => 'rule-13',
                        'title' => 'Cleanliness',
                        'title_mm' => 'သန့်ရှင်းမှု',
                        'description' => 'Keep classrooms and facilities clean',
                        'description_mm' => 'အတန်းခန်းများ နှင့် အဆောက်အအုံများကို သန့်ရှင်းစွာ ထားရှိရမည်',
                        'severity' => 'low',
                        'order' => 1,
                    ],
                    [
                        'id' => 'rule-14',
                        'title' => 'Library Books',
                        'title_mm' => 'စာကြည့်တိုက် စာအုပ်များ',
                        'description' => 'Return library books on time',
                        'description_mm' => 'စာကြည့်တိုက် စာအုပ်များကို အချိန်မီ ပြန်အပ်ရမည်',
                        'severity' => 'low',
                        'order' => 2,
                    ],
                    [
                        'id' => 'rule-15',
                        'title' => 'Lab Equipment',
                        'title_mm' => 'ဓာတ်ခွဲခန်း ပစ္စည်းများ',
                        'description' => 'Handle lab equipment with care',
                        'description_mm' => 'ဓာတ်ခွဲခန်း ပစ္စည်းများကို ဂရုတစိုက် ကိုင်တွယ်ရမည်',
                        'severity' => 'medium',
                        'order' => 3,
                    ],
                    [
                        'id' => 'rule-16',
                        'title' => 'Computer Lab Policy',
                        'title_mm' => 'ကွန်ပျူတာခန်း မူဝါဒ',
                        'description' => 'No food or drinks in computer labs',
                        'description_mm' => 'ကွန်ပျူတာခန်းထဲတွင် အစားအသောက် မသုံးရ',
                        'severity' => 'medium',
                        'order' => 4,
                    ],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $rules = $categoryData['rules'];
            unset($categoryData['rules']);

            $category = RuleCategory::create($categoryData);

            foreach ($rules as $ruleData) {
                SchoolRule::create([
                    'id' => $ruleData['id'],
                    'rule_category_id' => $category->id,
                    'title' => $ruleData['title'],
                    'title_mm' => $ruleData['title_mm'],
                    'description' => $ruleData['description'],
                    'description_mm' => $ruleData['description_mm'],
                    'text' => $ruleData['description'], // For backward compatibility
                    'severity' => $ruleData['severity'],
                    'sort_order' => $ruleData['order'],
                ]);
            }
        }

        $this->command->info('✅ Rule categories and rules seeded successfully!');
        $this->command->info('   - 4 categories created');
        $this->command->info('   - 16 rules created');
    }
}
