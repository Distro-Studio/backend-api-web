<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Akun;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class PermissionsController extends Controller
{
    public function getAllPermissions()
    {
        if (!Gate::allows('view.permission')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $permissions = Permission::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Berhasil menampilkan seluruh permission',
            'data' => $permissions
        ], Response::HTTP_OK);
    }

    public function updatePermissions(Request $request, Role $role)
    {
        if (!Gate::allows('edit.permission')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }
        
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
