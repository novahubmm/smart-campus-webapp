<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of permissions
     */
    public function index(): View
    {
        $this->authorize('manage permissions');

        $permissions = PermissionEnum::grouped();
        $permissionModels = Permission::all()->keyBy('name');

        return view('permissions.index', compact('permissions', 'permissionModels'));
    }
}
