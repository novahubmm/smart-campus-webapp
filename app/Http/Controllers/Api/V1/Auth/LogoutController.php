<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Logout user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            return ApiResponse::success(
                message: 'Logout successful'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                message: 'Logout failed',
                statusCode: 500
            );
        }
    }
}
