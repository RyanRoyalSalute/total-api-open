<?php

namespace App\Http\Controllers\Admin;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PermissionsController extends Controller
{
    // Assign permissions to a user
    public function assignPermissionsToUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $permissionCodes = $request->input('permissions'); // Example: ['LINE_BACKEND_VIEW', 'COURSE_MANAGE']

        // Retrieve the permissions by their codes
        $permissions = Permission::whereIn('permission_code', $permissionCodes)->get();

        // Sync permissions with the user (assign and remove permissions)
        $user->permissions()->sync($permissions->pluck('id')->toArray());

        return response()->json(['message' => 'Permissions assigned successfully.']);
    }

    // Check if user has a specific permission
    public function checkPermission(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $permissionCode = $request->input('permission_code');

        if ($user->hasPermission($permissionCode)) {
            return response()->json(['message' => 'User has permission.']);
        } else {
            return response()->json(['message' => 'User does not have permission.'], 403);
        }
    }
}
