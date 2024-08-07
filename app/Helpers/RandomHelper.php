<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\User;

class RandomHelper
{
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

	public static function convertTimeStringToSeconds($timeString)
	{
		try {
			// Konversi time string ke objek Carbon
			$carbonDate = Carbon::createFromFormat('H:i:s', $timeString);

			// Mengembalikan waktu dalam satuan detik
			return ($carbonDate->hour * 3600) + ($carbonDate->minute * 60) + $carbonDate->second;
		} catch (\Exception $e) {
			return null; // Atau tangani pengecualian sesuai kebutuhan Anda
		}
	}

	public static function convertToHoursMinutes($seconds)
	{
		try {
			$hours = floor($seconds / 3600);
			$minutes = floor(($seconds % 3600) / 60);
			return sprintf('%d Jam %d Menit', $hours, $minutes);
		} catch (\Exception $e) {
			return null; // Atau tangani pengecualian sesuai kebutuhan Anda
		}
	}
}
