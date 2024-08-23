<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

// class StorageSeverHelper
// {
// 	public static function uploadToServer(Request $request, $filename = 'File Upload')
// 	{
// 		$response = Http::asForm()->post('http://127.0.0.1:8001/api/login', [
// 			'username' => env('USERNAME_STORAGE'),
// 			'password' => env('PASSWORD_STORAGE')
// 		]);
// 		$logininfo = $response->json();
// 		$token = $logininfo['data']['token'];
// 		$file = $request->file('dokumen');

// 		$responseupload = Http::withHeaders([
// 			'Authorization' => 'Bearer ' . $token,
// 		])->asMultipart()->post('http://127.0.0.1:8001/api/upload', [
// 			'filename' => $filename,
// 			'file' => fopen($file->getRealPath(), 'r'),
// 			'kategori' => 'Umum'
// 		]);

// 		$uploadinfo = $responseupload->json();
// 		$dataupload = $uploadinfo['data'];

// 		$logout = Http::withHeaders([
// 			'Authorization' => 'Bearer ' . $token,
// 		])->post('http://127.0.0.1:8001/api/logout');

// 		return $dataupload;
// 	}
// }

class StorageServerHelper
{
	private static $token = null;

	public static function login()
	{
		$response = Http::asForm()->post(env('STORAGE_SERVER_DOMAIN') . '/api/login', [
			'username' => env('USERNAME_STORAGE'),
			'password' => env('PASSWORD_STORAGE')
		]);

		$logininfo = $response->json();
		Log::info($logininfo);

		if ($response->failed() || !isset($logininfo['data']['token'])) {
			// throw new \Exception('Gagal login ke server berkas.');
			Log::error('Failed to login to storage server', [
				'status_code' => $response->status(),
				'error_message' => $response->body()
			]);
		}

		self::$token = $logininfo['data']['token'];
	}

	public static function logout()
	{
		if (self::$token) {
			Http::withHeaders([
				'Authorization' => 'Bearer ' . self::$token,
			])->post(env('STORAGE_SERVER_DOMAIN') . '/api/logout');

			self::$token = null;
		}
	}

	// single upload
	public static function uploadToServer(Request $request, $filename = 'File Upload')
	{
		self::login();
		$file = $request->file('dokumen');

		$responseupload = Http::withHeaders([
			'Authorization' => 'Bearer ' . self::$token,
		])->asMultipart()->post(env('STORAGE_SERVER_DOMAIN') . '/api/upload', [
			'filename' => $filename,
			// 'filename' => $random_filename,
			'file' => fopen($file->getRealPath(), 'r'),
			'kategori' => 'Umum'
		]);

		$uploadinfo = $responseupload->json();
		$dataupload = $uploadinfo['data'];

		self::logout();

		return $dataupload;
	}

	// multi upload
	public static function multipleUploadToServer($file, $filename = 'File Upload')
	{
		self::login();

		$responseupload = Http::withHeaders([
			'Authorization' => 'Bearer ' . self::$token,
		])->asMultipart()->post(env('STORAGE_SERVER_DOMAIN') . '/api/upload', [
			'filename' => $filename,
			'file' => fopen($file->getRealPath(), 'r'),
			'kategori' => 'Umum'
		]);

		$uploadinfo = $responseupload->json();
		$dataupload = $uploadinfo['data'];

		self::logout();

		return $dataupload;
	}

	private static function getFileNameFromHeader($header)
	{
		// Extract filename from Content-Disposition header
		if (preg_match('/filename="(.+)"/', $header, $matches)) {
			return $matches[1];
		}
		return 'downloaded_file';
	}

	public static function getExtensionFromMimeType($mimeType)
	{
		$mimeMap = [
			// Text files
			'text/plain' => 'txt',
			'text/html' => 'html',
			'text/css' => 'css',
			'text/csv' => 'csv',
			'text/xml' => 'xml',

			// Image files
			'image/jpeg' => 'jpg',
			'image/png' => 'png',
			'image/gif' => 'gif',
			'image/bmp' => 'bmp',
			'image/webp' => 'webp',
			'image/svg+xml' => 'svg',

			// Audio files
			'audio/mpeg' => 'mp3',
			'audio/ogg' => 'ogg',
			'audio/wav' => 'wav',
			'audio/x-ms-wma' => 'wma',

			// Video files
			'video/mp4' => 'mp4',
			'video/ogg' => 'ogv',
			'video/webm' => 'webm',
			'video/x-msvideo' => 'avi',
			'video/x-ms-wmv' => 'wmv',

			// Application files
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

			// Font files
			'font/otf' => 'otf',
			'font/ttf' => 'ttf',
			'font/woff' => 'woff',
			'font/woff2' => 'woff2',

			// Binary and others
			'application/octet-stream' => 'bin',
		];

		return $mimeMap[$mimeType] ?? 'bin';
	}
}
