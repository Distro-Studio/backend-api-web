<?php

namespace App\Helpers;

use App\Models\User;

class RandomHelper
{
	public static function generateUniqueUsername(string $fullName, int $maxLength = 20): string
	{
		// Mengganti spasi dan karakter non-alfanumerik dengan underscore
		$usernameBase = strtolower(preg_replace("/[^a-zA-Z0-9]/", "_", $fullName));

		// Tentukan panjang suffix
		$maxSuffixLength = $maxLength - strlen($usernameBase);
		$maxSuffixLength = max(4, $maxSuffixLength);  // Pastikan suffix minimal 4 karakter

		// Karakter untuk suffix
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

		do {
			$randomSuffix = '';
			for ($i = 0; $i < $maxSuffixLength; $i++) {
				$randomSuffix .= $chars[random_int(0, strlen($chars) - 1)];
			}
			// Gabungkan base username dengan random suffix
			$username = $usernameBase . $randomSuffix;

			// Periksa apakah username sudah ada
			$existingUser = User::where('username', $username)->first();
		} while ($existingUser); // Ulangi sampai mendapatkan username yang unik

		return $username;
	}


	public static function generatePassword(string $email, int $length = 20): string
	{
		$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#&()";
		// Mengambil nama pengguna dari email (bagian sebelum '@')
		$emailPrefix = substr($email, 0, strpos($email, '@'));
		// Panjang karakter acak yang akan digunakan
		$randomCharsLength = max(0, $length - strlen($emailPrefix)); // Pastikan tidak negatif
		$randomChars = "";

		for ($i = 0; $i < $randomCharsLength; $i++) {
			$randomChars .= $chars[random_int(0, strlen($chars) - 1)];
		}

		// Gabungkan bagian email dengan karakter acak
		$password = $emailPrefix . $randomChars;
		return $password;
	}
}
