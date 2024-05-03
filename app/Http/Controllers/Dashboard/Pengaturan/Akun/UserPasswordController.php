<?php

namespace App\Http\Controllers\Dashboard\Pengaturan\Akun;

use Illuminate\Http\Request;
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
        $user = Auth::user();
        $data = $request->validated();
        if (isset($data['password'])) {
            // TODO: Check if the current password is correct
            $currentPassword = $request->input('current_password');
            if (!Hash::check($currentPassword, $user->password)) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Kata sandi yang anda masukkan tidak valid.'), Response::HTTP_BAD_REQUEST);
            }

            // TODO: Update the new password
            $data['password'] = Hash::make($data['password']);
        }
        /** @var \App\Models\User $user **/
        $user->fill($data)->save();
        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Berhasil memperbarui kata sandi anda.', $user), Response::HTTP_OK);
    }
}
