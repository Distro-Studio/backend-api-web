<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Akun;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Spatie\Permission\Models\Permission;
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

    public function index()
    {
        if (!Gate::allows('view role')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $role = Role::query()->orderBy('created_at', 'desc')->withTrashed();

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

    // public function show(Role $role)
    // {
    //     if (!Gate::allows('view role', $role)) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    //     }

    //     if (!$role) {
    //         return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data role karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
    //     }

    //     $formattedData = $this->formatData(collect([$role]))->first();

    //     return response()->json([
    //         'status' => Response::HTTP_OK,
    //         'message' => "Data role '{$role->name}' berhasil ditampilkan.",
    //         'data' => $formattedData,
    //     ], Response::HTTP_OK);
    // }

    public function show(Role $role)
    {
        if (!Gate::allows('view role', $role)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        if (!$role) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data role karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Get all permissions
        $allPermissions = Permission::all();

        $rolePermissions = $allPermissions->map(function ($permission) use ($role) {
            if (!$permission) {
                return [
                    'name' => $permission->name,
                    'group' => $permission->group,
                    'has_permission' => null, // Permission tidak ada di DB
                ];
            } elseif ($role->hasPermissionTo($permission)) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'group' => $permission->group,
                    'has_permission' => true, // Permission ada di DB dan di-assign ke role
                ];
            } else {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'group' => $permission->group,
                    'has_permission' => false, // Permission ada di DB tetapi tidak di-assign
                ];
            }
        });

        // Format the permissions for the response
        $formattedPermissions = $this->formatPermissions($rolePermissions);

        $formattedData = $this->formatData(collect([$role]))->first();
        $formattedData['permissions'] = $formattedPermissions;

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

        if ($role->id == 1 || $role->name === 'Super Admin') {
            return response()->json([
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'Role Super Admin tidak dapat dihapus.',
            ]);
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
                // 'initialValues' => $this->formatPermissions($role->permissions),
                'deleted_at' => $role->deleted_at,
                'created_at' => $role->created_at,
                'updated_at' => $role->updated_at,
            ];
        });
    }

    // protected function formatPermissions($permissions)
    // {
    //     if ($permissions->isEmpty()) {
    //         return null;
    //     }

    //     $permissionTypes = ['view', 'create', 'edit', 'delete', 'import', 'export', 'verifikasi'];

    //     $groupedPermissions = $permissions->groupBy('group')->map(function ($group, $groupName) use ($permissionTypes) {
    //         $permissionsArray = [];
    //         foreach ($permissionTypes as $type) {
    //             $hasPermission = $group->contains(function ($item) use ($type) {
    //                 return str_contains($item->name, $type);
    //             });
    //             $permissionsArray[$type] = $hasPermission ? true : null;
    //         }
    //         return [
    //             'name' => $groupName,
    //             'permissions' => $permissionsArray,
    //         ];
    //     });

    //     return $groupedPermissions->values()->toArray();
    // }

    // protected function formatPermissions($permissions)
    // {
    //     if ($permissions->isEmpty()) {
    //         return null;
    //     }

    //     $permissionTypes = ['view', 'create', 'edit', 'delete', 'import', 'export', 'verifikasi1', 'verifikasi2'];

    //     $groupedPermissions = $permissions->groupBy('group')->map(function ($group, $groupName) use ($permissionTypes) {
    //         $permissionsArray = [];
    //         foreach ($permissionTypes as $type) {
    //             $permissionItem = $group->first(function ($item) use ($type) {
    //                 return str_contains($item['name'], $type);
    //             });

    //             if ($permissionItem) {
    //                 $permissionsArray[$type] = $permissionItem['has_permission'];
    //             } else {
    //                 $permissionsArray[$type] = null; // Jika tidak ada permission di DB
    //             }
    //         }
    //         return [
    //             'name' => $groupName,
    //             'permissions' => $permissionsArray,
    //         ];
    //     });

    //     return $groupedPermissions->values()->toArray();
    // }

    protected function formatPermissions($permissions)
    {
        if ($permissions->isEmpty()) {
            return null;
        }

        $permissionTypes = ['view', 'create', 'edit', 'delete', 'import', 'export', 'verifikasi1', 'verifikasi2'];

        $groupedPermissions = $permissions->groupBy('group')->map(function ($group, $groupName) use ($permissionTypes) {
            $permissionsArray = [];
            foreach ($permissionTypes as $type) {
                $permissionItem = $group->first(function ($item) use ($type) {
                    return str_contains($item['name'], $type);
                });

                if ($permissionItem) {
                    $permissionsArray[$type] = [
                        'id' => $permissionItem['id'],
                        'has_permission' => $permissionItem['has_permission']
                    ];
                } else {
                    $permissionsArray[$type] = [
                        'id' => null,
                        'has_permission' => null
                    ]; // Jika tidak ada permission di DB
                }
            }
            return [
                'name' => $groupName,
                'permissions' => $permissionsArray,
            ];
        });

        return $groupedPermissions->values()->toArray();
    }
}
