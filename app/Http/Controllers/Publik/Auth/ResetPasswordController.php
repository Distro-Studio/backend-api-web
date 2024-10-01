<?php

namespace App\Http\Controllers\Publik\Auth;

use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class ResetPasswordController extends Controller
{
    public function resetPassword(ResetPasswordRequest $request)
    {
        $data = $request->validated();

        $user = User::whereHas('data_karyawans', function ($query) use ($data) {
            $query->where('email', $data['email']);
        })->first();
        if (!$user) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Pengguna dengan email tersebut tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        if (!$user->remember_token || $user->remember_token_expired_at < now()) {
            return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kode OTP sudah kadaluwarsa atau tidak ditemukan. Silakan lakukan permintaan ulang.'), Response::HTTP_UNAUTHORIZED);
        }

        if (!Hash::check($data['kode_otp'], $user->remember_token)) {
            return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kode OTP tidak valid.'), Response::HTTP_UNAUTHORIZED);
        }

        // Setel ulang kata sandi pengguna
        $user->password = Hash::make($data['password']);
        $user->remember_token = null;
        $user->remember_token_expired_at = null;
        $user->save();

        Log::info("| Auth | - New password set for user ID: {$user->id}, Name: {$user->nama}.");

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Kata sandi baru anda berhasil diubah.'), Response::HTTP_OK);
    }
}
