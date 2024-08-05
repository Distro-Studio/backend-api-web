<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\User;

class RandomHelper
{
	// public static function generateUniqueUsername(string $fullName): string
	// {
	// 	// Mengganti spasi dan karakter non-alfanumerik pada full name dengan underscore
	// 	$usernameBase = strtolower(preg_replace("/[^a-zA-Z0-9]/", "_", $fullName));

	// 	// Ambil tanggal pembuatan sekarang
	// 	$creationDate = Carbon::now()->format('dmY');

	// 	// Gabungkan fullname dan creation date
	// 	$username = $usernameBase . '_' . $creationDate;

	// 	// Periksa apakah username sudah ada
	// 	$existingUser = User::where('username', $username)->first();

	// 	if ($existingUser) {
	// 		throw new \Exception('Username already exists.');
	// 	}

	// 	return $username;
	// }

	public static function generatePassword(int $length = 12): string
	{
		// Karakter yang akan digunakan untuk password
		$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#&()";

		// Inisialisasi password kosong
		$password = "";

		// Tambahkan karakter acak ke password
		for ($i = 0; $i < $length; $i++) {
			$password .= $chars[random_int(0, strlen($chars) - 1)];
		}

		return $password;
	}

	public static function convertToDateTimeString($dateString)
	{
		try {
			// Hilangkan bagian zona waktu ganda
			$cleanDateString = preg_replace('/\s\(.*\)$/', '', $dateString);

			// Konversi string tanggal ke objek Carbon
			$carbonDate = Carbon::parse($cleanDateString);

			// Mengembalikan datetime string
			return $carbonDate->toDateTimeString();
		} catch (\Exception $e) {
			return null; // Atau tangani pengecualian sesuai kebutuhan Anda
		}
	}

	public static function convertToDateString($dateString)
	{
		try {
			// Hilangkan bagian zona waktu ganda
			$cleanDateString = preg_replace('/\s\(.*\)$/', '', $dateString);

			// Konversi string tanggal ke objek Carbon
			$carbonDate = Carbon::parse($cleanDateString);

			// Mengembalikan datetime string
			return $carbonDate->toDateString();
		} catch (\Exception $e) {
			return null; // Atau tangani pengecualian sesuai kebutuhan Anda
		}
	}

	public static function convertToTimeString($dateString)
	{
		try {
			// Hilangkan bagian zona waktu ganda
			$cleanDateString = preg_replace('/\s\(.*\)$/', '', $dateString);

			// Konversi string tanggal ke objek Carbon
			$carbonDate = Carbon::parse($cleanDateString);

			// Mengembalikan datetime string
			return $carbonDate->toTimeString();
		} catch (\Exception $e) {
			return null; // Atau tangani pengecualian sesuai kebutuhan Anda
		}
	}
}
