<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;

class RandomHelper
{
	public static function generatePassword(int $length = 12): string
	{
		// Karakter yang akan digunakan untuk password
		$chars = Str::random($length);

		// Inisialisasi password kosong
		$password = "";

		// Tambahkan karakter acak ke password
		for ($i = 0; $i < $length; $i++) {
			$password .= $chars[random_int(0, strlen($chars) - 1)];
		}

		return $password;
	}

	public static function generateUsername($nama): string
	{
		// Ubah string nama menjadi lowercase
		$nama = strtolower($nama);

		$namaArray = explode(' ', $nama);

		// Jika ada lebih dari satu kata
		if (count($namaArray) > 1) {
			// Ambil kata pertama dan kata terakhir
			$namaDepan = $namaArray[0];
			$namaBelakang = $namaArray[count($namaArray) - 1];

			// Gabungkan dengan titik di antara nama depan dan nama belakang
			return $namaDepan . '.' . $namaBelakang;
		} else {
			// Jika hanya ada satu kata
			return $namaArray[0];
		}
	}

	// ini untuk -> Tue Aug 13 2024 00:00:00 GMT+0700 (Western Indonesia Time)
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

	// ini untuk -> 00:00:00 ke detik
	public static function convertTimeStringToSeconds($timeString)
	{
		try {
			// Konversi time string ke objek Carbon
			$carbonDate = Carbon::createFromFormat('H:i:s', $timeString);
			return ($carbonDate->hour * 3600) + ($carbonDate->minute * 60) + $carbonDate->second;
		} catch (\Exception $e) {
			return null;
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

	// ini untuk -> 28-08-2024
	public static function convertSpecialDateFormat($dateString)
	{
		try {
			$carbonDate = Carbon::createFromFormat('d-m-Y', $dateString)->format('Y-m-d');

			return $carbonDate;
		} catch (\Exception $e) {
			return null;
		}
	}
}
