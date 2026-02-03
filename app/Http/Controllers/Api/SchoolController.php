<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\StudentProfile;
use App\Models\SchoolClass;
use Illuminate\Http\JsonResponse;

class SchoolController extends Controller
{
    public function info(): JsonResponse
    {
        $setting = Setting::first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $setting?->id,
                'name' => $setting?->school_name,
                'tagline' => $this->taglineData($setting),
                'logo' => storage_url($setting?->school_logo_path),
                'contact' => $this->contactData($setting),
                'about_us' => $this->aboutUsData($setting),
            ],
        ]);
    }

    private function taglineData(?Setting $setting): ?string
    {
        $tagline = $setting?->school_tagline;

        if ($tagline) {
            return $tagline;
        }

        if ($setting?->principal_name) {
            return 'Principal: ' . $setting->principal_name;
        }

        $about = trim((string) ($setting?->school_about_us ?? ''));
        if ($about !== '') {
            $sentence = strtok($about, '.');
            if ($sentence !== false) {
                return trim($sentence);
            }

            return $about;
        }

        return null;
    }

    private function aboutUsData(?Setting $setting): string
    {
        return $setting?->school_about_us ?? '';
    }

    private function contactData(?Setting $setting): array
    {
        return [
            'address' => $setting?->school_address,
            'phone' => $setting?->school_phone,
            'email' => $setting?->school_email,
            'website' => $this->websiteData($setting),
        ];
    }

    private function websiteData(?Setting $setting): ?string
    {
        if (!empty($setting?->school_website)) {
            return $setting->school_website;
        }

        $appUrl = config('app.url');
        if (!empty($appUrl)) {
            return $appUrl;
        }

        return request()->getSchemeAndHttpHost();
    }
}
