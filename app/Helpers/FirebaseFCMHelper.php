<?php

namespace App\Helpers;

use App\Models\Notifikasi;
use App\Models\User;
use App\Services\FirebaseAccessTokenService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FirebaseFCMHelper
{
	protected static function getClient()
	{
		return new Client();
	}

	protected static function getAccessToken()
	{
		return app(FirebaseAccessTokenService::class)->getAccessToken();
	}

	/**
	 * Mengirimkan welcome message ke semua perangkat yang terkait dengan user
	 *
	 * @param User $user
	 * @return void
	 */
	public static function sendWelcomeMessage(User $user)
	{
		// Ambil semua device tokens milik user
		$userDeviceTokens = $user->deviceTokens()->get();

		if (!$userDeviceTokens->isEmpty()) {
			// Loop untuk mengirim pesan per platform
			foreach ($userDeviceTokens as $device) {
				$message = '';
				switch ($device->platform) {
					case 'android':
						$message = "Selamat datang di aplikasi mobile kami, {$user->nama}!";
						break;
					case 'ios':
						$message = "Selamat datang di aplikasi iOS kami, {$user->nama}!";
						break;
					case 'web':
						$message = "Selamat datang di situs web kami, {$user->nama}!";
						break;
					case 'desktop':
						$message = "Selamat datang di aplikasi desktop kami, {$user->nama}!";
						break;
					default:
						$message = "Selamat datang, {$user->nama}!";
						break;
				}

				// Kirim notifikasi ke perangkat
				self::sendNotificationToDevice($device->device_token, $message, $user);
			}
		}
	}

	/**
	 * Membuat notifikasi di database dan langsung mengirim push notification FCM
	 *
	 * @param array $data (format data untuk tabel notifikasis)
	 * @return Notifikasi
	 */
	public static function createNotification(array $data)
	{
		$notifikasi = Notifikasi::create($data);
		$user = User::find($notifikasi->user_id);

		if ($user) {
			// Ambil semua device tokens milik user
			$userDeviceTokens = $user->deviceTokens()->pluck('device_token')->toArray();

			// Pastikan ada device_token yang valid
			if (!empty($userDeviceTokens)) {
				// URL untuk FCM API
				$url = "https://fcm.googleapis.com/v1/projects/" . config('firebase.project_id') . "/messages:send";

				// Setup payload untuk FCM
				$payload = [
					'message' => [
						'tokens' => $userDeviceTokens,  // Kirim ke semua token
						'notification' => [
							'title' => 'Notifikasi Baru',  // Bisa custom sesuai keperluan
							'body' => $notifikasi->message,
						],
						'data' => [
							'notifikasi_id' => (string) $notifikasi->id,
							'kategori_notifikasi_id' => (string) $notifikasi->kategori_notifikasi_id,
						],
					],
				];

				// Set header untuk Authorization
				$headers = [
					'Authorization' => 'Bearer ' . self::getAccessToken(),
					'Content-Type'  => 'application/json',
				];

				try {
					// Kirim HTTP POST request ke FCM
					self::getClient()->post($url, [
						'headers' => $headers,
						'json' => $payload,
					]);
				} catch (\Exception $e) {
					Log::error('| FCM Notification | Push Notification Error: ' . $e->getMessage());
				}
			}
		}

		return $notifikasi;
	}

	/**
	 * Mengirimkan notifikasi ke perangkat tertentu
	 *
	 * @param string $deviceToken
	 * @param string $message
	 * @param User $user
	 * @return void
	 */
	private static function sendNotificationToDevice(string $deviceToken, string $message, User $user)
	{
		// URL untuk FCM API
		$url = "https://fcm.googleapis.com/v1/projects/" . config('firebase.project_id') . "/messages:send";

		// Setup payload untuk FCM
		$payload = [
			'message' => [
				'token' => $deviceToken,
				'notification' => [
					'title' => 'Welcome!',  // Judul notifikasi
					'body' => $message,     // Body notifikasi yang disesuaikan
				],
				'data' => [
					'user_id' => (string) $user->id,
					'name' => $user->nama,
				],
			],
		];

		// Set header untuk Authorization
		$headers = [
			'Authorization' => 'Bearer ' . self::getAccessToken(),
			'Content-Type'  => 'application/json',
		];

		try {
			// Kirim HTTP POST request ke FCM
			self::getClient()->post($url, [
				'headers' => $headers,
				'json' => $payload,
			]);
			Log::info("| FCM | - Welcome message sent to device for user ID: {$user->id}, Token: {$deviceToken}");
		} catch (\Exception $e) {
			Log::error("| FCM | - Failed to send welcome message to device: " . $e->getMessage());
		}
	}
}
