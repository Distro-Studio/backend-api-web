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
use App\Imports\Pengaturan\Akun\RolesImport;
use App\Http\Resources\Dashboard\Pengaturan_Akun\RoleResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class RolesController extends Controller
{
    /* ============================= For Dropdown ============================= */
    public function getAllRoles()
    {
        $dataRole = Role::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all Role for dropdown',
            'data' => $dataRole
        ], Response::HTTP_OK);
    }
    /* ============================= For Dropdown ============================= */

    public function index(Request $request)
    {
        if (!Gate::allows('view.role')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $role = Role::query();

        // Filter data Role berdasarkan parameter 'deskripsi'
        if ($request->has('is_struktural')) {
            $role = $role->where('is_struktural', $request->is_struktural);
        }

        // Terapkan pencarian jika parameter 'search' ada
        if ($request->has('search')) {
            $role = $role->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Urutkan data Role
        if ($request->has('sort')) {
            $sortFields = explode(',', $request->sort); // Pecah parameter 'sort' menjadi array
            $sortOrder = $request->get('order', 'asc');

            foreach ($sortFields as $sortField) {
                $role = $role->orderBy($sortField, $sortOrder);
            }
        }

        $dataRole = $role->paginate(10);

        if ($dataRole->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data Role tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        return response()->json(new RoleResource(Response::HTTP_OK, 'Data Role berhasil ditampilkan.', $dataRole), Response::HTTP_OK);
    }

    public function store(StoreRoleRequest $request)
    {
        if (!Gate::allows('create.role')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $role = Role::create($data);
        $successMessage = "Data Role '{$role->name}' berhasil dibuat.";
        return response()->json(new RoleResource(Response::HTTP_OK, $successMessage, $role), Response::HTTP_OK);
    }

    public function update(Role $role, UpdateRoleRequest $request)
    {
        if (!Gate::allows('edit.role', $role)) {
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
        if (!Gate::allows('delete.role')) {
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

        $message = 'Role berhasil dihapus.';

        return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    }

    public function exportRoles()
    {
        if (!Gate::allows('export.role')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $role = Role::all();
        return Excel::download(new RolesExport, 'roles.xlsx');
    }

    // public function importRoles(Request $request)
    // {
    // if (!Gate::allows('import.role')) {
    //     return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
    // }

    //     $import = Excel::import(new RolesImport, $request->file('roles_file'));

    //     if ($import->failures()->count() > 0) {
    //         // Handle import failures with validation errors (consider logging specific errors)
    //         return response()->json($import->failures(), Response::HTTP_UNPROCESSABLE_ENTITY);
    //     }

    //     // More informative success message
    //     $message = 'Data Role berhasil di import kedalam table.';
    //     return response()->json(new WithoutDataResource(Response::HTTP_OK, $message), Response::HTTP_OK);
    // }
}
