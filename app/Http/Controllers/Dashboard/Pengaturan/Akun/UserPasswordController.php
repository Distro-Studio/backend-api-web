<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Akun;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class UserPasswordController extends Controller
{
    public function updatePassword(UpdateUserPasswordRequest $request)
    {
        // TODO: Buat validasi email dahulu

        $user = Auth::user();

        // $dataKaryawan = $user->data_karyawans;
        // if ($dataKaryawan && $dataKaryawan->email == 'super_admin@admin.rski') {
        //     return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak dapat memperbarui kata sandi pada role Super Admin.'), Response::HTTP_FORBIDDEN);
        // }

        $data = $request->validated();
        if (isset($data['password'])) {
            // Check if the current password is correct
            $currentPassword = $request->input('current_password');
            if (!Hash::check($currentPassword, $user->password)) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Kata sandi yang anda masukkan tidak valid.'), Response::HTTP_BAD_REQUEST);
            }

            // TODO: Verify email before changing password

            // Update the new password
            $data['password'] = Hash::make($data['password']);
        }
        /** @var \App\Models\User $user **/
        $user->fill($data)->save();
        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Berhasil memperbarui kata sandi anda.', $user), Response::HTTP_OK);
    }
}
