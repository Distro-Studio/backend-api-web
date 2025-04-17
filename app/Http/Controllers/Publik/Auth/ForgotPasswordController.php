<?php

namespace App\Http\Controllers\Publik\Auth;

use App\Models\User;
use App\Mail\SendOTPAccount;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\VerifyOTPRequest;
use App\Http\Requests\SendingOTPRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use Illuminate\Support\Facades\Log;

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

        $user->remember_token = Hash::make($otp);
        $user->remember_token_expired_at = now()->addMinutes(10);
        $user->save();

        try {
            // Percobaan kirim email
            Mail::to($data['email'])->send(new SendOTPAccount($user->nama, $otp));

            // Jika berhasil kirim
            Log::info("| Auth | - OTP successfully sent to email: {$data['email']}.");

            return response()->json(new WithoutDataResource(
                Response::HTTP_OK,
                'Kode OTP berhasil dikirim, silakan cek inbox atau spam di email anda.'
            ), Response::HTTP_OK);
        } catch (\Exception $e) {
            // Kalau terjadi error saat kirim email (SMTP error)
            Log::error('| Auth | - Failed to send OTP email: ' . $e->getMessage());

            return response()->json(new WithoutDataResource(
                Response::HTTP_NOT_ACCEPTABLE,
                'SMTP Google Mail sedang offline. Silakan lakukan reset password melalui Dashboard Super Admin Personalia.'
            ), Response::HTTP_NOT_ACCEPTABLE);
        }
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

        // Periksa apakah OTP masih berlaku
        if ($user->remember_token_expired_at < now()) {
            return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kode OTP sudah kadaluwarsa, silahkan lakukan verifikasi ulang.'), Response::HTTP_UNAUTHORIZED);
        }

        if (!Hash::check($data['kode_otp'], $user->remember_token)) {
            return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Kode OTP tidak valid.'), Response::HTTP_UNAUTHORIZED);
        }

        Log::info("| Auth | - OTP successfully verified for user ID: {$user->id}, Name: {$user->nama}.");

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Kode OTP valid. Silakan lanjutkan untuk mengatur ulang kata sandi.'), Response::HTTP_OK);
    }
}
