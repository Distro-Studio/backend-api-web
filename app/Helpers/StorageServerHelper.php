<?php

namespace App\Helpers;

use App\Models\Berkas;
use Illuminate\Http\Request;
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
			self::$storageDomain = "https://192.168.0.20/RskiSistem24/file-storage/public";
			self::$storageUsername = "userwebrski";
			self::$storagePassword = "12345678";
		}
	}

	public static function login()
	{
		self::initDomain(); // Ensure domain is initialized
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
		self::login();
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
		$dataupload = $uploadinfo['data'];

		self::logout();

		return $dataupload;
	}

	// Multi upload
	public static function multipleUploadToServer($file, $filename = 'File Upload')
	{
		self::login();
		self::initDomain();

		$responseupload = Http::withHeaders([
			'Authorization' => 'Bearer ' . self::$token,
		])->asMultipart()->post(self::$storageDomain . '/api/upload', [
			'filename' => $filename,
			'file' => fopen($file->getRealPath(), 'r'),
			'kategori' => 'Umum'
		]);

		$uploadinfo = $responseupload->json();
		$dataupload = $uploadinfo['data'];

		self::logout();

		return $dataupload;
	}

	// Delete Berkas
	public static function deleteFromServer($file_id)
	{
		self::login();
		self::initDomain();

		$responseupload = Http::withHeaders([
			'Authorization' => 'Bearer ' . self::$token,
		])->asMultipart()->post(self::$storageDomain . '/api/delete-file', [
			'file_id' => $file_id,
		]);

		$uploadinfo = $responseupload->json();
		// if (!isset($uploadinfo['data'])) {
		// 	throw new \Exception('Error: ' . $responseupload->body());
		// }
		$dataupload = $uploadinfo['data'];

		self::logout();

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
