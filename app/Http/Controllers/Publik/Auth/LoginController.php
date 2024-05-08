<?php

namespace App\Http\Controllers\Publik\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use App\Http\Requests\LoginDashboardRequest;
use App\Http\Resources\Dashboard\User\UserResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class LoginController extends Controller
{
    public function login(LoginDashboardRequest $request)
    {
        // $data = $request->validated();

        // $user = User::with('roles')->where('email', $data['email'])->first();

        // if (!$user || !Hash::check($data['password'], $user->password)) {
        //     return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Password atau email anda tidak valid'), Response::HTTP_UNAUTHORIZED);
        // }

        // $createToken = $user->createToken('create_token_' . Str::uuid())->plainTextToken;
        // session()->put('token_login', $createToken);

        // $createCookie = cookie('authToken', $createToken, 43200);
        // return response()->json(new UserResource(Response::HTTP_OK, 'Login berhasil!, Selamat Datang ' . $user->name . '.', $user), Response::HTTP_OK)->withCookie($createCookie);
        $credentials = $request->validated();

        if (!auth()->attempt($credentials)) {
            return response()->json(new WithoutDataResource(Response::HTTP_UNAUTHORIZED, 'Password atau email anda tidak valid'), Response::HTTP_UNAUTHORIZED);
        }

        $user = auth()->user();
        $token = $user->createToken('create_token_' . Str::uuid())->plainTextToken;

        $response = response()->json([
            'user' => new UserResource(Response::HTTP_OK, 'Login berhasil!, Selamat Datang ' . $user->name . '.', $user),
            'token' => $token
        ], Response::HTTP_OK);

        return $response->withCookie(cookie('authToken', $token, 43200, true));
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
