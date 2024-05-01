<?php

namespace App\Http\Controllers\Publik\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use App\Http\Resources\Dashboard\User\UserResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8'
        ], [
            'email.required' => 'Silahkan masukkan email anda terlebih dahulu.',
            'email.email' => 'Alamat email wajib menggunakan @.',
            'password.required' => 'Kolom password tidak boleh kosong.',
            'password.min' => 'Minimum password yang diperbolehkan 8 karakter.',
        ]);

        $user = User::with('roles')->where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Password atau email anda tidak valid'), Response::HTTP_UNAUTHORIZED);
        }

        $createToken = $user->createToken('create_token_' . $user->email)->plainTextToken;
        // TODO: Debug token with postman
        if (app()->environment() === 'local') {
            session()->put('debug_token', $createToken);
        }

        $createCookie = cookie('authToken', $createToken, 43200);
        return response()->json(new UserResource(Response::HTTP_OK, 'Selamat Datang ' . $user->name . '.', $user), Response::HTTP_OK)->withCookie($createCookie);
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
