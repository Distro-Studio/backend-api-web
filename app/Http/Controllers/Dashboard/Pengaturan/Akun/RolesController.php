<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Akun;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Support\Facades\Validator;
use App\Exports\Pengaturan\Akun\RolesExport;
use App\Http\Requests\Excel_Import\ImportRoleRequest;
use App\Imports\Pengaturan\Akun\RolesImport;
use App\Http\Resources\Dashboard\Pengaturan_Akun\RoleResource;
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

        // Filter
        if ($request->has('name')) {
            $role = $role->where('name', $request->name);
        }

        // Search
        if ($request->has('search')) {
            $role = $role->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $dataRole = $role->paginate(10);
        if ($dataRole->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data Role tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new RoleResource(Response::HTTP_OK, 'Data Role berhasil ditampilkan.', $dataRole), Response::HTTP_OK);
    }

    public function store(StoreRoleRequest $request)
    {
        if (!Gate::allows('create role')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $role = Role::create($data);
        $successMessage = "Data Role '{$role->name}' berhasil dibuat.";
        return response()->json(new RoleResource(Response::HTTP_OK, $successMessage, $role), Response::HTTP_OK);
    }

    public function show(Role $role)
    {
        if (!Gate::allows('view role', $role)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }
        
        

        if (!$role) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data role karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new RoleResource(Response::HTTP_OK, "Data role {$role->name} karyawan berhasil ditampilkan.", $role), Response::HTTP_OK);
    }

    public function update(Role $role, UpdateRoleRequest $request)
    {
        if (!Gate::allows('edit role', $role)) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $role->update($data);
        $updatedRole = $role->fresh();
        $successMessage = "Data Role '{$updatedRole->name}' berhasil diubah.";
        return response()->json(new RoleResource(Response::HTTP_OK, $successMessage, $role), Response::HTTP_OK);
    }

    public function bulkDelete(Request $request)
    {
        if (!Gate::allows('delete role')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataRole = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:roles,id'
        ]);

        if ($dataRole->fails()) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, $dataRole->errors()), Response::HTTP_BAD_REQUEST);
        }

        $ids = $request->input('ids');
        $deletedCount = Role::whereIn('id', $ids);
        $deletedCount->delete();

        $message = 'Data role berhasil dihapus.';

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function exportRoles(Request $request)
    {
        if (!Gate::allows('export role')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            $ids = $request->input('ids', []);
            return Excel::download(new RolesExport($ids), 'roles.xlsx');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data role berhasil di download.'), Response::HTTP_OK);
    }

    public function importRoles(ImportRoleRequest $request)
    {
        if (!Gate::allows('import role')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new RolesImport, $file['role_file']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data role berhasil di import kedalam table.'), Response::HTTP_OK);
    }
}
