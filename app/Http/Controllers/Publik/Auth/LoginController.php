<?php

namespace App\Http\Controllers\Publik\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;
use App\Http\Requests\LoginDashboardRequest;
use App\Http\Resources\Dashboard\User\UserResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class LoginController extends Controller
{
    public function login(LoginDashboardRequest $request)
    {
        $credentials = $request->validated();

        if (!auth()->attempt($credentials)) {
            return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Password atau email anda tidak valid'), Response::HTTP_UNAUTHORIZED);
        }

        $user = auth()->user();

        // Cek status_aktif
        if ($user->status_akun == 0) {
            auth()->logout(); // Logout user jika status_aktif bernilai 0
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, "Kami mendeteksi bahwa akun anda sudah tidak aktif sejak {$user->updated_at}."), Response::HTTP_FORBIDDEN);
        }

        $token = $user->createToken('create_token_' . Str::uuid())->plainTextToken;

        $response = response()->json([
            'user' => new UserResource(Response::HTTP_OK, 'Login berhasil!, Selamat Datang ' . $user->nama . '.', $user),
            'token' => $token
        ], Response::HTTP_OK);

        return $response->withCookie(cookie('authToken', $token, 43200, true));
    }

    public function getInfoUserLogin()
    {
        $user = auth()->user();
        return response()->json(new UserResource(Response::HTTP_OK, 'Data pengguna akun ' . $user->nama . ' berhasil didapatkan.', $user), Response::HTTP_OK);
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
