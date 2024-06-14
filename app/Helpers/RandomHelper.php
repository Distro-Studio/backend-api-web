<?php

namespace App\Helpers;

use App\Models\User;

class RandomHelper
{
	public static function generateUniqueUsername(string $fullName, string $email): string
	{
		// Mengganti spasi dan karakter non-alfanumerik pada full name dengan underscore
		$usernameBase = strtolower(preg_replace("/[^a-zA-Z0-9]/", "_", $fullName));

		// Ambil bagian sebelum '@' dari email
		$emailBase = strtolower(strstr($email, '@', true));

		// Gabungkan fullname dan emailBase
		$username = $usernameBase . '_' . $emailBase;

		// Periksa apakah username sudah ada
		$existingUser = User::where('username', $username)->first();

		if ($existingUser) {
			throw new \Exception('Username already exists.');
		}

		return $username;
	}

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
}
