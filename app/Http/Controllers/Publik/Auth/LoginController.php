<?php

namespace App\Http\Controllers\Publik\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use App\Http\Requests\LoginDashboardRequest;
use App\Jobs\EmailNotification\AccountEmailJob;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Http\Resources\Dashboard\User\UserResource;
use App\Http\Requests\UpdatePasswordWrongEmailRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class LoginController extends Controller
{
    public function login(LoginDashboardRequest $request)
    {
        $credentials = $request->validated();

        // if (!auth()->attempt($credentials)) {
        //     return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Password atau email anda tidak valid'), Response::HTTP_UNAUTHORIZED);
        // }
        // $user = auth()->user();

        // Retrieve the user based on the email in the data_karyawans table
        $user = User::whereHas('data_karyawans', function ($query) use ($credentials) {
            $query->where('email', $credentials['email']);
        })->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Password atau email anda tidak valid, silahkan cek kembali dan pastikan akun anda aktif'), Response::HTTP_UNAUTHORIZED);
        }

        // Cek status_aktif
        if ($user->status_aktif == 1) {
            auth()->logout(); // Logout user jika status_aktif bernilai 1
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, "Kami mendeteksi bahwa akun anda sudah tidak aktif sejak {$user->updated_at}."), Response::HTTP_FORBIDDEN);
        }

        $token = $user->createToken('create_token_' . Str::uuid())->plainTextToken;

        return response()->json([
            'user' => new UserResource(Response::HTTP_OK, "Login berhasil!, Selamat Datang '{$user->nama}'.", $user),
            'token' => $token
        ], Response::HTTP_OK);
    }

    public function getInfoUserLogin()
    {
        $user = auth()->user();
        return response()->json(new UserResource(Response::HTTP_OK, "Data pengguna akun '{$user->nama}' berhasil didapatkan.", $user), Response::HTTP_OK);
    }

    public function logout()
    {
        if (method_exists(auth()->user()->currentAccessToken(), 'delete')) {
            auth()->user()->currentAccessToken()->delete();
        }

        auth()->guard('web')->logout();
        $deleteCookie = Cookie::forget('authToken');
        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Anda berhasil melakukan logout.'), Response::HTTP_OK)->withCookie($deleteCookie);
    }
}
