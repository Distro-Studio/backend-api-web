<?php

namespace App\Http\Controllers\Publik\Auth;

use App\Helpers\FirebaseFCMHelper;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use App\Http\Requests\LoginDashboardRequest;
use App\Http\Resources\Dashboard\User\UserResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function login(LoginDashboardRequest $request)
    {
        $credentials = $request->validated();

        $user = User::where(function ($query) use ($credentials) {
            $query->whereHas('data_karyawans', function ($query) use ($credentials) {
                $query->where('email', $credentials['email'])
                    ->orWhere('nik', $credentials['email']);
            })
                ->orWhere('username', $credentials['email']);
        })->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            Log::info("| Auth | - Login failed for email/username/nik: {$credentials['email']} - Invalid credentials or account inactive.");
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Password atau email/username/NIK anda tidak valid, silahkan cek kembali dan pastikan akun anda aktif'), Response::HTTP_BAD_REQUEST);
        }

        // Cek status_aktif
        if ($user->status_aktif == 1) {
            auth()->logout(); // Logout user jika status_aktif bernilai 1
            Log::info("| Auth | - Login failed for user ID: {$user->id} - Account inactive since {$user->updated_at}.");
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, "Kami mendeteksi bahwa akun anda tidak aktif sejak {$user->updated_at}."), Response::HTTP_FORBIDDEN);
        }

        $token = $user->createToken('create_token_' . Str::uuid())->plainTextToken;

        // Handle device_tokens array
        if ($request->has('device_tokens')) {
            foreach ($request->device_tokens as $deviceData) {
                if (isset($deviceData['device_token']) && isset($deviceData['platform'])) {
                    // Validasi platform
                    $validPlatforms = ['web', 'android', 'ios', 'desktop'];
                    if (!in_array($deviceData['platform'], $validPlatforms)) {
                        Log::info("| Auth | - Login failed for user ID: {$user->id} - Invalid platform: {$deviceData['platform']}.");
                        return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Platform anda tidak valid, platform yang disediakan hanya android, ios, web, dan desktop'), Response::HTTP_BAD_REQUEST);
                    }

                    if (in_array($deviceData['platform'], $validPlatforms)) {
                        $user->deviceTokens()->updateOrCreate(
                            ['device_token' => $deviceData['device_token'], 'platform' => $deviceData['platform']],
                            ['device_info' => $deviceData['device_info'] ?? null]
                        );
                    }
                }
            }

            // Kirimkan welcome message ke perangkat yang terkait dengan user
            FirebaseFCMHelper::sendWelcomeMessage($user);

            Log::info("| Auth | - Login successful for user ID: {$user->id}, Name: {$user->nama}, Platform: {$deviceData['platform']}.");
        }

        Log::info("| Auth | - Login successful for user ID: {$user->id}, Name: {$user->nama}.");

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

        $userId = auth()->user();
        Log::info("| Auth | - Logout successful for user ID: {$userId->id}, Name: {$userId->nama}.");

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Anda berhasil melakukan logout.'), Response::HTTP_OK)->withCookie($deleteCookie);
    }
}
