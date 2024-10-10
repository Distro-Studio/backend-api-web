<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StorageLocalHelper
{
	/**
	 * Simpan foto profil ke direktori storage
	 *
	 * @param UploadedFile $file
	 * @param string $directory
	 * @return string $filePath
	 */
	public static function storePhoto(UploadedFile $file, $directory)
	{
		$extension = $file->getClientOriginalExtension();
		$filename = Str::random(25) . '.' . $extension;
		$destinationPath = public_path($directory);
		$file->move($destinationPath, $filename);
		return $directory . '/' . $filename;
	}

	/**
	 * Hapus foto profil yang ada di direktori storage
	 *
	 * @param string $filePath
	 * @return void
	 */
	public static function deletePhoto($filePath)
	{
		$fullPath = public_path($filePath);
		if (file_exists($fullPath)) {
			unlink($fullPath);
		}
	}
}
