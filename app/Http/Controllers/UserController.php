<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\User\UserPasswordResetData;
use App\DTOs\User\UserStoreData;
use App\DTOs\User\UserUpdateData;
use App\Enums\RoleEnum;
use App\Http\Requests\UserResetPasswordRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    use LogsActivity;

    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request): View
    {
        $this->authorize('view users');

        $filters = [
            'search' => $request->string('search')->toString(),
            'role' => $request->string('role')->toString(),
            'status' => $request->string('status')->toString(),
        ];

        $users = $this->userService->paginate($filters);
        $roles = Role::orderBy('name')->get();
        $totals = [
            'all' => User::count(),
            'active' => User::where('is_active', true)->count(),
        ];

        return view('users.index', compact('users', 'roles', 'totals', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create users');

        $roles = Role::where('name', RoleEnum::ADMIN->value)->orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    public function store(UserStoreRequest $request): RedirectResponse
    {
        $this->authorize('create users');

        $data = UserStoreData::from($request->validated());
        $user = $this->userService->store($data, $request->user());

        $this->logCreate('User', $user->id ?? '', $request->validated()['name'] ?? null);

        return redirect()->route('users.index')->with('success', __('User created successfully.'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update users');

        $user->load('roles');

        return view('users.edit', compact('user'));
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update users');

        $data = UserUpdateData::from($user, $request->validated());
        $this->userService->update($data, $request->user());

        $this->logUpdate('User', $user->id, $user->name);

        return redirect()->route('users.index')->with('success', __('User updated successfully.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete users');

        return redirect()->route('users.index')->with('error', __('Delete is disabled. Deactivate users instead.'));
    }

    public function deactivate(User $user): RedirectResponse
    {
        $this->authorize('update users');

        $this->userService->setActive($user, false);

        $this->logActivity('deactivate', 'User', $user->id, 'Deactivated User: ' . $user->name);

        return redirect()->route('users.index')->with('success', __('User deactivated successfully.'));
    }

    public function activate(User $user): RedirectResponse
    {
        $this->authorize('update users');

        $this->userService->setActive($user, true);

        $this->logActivity('activate', 'User', $user->id, 'Activated User: ' . $user->name);

        return redirect()->route('users.index')->with('success', __('User activated successfully.'));
    }

    public function resetPassword(UserResetPasswordRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update users');

        $data = UserPasswordResetData::from($user, $request->validated());
        $this->userService->resetPassword($data);

        $this->logActivity('password_reset', 'User', $user->id, 'Reset password for User: ' . $user->name);

        return redirect()->route('users.index')->with('success', __('Password reset successfully.'));
    }
}
