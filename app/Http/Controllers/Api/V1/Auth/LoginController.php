<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\DTOs\Auth\LoginData;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Login user
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        try {
            $data = LoginData::from($request->validated());
            $result = $this->authService->login($data);

            return ApiResponse::success(
                data: [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                ],
                message: 'Login successful'
            );
        } catch (ValidationException $e) {
            return ApiResponse::validationError(
                errors: $e->errors(),
                message: $e->getMessage()
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                message: 'Login failed',
                statusCode: 500
            );
        }
    }
}
