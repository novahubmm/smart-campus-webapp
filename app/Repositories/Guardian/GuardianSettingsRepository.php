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
        return [
            'basic_info' => [
                'name' => Setting::where('key', 'school_name')->first()?->value ?? 'SmartCampus School',
                'established_year' => Setting::where('key', 'school_established_year')->first()?->value ?? 2000,
                'type' => Setting::where('key', 'school_type')->first()?->value ?? 'Private',
                'affiliation' => Setting::where('key', 'school_affiliation')->first()?->value ?? 'Ministry of Education',
                'principal' => [
                    'name' => Setting::where('key', 'principal_name')->first()?->value ?? 'N/A',
                    'photo' => Setting::where('key', 'principal_photo')->first()?->value,
                    'message' => Setting::where('key', 'principal_message')->first()?->value ?? 'Welcome to our school.',
                ],
            ],
            'contact' => $this->getSchoolContact(),
            'facilities' => $this->getSchoolFacilities(),
            'statistics' => [
                'total_students' => Setting::where('key', 'total_students')->first()?->value ?? 0,
                'total_teachers' => Setting::where('key', 'total_teachers')->first()?->value ?? 0,
                'student_teacher_ratio' => Setting::where('key', 'student_teacher_ratio')->first()?->value ?? '15:1',
                'pass_rate' => Setting::where('key', 'pass_rate')->first()?->value ?? '95%',
            ],
        ];
    }

    public function getSchoolRules(): array
    {
        $categories = RuleCategory::with('rules')->orderBy('order')->get();

        return [
            'total_rules' => $categories->sum(fn($c) => $c->rules->count()),
            'total_categories' => $categories->count(),
            'last_updated' => $categories->max('updated_at')?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'categories' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'title' => $category->name,
                    'icon' => $category->icon ?? 'rules',
                    'icon_background_color' => $category->icon_background_color ?? '#E3F2FD',
                    'icon_color' => $category->icon_color ?? '#1E88E5',
                    'rules_count' => $category->rules->count(),
                    'rules' => $category->rules->map(function ($rule) {
                        return [
                            'id' => $rule->id,
                            'rule' => $rule->content,
                            'order' => $rule->order,
                        ];
                    })->toArray(),
                ];
            })->toArray(),
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
