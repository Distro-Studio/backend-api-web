<?php

namespace App\Http\Controllers\Publik\Auth;

use App\Models\User;
use App\Mail\SendOTPAccount;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\VerifyOTPRequest;
use App\Http\Requests\SendingOTPRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class ForgotPasswordController extends Controller
{
    public function sendOtp(SendingOTPRequest $request)
    {
        $data = $request->validated();

        $user = User::whereHas('data_karyawans', function ($query) use ($data) {
            $query->where('email', $data['email']);
        })->first();

        if (!$user) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Pengguna dengan email tersebut tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $otp = mt_rand(100000, 999999);
        Cache::put('otp_' . $user->data_karyawans->email, $otp, now()->addMinutes(10));

        $nama_user = $user->nama;
        Mail::to($data['email'])->send(new SendOTPAccount($nama_user, $otp));

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Kode OTP berhasil dikirim, Silahkan cek inbox atau spam di email anda.'), Response::HTTP_OK);
    }

    public function verifyOtp(VerifyOTPRequest $request)
    {
        $data = $request->validated();

        $user = User::whereHas('data_karyawans', function ($query) use ($data) {
            $query->where('email', $data['email']);
        })->first();

        if (!$user) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Pengguna dengan email tersebut tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $cachedOtp = Cache::get('otp_' . $user->data_karyawans->email);
        if ($cachedOtp === null) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Kode OTP sudah kadaluwarsa, silahkan lakukan verifikasi ulang.'), Response::HTTP_NOT_FOUND);
        }
        if ($cachedOtp != $data['kode_otp']) {
            return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kode OTP tidak valid.'), Response::HTTP_UNAUTHORIZED);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        Cache::forget('otp_' . $user->data_karyawans->email);

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Kata sandi baru anda berhasil diubah.'), Response::HTTP_OK);
    }
}
