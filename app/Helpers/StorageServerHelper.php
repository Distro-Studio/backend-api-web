<?php

namespace App\Helpers;

use App\Models\Berkas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class StorageServerHelper
{
	private static $token = null;
	private static $storageDomain = null;
	private static $storageUsername = null;
	private static $storagePassword = null;

	// Initialize storageDomain once
	private static function initDomain()
	{
		if (self::$storageDomain === null) {
			self::$storageDomain = env('STORAGE_SERVER_DOMAIN');
			self::$storageUsername = env('USERNAME_STORAGE');
			self::$storagePassword = env('PASSWORD_STORAGE');
		}
	}

	public static function login()
	{
		self::initDomain(); // Ensure domain is initialized

		// Cek token cache dulu
		if ($token = Cache::get('storage_server_token')) {
			self::$token = $token;
			return;
		}

		$response = Http::asForm()->post(self::$storageDomain . '/api/login', [
			'username' => self::$storageUsername,
			'password' => self::$storagePassword
		]);

		$logininfo = $response->json();
		Log::info($logininfo);

		if ($response->failed() || !isset($logininfo['data']['token'])) {
			Log::error('Failed to login to storage server', [
				'status_code' => $response->status(),
				'error_message' => $response->body()
			]);
		}

		self::$token = $logininfo['data']['token'];

		// Simpan token di cache selama 5 menit (atur sesuai waktu expiry server)
		Cache::put('storage_server_token', self::$token, now('Asia/Jakarta')->addMinutes(5));
	}

	public static function logout()
	{
		if (self::$token) {
			self::initDomain();
			Http::withHeaders([
				'Authorization' => 'Bearer ' . self::$token,
			])->post(self::$storageDomain . '/api/logout');

			self::$token = null;
		}
	}

	// Single upload
	public static function uploadToServer(Request $request, $filename = 'File Upload')
	{
		if (self::$token === null) {
			self::login();
		}
		self::initDomain(); // Ensure domain is initialized
		$file = $request->file('dokumen');

		$responseupload = Http::withHeaders([
			'Authorization' => 'Bearer ' . self::$token,
		])->asMultipart()->post(self::$storageDomain . '/api/upload', [
			'filename' => $filename,
			'file' => fopen($file->getRealPath(), 'r'),
			'kategori' => 'Umum'
		]);

		$uploadinfo = $responseupload->json();
		if (!isset($uploadinfo['data'])) {
			Log::channel('storage_server_log')->error("Single Upload Failed - response tidak berisi data", [
				'status_code' => $responseupload->status(),
				'response_body' => $responseupload->body(),
				'headers' => $responseupload->headers(),
			]);
			// throw new \Exception('Error: ' . $responseupload->body());
		}
		$dataupload = $uploadinfo['data'];

		return $dataupload;
	}

	// Multi upload
	public static function multipleUploadToServer($file, $filename = 'File Upload')
	{
		if (self::$token === null) {
			self::login();
		}

		self::initDomain();

		$responseupload = Http::withHeaders([
			'Authorization' => 'Bearer ' . self::$token,
		])->asMultipart()->post(self::$storageDomain . '/api/upload', [
			'filename' => $filename,
			'file' => fopen($file->getRealPath(), 'r'),
			'kategori' => 'Umum'
		]);

		if ($responseupload->status() == 429) {
			$retryAfter = (int) $responseupload->header('retry-after', 10); // default 10 detik
			Log::channel('storage_server_log')->warning("Rate limit reached. Retrying after {$retryAfter} seconds.");

			sleep($retryAfter); // tunggu sebelum retry

			// Coba upload ulang sekali
			$responseupload = Http::withHeaders([
				'Authorization' => 'Bearer ' . self::$token,
			])->asMultipart()->post(self::$storageDomain . '/api/upload', [
				'filename' => $filename,
				'file' => fopen($file->getRealPath(), 'r'),
				'kategori' => 'Umum'
			]);
		}

		$uploadinfo = $responseupload->json();

		if (!isset($uploadinfo['data'])) {
			Log::channel('storage_server_log')->error("Multiple Upload Failed - response tidak berisi data", [
				'status_code' => $responseupload->status(),
				'response_body' => $responseupload->body(),
				'headers' => $responseupload->headers(),
			]);
		}
		$dataupload = $uploadinfo['data'];

		return $dataupload;
	}

	// Delete Berkas
	public static function deleteFromServer($file_id)
	{
		if (self::$token === null) {
			self::login();
		}

		self::initDomain();

		$responseupload = Http::withHeaders([
			'Authorization' => 'Bearer ' . self::$token,
		])->asMultipart()->post(self::$storageDomain . '/api/delete-file', [
			'file_id' => $file_id,
		]);

		$uploadinfo = $responseupload->json();
		if (!isset($uploadinfo['data'])) {
			Log::channel('storage_server_log')->error("Delete Failed - response tidak berisi data", [
				'status_code' => $responseupload->status(),
				'response_body' => $responseupload->body(),
				'headers' => $responseupload->headers(),
			]);
			// throw new \Exception('Error: ' . $responseupload->body());
		}
		$dataupload = $uploadinfo['data'];

		return $dataupload;
	}

	private static function getFileNameFromHeader($header)
	{
		if (preg_match('/filename="(.+)"/', $header, $matches)) {
			return $matches[1];
		}
		return 'downloaded_file';
	}

	public static function getExtensionFromMimeType($mimeType)
	{
		$mimeMap = [
			'text/plain' => 'txt',
			'text/html' => 'html',
			'text/css' => 'css',
			'text/csv' => 'csv',
			'text/xml' => 'xml',
			'image/jpeg' => 'jpg',
			'image/png' => 'png',
			'image/gif' => 'gif',
			'image/bmp' => 'bmp',
			'image/webp' => 'webp',
			'image/svg+xml' => 'svg',
			'audio/mpeg' => 'mp3',
			'audio/ogg' => 'ogg',
			'audio/wav' => 'wav',
			'audio/x-ms-wma' => 'wma',
			'video/mp4' => 'mp4',
			'video/ogg' => 'ogv',
			'video/webm' => 'webm',
			'video/x-msvideo' => 'avi',
			'video/x-ms-wmv' => 'wmv',
			'application/pdf' => 'pdf',
			'application/zip' => 'zip',
			'application/x-rar-compressed' => 'rar',
			'application/vnd.ms-excel' => 'xls',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
			'application/msword' => 'doc',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
			'application/vnd.ms-powerpoint' => 'ppt',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
			'application/json' => 'json',
			'application/javascript' => 'js',
			'application/vnd.oasis.opendocument.text' => 'odt',
			'application/vnd.oasis.opendocument.spreadsheet' => 'ods',
			'application/vnd.oasis.opendocument.presentation' => 'odp',
			'font/otf' => 'otf',
			'font/ttf' => 'ttf',
			'font/woff' => 'woff',
			'font/woff2' => 'woff2',
			'application/octet-stream' => 'bin',
		];

		return $mimeMap[$mimeType] ?? 'bin';
	}
}
