<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Akun;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Exports\Pengaturan\Akun\RolesExport;
use App\Imports\Pengaturan\Akun\RolesImport;
use App\Http\Requests\Excel_Import\ImportRoleRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class RolesController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllRoles()
    {
        if (!Gate::allows('view role')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataRole = Role::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all role for dropdown',
            'data' => $dataRole
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view role')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $role = Role::query();

        // Search
        // if ($request->has('search')) {
        //     $role = $role->where('name', 'like', '%' . $request->search . '%')
        //         ->orWhere('deskripsi', 'like', '%' . $request->search . '%');
        // }

        $dataRole = $role->get();
        if ($dataRole->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data Role tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $this->formatData($dataRole);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data Role berhasil ditampilkan.',
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function store(StoreRoleRequest $request)
    {
        if (!Gate::allows('create role')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $role = Role::create($data);
        $successMessage = "Data Role '{$role->name}' berhasil dibuat.";
        $formattedData = $this->formatData(collect([$role]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function show(Role $role)
    {
        if (!Gate::allows('view role', $role)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$role) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data role karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $this->formatData(collect([$role]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Data role '{$role->name}' berhasil ditampilkan.",
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update(Role $role, UpdateRoleRequest $request)
    {
        if (!Gate::allows('edit role', $role)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $role->update($data);
        $updatedRole = $role->fresh();

        $successMessage = "Data Role '{$updatedRole->name}' berhasil diperbarui.";
        $formattedData = $this->formatData(collect([$role]))->first();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => $successMessage,
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function destroy(Role $role)
    {
        if (!Gate::allows('delete role', $role)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $role->delete();

        $successMessage = "Data role '{$role->name}' berhasil dihapus.";
        return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
    }

    public function restore($id)
    {
        $role = Role::withTrashed()->find($id);

        if (!Gate::allows('delete role', $role)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $role->restore();

        if (is_null($role->deleted_at)) {
            $successMessage = "Data role '{$role->name}' berhasil dipulihkan.";
            return response()->json(new WithoutDataResource(Response::HTTP_OK, $successMessage), Response::HTTP_OK);
        } else {
            $successMessage = 'Restore data tidak dapat diproses, Silahkan hubungi admin untuk dilakukan pengecekan ulang.';
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $successMessage), Response::HTTP_BAD_REQUEST);
        }
    }

    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'deskripsi' => $role->deskripsi,
                'initialValues' => $this->formatPermissions($role->permissions),
                'created_at' => $role->created_at,
                'updated_at' => $role->updated_at,
            ];
        });
    }

    protected function formatPermissions($permissions)
    {
        if ($permissions->isEmpty()) {
            return null;
        }

        $permissionTypes = ['view', 'create', 'edit', 'delete', 'import', 'export', 'verifikasi'];

        $groupedPermissions = $permissions->groupBy('group')->map(function ($group, $groupName) use ($permissionTypes) {
            $permissionsArray = [];
            foreach ($permissionTypes as $type) {
                $hasPermission = $group->contains(function ($item) use ($type) {
                    return str_contains($item->name, $type);
                });
                $permissionsArray[$type] = $hasPermission ? true : null;
            }
            return [
                'name' => $groupName,
                'permissions' => $permissionsArray,
            ];
        });

        return $groupedPermissions->values()->toArray();
    }
}
