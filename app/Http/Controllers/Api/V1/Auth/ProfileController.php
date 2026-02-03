<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Get authenticated user profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $user = $this->authService->profile($request->user());

            return ApiResponse::success(
                data: new UserResource($user),
                message: 'Profile retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                message: 'Failed to retrieve profile',
                statusCode: 500
            );
        }
    }
}
