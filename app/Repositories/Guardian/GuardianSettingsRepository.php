<?php

namespace App\Repositories\Guardian;

use App\Interfaces\Guardian\GuardianSettingsRepositoryInterface;
use App\Models\Facility;
use App\Models\KeyContact;
use App\Models\RuleCategory;
use App\Models\Setting;

class GuardianSettingsRepository implements GuardianSettingsRepositoryInterface
{
    public function getSettings(string $guardianId): array
    {
        $settings = Setting::where('key', 'guardian_settings_' . $guardianId)->first();

        if ($settings) {
            return json_decode($settings->value, true);
        }

        return [
            'language' => 'en',
            'theme' => 'light',
            'notifications' => [
                'push_enabled' => true,
                'email_enabled' => false,
            ],
            'preferences' => [
                'show_grades' => true,
                'show_ranks' => true,
            ],
        ];
    }

    public function updateSettings(string $guardianId, array $settings): array
    {
        Setting::updateOrCreate(
            ['key' => 'guardian_settings_' . $guardianId],
            ['value' => json_encode($settings)]
        );

        return $settings;
    }

    public function getSchoolInfo(): array
    {
        $setting = Setting::first();
        
        if (!$setting) {
            $setting = new Setting([
                'school_name' => 'Khinn Shin Thar High School',
                'school_email' => 'info@khinshinthar.edu',
                'school_phone' => '+959123456789',
                'school_address' => '123 Education Road, Yangon',
                'school_website' => 'https://khinshinthar.edu',
                'school_about_us' => 'Khinn Shin Thar High School is a leading educational institution committed to excellence in education.',
                'principal_name' => 'Principal Name',
            ]);
        }

        // Get key contacts
        $keyContacts = KeyContact::where('setting_id', $setting->id ?? '00000000-0000-0000-0000-000000000001')
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get();

        // Get facilities
        $facilities = Facility::all();

        // Calculate statistics from actual data
        $totalStudents = \App\Models\StudentProfile::count();
        $totalTeachers = \App\Models\TeacherProfile::count();
        $totalStaff = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'staff');
        })->count();
        $totalClasses = \App\Models\SchoolClass::count();
        $studentTeacherRatio = $totalTeachers > 0 ? round($totalStudents / $totalTeachers) . ':1' : '0:1';

        // Build contact info
        $contactInfo = [
            'phone' => $setting->school_phone ?? '+959123456789',
            'email' => $setting->school_email ?? 'info@khinshinthar.edu',
            'website' => $setting->school_website ?? 'https://khinshinthar.edu',
            'address' => $setting->school_address ?? '123 Education Road, Yangon',
            'address_mm' => $setting->school_address ?? 'ပညာရေးလမ်း ၁၂၃၊ ရန်ကုန်',
            'office_hours' => $setting->office_start_time && $setting->office_end_time 
                ? "Mon-Fri: {$setting->office_start_time} - {$setting->office_end_time}"
                : 'Mon-Fri: 8:00 AM - 4:00 PM',
            'office_hours_mm' => 'တနင်္လာ-သောကြာ: နံနက် ၈:၀၀ - ညနေ ၄:၀၀',
        ];

        // Build about info
        $aboutInfo = [
            'description' => $setting->school_about_us ?? 'Khinn Shin Thar High School is a leading educational institution committed to excellence in education.',
            'description_mm' => 'ခင်ရှင်သာ အထက်တန်းကျောင်းသည် ထိပ်တန်းပညာရေးအဖွဲ့အစည်း ဖြစ်ပါသည်။',
            'vision' => 'To be the premier educational institution providing world-class education.',
            'vision_mm' => 'ထိပ်တန်းပညာရေးအဖွဲ့အစည်း ဖြစ်လာရန်',
            'mission' => 'To provide quality education and nurture future leaders.',
            'mission_mm' => 'အရည်အသွေးမြင့် ပညာရေး ပေးအပ်ရန်',
            'values' => ['Excellence', 'Integrity', 'Innovation', 'Respect', 'Responsibility'],
            'values_mm' => ['ထူးချွန်မှု', 'သမာဓိ', 'ဆန်းသစ်မှု', 'လေးစား', 'တာဝန်ယူမှု'],
        ];

        // Build facilities list
        $facilitiesList = [];
        if ($facilities->isNotEmpty()) {
            foreach ($facilities as $index => $facility) {
                $facilitiesList[] = [
                    'id' => $facility->id,
                    'name' => $facility->name,
                    'name_mm' => $facility->name, // Add Myanmar translation if available
                    'icon' => $this->getFacilityIcon($facility->name),
                    'description' => $facility->description ?? "Modern {$facility->name} facility",
                    'description_mm' => $facility->description ?? "ခေတ်မီ {$facility->name}",
                    'capacity' => $facility->capacity ?? 50,
                    'available' => true,
                ];
            }
        } else {
            // Default facilities if none configured
            $facilitiesList = [
                [
                    'id' => 'fac-1',
                    'name' => 'Science Laboratory',
                    'name_mm' => 'သိပ္ပံဓာတ်ခွဲခန်း',
                    'icon' => '🔬',
                    'description' => 'Modern science lab with latest equipment',
                    'description_mm' => 'ခေတ်မီသိပ္ပံဓာတ်ခွဲခန်း',
                    'capacity' => 40,
                    'available' => true,
                ],
                [
                    'id' => 'fac-2',
                    'name' => 'Library',
                    'name_mm' => 'စာကြည့်တိုက်',
                    'icon' => '📚',
                    'description' => 'Well-stocked library with 10,000+ books',
                    'description_mm' => 'စာအုပ် ၁၀,၀၀၀ ကျော် ရှိသော စာကြည့်တိုက်',
                    'capacity' => 100,
                    'available' => true,
                ],
                [
                    'id' => 'fac-3',
                    'name' => 'Computer Lab',
                    'name_mm' => 'ကွန်ပျူတာခန်း',
                    'icon' => '💻',
                    'description' => '50 computers with high-speed internet',
                    'description_mm' => 'မြန်နှုန်းမြင့် အင်တာနက်ပါ ကွန်ပျူတာ ၅၀ လုံး',
                    'capacity' => 50,
                    'available' => true,
                ],
                [
                    'id' => 'fac-4',
                    'name' => 'Sports Complex',
                    'name_mm' => 'အားကစားကွင်း',
                    'icon' => '⚽',
                    'description' => 'Indoor and outdoor sports facilities',
                    'description_mm' => 'အတွင်းပိုင်း နှင့် ပြင်ပ အားကစားကွင်းများ',
                    'capacity' => 200,
                    'available' => true,
                ],
                [
                    'id' => 'fac-5',
                    'name' => 'Auditorium',
                    'name_mm' => 'ခန်းမကြီး',
                    'icon' => '🎭',
                    'description' => '500-seat auditorium for events',
                    'description_mm' => 'ထိုင်ခုံ ၅၀၀ ပါ ခန်းမကြီး',
                    'capacity' => 500,
                    'available' => true,
                ],
                [
                    'id' => 'fac-6',
                    'name' => 'Cafeteria',
                    'name_mm' => 'စားသောက်ဆိုင်',
                    'icon' => '🍽️',
                    'description' => 'Hygienic cafeteria with healthy meals',
                    'description_mm' => 'သန့်ရှင်းပြီး ကျန်းမာသော အစားအစာများ',
                    'capacity' => 150,
                    'available' => true,
                ],
            ];
        }

        // Build statistics
        $statistics = [
            'total_students' => $totalStudents,
            'total_teachers' => $totalTeachers,
            'total_staff' => $totalStaff,
            'total_classes' => $totalClasses,
            'student_teacher_ratio' => $studentTeacherRatio,
            'pass_rate' => 98.5,
            'average_attendance' => 95.2,
        ];

        // Build accreditations
        $accreditations = [
            [
                'name' => 'Ministry of Education',
                'name_mm' => 'ပညာရေးဝန်ကြီးဌာန',
                'year' => 1995,
                'certificate_url' => url('/certificates/moe.pdf'),
            ],
        ];

        // Build social media
        $socialMedia = [
            'facebook' => 'https://facebook.com/smartcampus',
            'twitter' => 'https://twitter.com/smartcampus',
            'instagram' => 'https://instagram.com/smartcampus',
            'youtube' => 'https://youtube.com/smartcampus',
        ];

        return [
            'school_id' => $setting->id ?? '019c45b4-d7b1-73b5-b03c-b1cff25f05d7',
            'school_name' => $setting->school_name ?? 'Khinn Shin Thar High School',
            'school_name_mm' => 'ခင်ရှင်သာ အထက်တန်းကျောင်း',
            'school_code' => 'SCHS-001',
            'logo_url' => $setting->school_logo_path ? url($setting->school_logo_path) : url('/school-logo.jpg'),
            'established_year' => 1995,
            'motto' => 'Excellence in Education',
            'motto_mm' => 'ပညာရေးတွင် ထူးချွန်မှု',
            'contact' => $contactInfo,
            'about' => $aboutInfo,
            'facilities' => $facilitiesList,
            'statistics' => $statistics,
            'accreditations' => $accreditations,
            'social_media' => $socialMedia,
        ];
    }

    /**
     * Get facility icon based on name
     */
    private function getFacilityIcon(string $name): string
    {
        $icons = [
            'science' => '🔬',
            'laboratory' => '🔬',
            'library' => '📚',
            'computer' => '💻',
            'sports' => '⚽',
            'auditorium' => '🎭',
            'cafeteria' => '🍽️',
            'playground' => '🏃',
            'gym' => '🏋️',
            'music' => '🎵',
            'art' => '🎨',
        ];

        $nameLower = strtolower($name);
        foreach ($icons as $key => $icon) {
            if (str_contains($nameLower, $key)) {
                return $icon;
            }
        }

        return '🏫'; // Default school icon
    }

    public function getSchoolRules(): array
    {
        $categories = RuleCategory::with(['rules' => function($query) {
            $query->orderBy('sort_order');
        }])
        ->where('is_active', true)
        ->orderBy('priority')
        ->get();

        $totalRules = $categories->sum(fn($c) => $c->rules->count());

        return [
            'categories' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'title' => $category->title,
                    'title_mm' => $category->title_mm ?? $category->title,
                    'description' => $category->description ?? '',
                    'description_mm' => $category->description_mm ?? $category->description ?? '',
                    'icon' => $category->icon ?? '📚',
                    'icon_color' => $category->icon_color ?? '#1E88E5',
                    'icon_background_color' => $category->icon_background_color ?? $category->icon_bg_color ?? '#E3F2FD',
                    'rules_count' => $category->rules->count(),
                    'priority' => $category->priority ?? 0,
                    'is_active' => $category->is_active ?? true,
                    'rules' => $category->rules->map(function ($rule) {
                        return [
                            'id' => $rule->id,
                            'title' => $rule->title ?? $rule->text,
                            'title_mm' => $rule->title_mm ?? $rule->title ?? $rule->text,
                            'description' => $rule->description ?? $rule->text,
                            'description_mm' => $rule->description_mm ?? $rule->description ?? $rule->text,
                            'severity' => $rule->severity ?? 'low',
                            'order' => $rule->sort_order ?? 0,
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray(),
            'total_categories' => $categories->count(),
            'total_rules' => $totalRules,
            'last_updated' => $categories->max('updated_at')?->toIso8601String() ?? now()->toIso8601String(),
        ];
    }

    public function getSchoolContact(): array
    {
        return [
            'address' => Setting::where('key', 'school_address')->first()?->value ?? 'N/A',
            'phone' => Setting::where('key', 'school_phone')->first()?->value ?? 'N/A',
            'email' => Setting::where('key', 'school_email')->first()?->value ?? 'N/A',
            'website' => Setting::where('key', 'school_website')->first()?->value ?? 'N/A',
            'office_hours' => Setting::where('key', 'school_office_hours')->first()?->value ?? 'Monday - Friday: 8:00 AM - 4:00 PM',
            'key_contacts' => KeyContact::orderBy('order')->get()->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'position' => $contact->position,
                    'phone' => $contact->phone,
                    'email' => $contact->email,
                ];
            })->toArray(),
        ];
    }

    public function getSchoolFacilities(): array
    {
        $facilities = Facility::orderBy('order')->get();

        if ($facilities->isEmpty()) {
            // Return default facilities if none configured
            return [
                [
                    'id' => 1,
                    'name' => 'Science Laboratory',
                    'icon' => 'science',
                    'description' => 'Fully equipped modern science lab',
                ],
                [
                    'id' => 2,
                    'name' => 'Computer Lab',
                    'icon' => 'computer',
                    'description' => 'Modern computers with high-speed internet',
                ],
                [
                    'id' => 3,
                    'name' => 'Library',
                    'icon' => 'book',
                    'description' => 'Extensive collection of books and digital resources',
                ],
                [
                    'id' => 4,
                    'name' => 'Sports Complex',
                    'icon' => 'sports',
                    'description' => 'Basketball, football, and indoor games',
                ],
            ];
        }

        return $facilities->map(function ($facility) {
            return [
                'id' => $facility->id,
                'name' => $facility->name,
                'icon' => $facility->icon ?? 'business',
                'description' => $facility->description,
            ];
        })->toArray();
    }
}
