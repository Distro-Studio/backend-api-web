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
use Illuminate\Support\Facades\Artisan;

class PermissionsController extends Controller
{
    public function getAllPermissions()
    {
        if (!Gate::allows('view permission')) {
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
        if (!Gate::allows('edit permission')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Validate permission IDs (optional to validate empty array for removals)
        $validateIds = Validator::make(
            $request->all(),
            [
                'permission_ids' => 'nullable|array',
                'permission_ids.*' => 'integer|exists:permissions,id'
            ],
            [
                // 'permission_ids.required' => 'Permission tidak diperbolehkan kosong.',
                'permission_ids.*.integer' => 'Permission harus berupa angka.',
                'permission_ids.*.exists' => 'Terdapat permission yang tidak valid.'
            ]
        );

        if ($validateIds->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $validateIds->errors()), Response::HTTP_BAD_REQUEST);
        }

        $permissionIds = $request->input('permission_ids', []);

        // Sync permissions: This will add/remove permissions as necessary
        $role->permissions()->sync($permissionIds);

        Artisan::call('permission:cache:clear');

        return response()->json(new WithoutDataResource(Response::HTTP_OK, "Berhasil melakukan update permission pada role '{$role->name}'."));
    }

    public function removeAllPermissions(Role $role)
    {
        if (!Gate::allows('edit permission')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $role->permissions()->detach(); // Removes all permissions from the role

        return response()->json(new WithoutDataResource(Response::HTTP_OK, "Semua permission pada role '{$role->name}' berhasil dihapus."));
    }
}
