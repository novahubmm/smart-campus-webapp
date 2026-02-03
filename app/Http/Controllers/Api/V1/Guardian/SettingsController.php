<?php

namespace App\Http\Controllers\Api\V1\Guardian;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Interfaces\Guardian\GuardianSettingsRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(
        private readonly GuardianSettingsRepositoryInterface $settingsRepository
    ) {}

    /**
     * Get Settings
     * GET /api/v1/guardian/settings
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $guardianId = $request->user()->guardianProfile?->id;
            
            if (!$guardianId) {
                return ApiResponse::error('Guardian profile not found', 404);
            }

            $settings = $this->settingsRepository->getSettings($guardianId);

            return ApiResponse::success($settings);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve settings: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update Settings
     * PUT /api/v1/guardian/settings
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'language' => 'nullable|string|in:en,mm,zh',
            'theme' => 'nullable|string|in:light,dark',
            'notifications' => 'nullable|array',
            'preferences' => 'nullable|array',
        ]);

        try {
            $guardianId = $request->user()->guardianProfile?->id;
            
            if (!$guardianId) {
                return ApiResponse::error('Guardian profile not found', 404);
            }

            $settings = $this->settingsRepository->updateSettings($guardianId, $request->all());

            return ApiResponse::success($settings, 'Settings updated successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update settings: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get School Info
     * GET /api/v1/guardian/school/info
     */
    public function schoolInfo(): JsonResponse
    {
        try {
            $info = $this->settingsRepository->getSchoolInfo();

            return ApiResponse::success($info);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve school info: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get School Rules
     * GET /api/v1/guardian/school/rules
     */
    public function schoolRules(): JsonResponse
    {
        try {
            $rules = $this->settingsRepository->getSchoolRules();

            return ApiResponse::success($rules);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve school rules: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get School Contact
     * GET /api/v1/guardian/school/contact
     */
    public function schoolContact(): JsonResponse
    {
        try {
            $contact = $this->settingsRepository->getSchoolContact();

            return ApiResponse::success($contact);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve school contact: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get School Facilities
     * GET /api/v1/guardian/school/facilities
     */
    public function schoolFacilities(): JsonResponse
    {
        try {
            $facilities = $this->settingsRepository->getSchoolFacilities();

            return ApiResponse::success($facilities);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve school facilities: ' . $e->getMessage(), 500);
        }
    }
}
