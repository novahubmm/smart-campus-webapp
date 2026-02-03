<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Models\Role;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    use AuthorizesRequests, LogsActivity;

    /**
     * Display a listing of roles
     */
    public function index(): View
    {
        $this->authorize('manage roles');

        $roles = Role::withCount('users', 'permissions')->get();

        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create(): View
    {
        $this->authorize('manage roles');

        $permissions = PermissionEnum::grouped();

        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage roles');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string'],
        ]);

        $role = Role::create(['name' => $validated['name']]);
        $role->givePermissionTo($validated['permissions']);

        $this->logCreate('Role', (string) $role->id, $role->name);

        return redirect()->route('roles.index')
            ->with('success', __('Role created successfully'));
    }

    /**
     * Show the form for editing the role
     */
    public function edit(Role $role): View
    {
        $this->authorize('manage roles');

        $permissions = PermissionEnum::grouped();
        $role->load('permissions');

        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('manage roles');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string'],
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions']);

        $this->logUpdate('Role', (string) $role->id, $role->name);

        return redirect()->route('roles.index')
            ->with('success', __('Role updated successfully'));
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('manage roles');

        // Prevent deleting role if it has users
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', __('Cannot delete role with assigned users'));
        }

        $roleName = $role->name;
        $roleId = (string) $role->id;
        
        $role->delete();

        $this->logDelete('Role', $roleId, $roleName);

        return redirect()->route('roles.index')
            ->with('success', __('Role deleted successfully'));
    }
}
