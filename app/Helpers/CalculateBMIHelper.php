<?php

namespace App\Helpers;

class CalculateBMIHelper
{
	public static function calculateBMI($weight, $height)
	{
		// Pastikan berat dan tinggi badan tidak null, tidak nol, dan tidak negatif untuk mencegah error
		if (is_null($weight) || is_null($height) || $weight <= 0 || $height <= 0) {
			return [
				'bmi_value' => null,
				'bmi_ket' => null
			];
		}

		// Konversi tinggi badan dari cm ke meter
		$heightInMeters = $height / 100;

		// Hitung nilai BMI
		$bmi = $weight / ($heightInMeters * $heightInMeters);

		// Tentukan kategori BMI berdasarkan hasil perhitungan
		if ($bmi < 18.5) {
			$category = 'Berat badan kurang (Underweight)';
		} elseif ($bmi >= 18.5 && $bmi <= 24.9) {
			$category = 'Berat badan normal';
		} elseif ($bmi >= 25 && $bmi <= 29.9) {
			$category = 'Berat badan berlebih (Overweight)';
		} else {
			$category = 'Obesitas (Obese)';
		}

		// Return hasil perhitungan BMI dan kategori
		return [
			'bmi_value' => $bmi,
			'bmi_ket' => $category
		];
	}
}
