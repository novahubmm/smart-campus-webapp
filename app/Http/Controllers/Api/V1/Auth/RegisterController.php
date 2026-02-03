<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\DTOs\Auth\RegisterData;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        try {
            $data = RegisterData::from($request->validated());
            $result = $this->authService->register($data);

            return ApiResponse::success(
                data: [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                ],
                message: 'User registered successfully',
                statusCode: 201
            );
        } catch (ValidationException $e) {
            return ApiResponse::validationError(
                errors: $e->errors(),
                message: $e->getMessage()
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                message: 'Registration failed',
                statusCode: 500
            );
        }
    }
}
