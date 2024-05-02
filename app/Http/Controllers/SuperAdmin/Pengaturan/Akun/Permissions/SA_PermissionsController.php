<?php

namespace App\Http\Controllers\SuperAdmin\Pengaturan\Akun\Permissions;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SA_PermissionsController extends Controller
{
    public function getAllPermissions()
    {
        $permissions = Permission::all();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Berhasil menampilkan seluruh permission',
            'data' => $permissions
        ], Response::HTTP_OK);
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $validateIds = Validator::make($request->all(), [
            'permission_ids' => 'required|array|min:1',
            'permission_ids.*' => 'integer|exists:permissions,id'
        ]);

        if ($validateIds->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $validateIds->errors()), Response::HTTP_BAD_REQUEST);
        }

        $permissionIds = $request->input('permission_ids');
        $role->permissions()->sync($permissionIds);

        return response()->json(new WithoutDataResource(Response::HTTP_OK, "Berhasil melakukan update permission pada Role '{$role->name}'."));
    }
}
