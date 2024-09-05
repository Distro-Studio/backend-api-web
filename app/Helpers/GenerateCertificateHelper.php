<?php

namespace App\Helpers;

use Exception;
use App\Models\Berkas;
use Illuminate\Http\File;
use Illuminate\Support\Str;
use App\Models\KategoriBerkas;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use App\Helpers\StorageServerHelper;
use Illuminate\Support\Facades\View;

class GenerateCertificateHelper
{
	public static function generateCertificate($diklat, $user)
	{
		try {
			// Prepare data
			$certificateData = [
				'user' => $user,
				'diklat' => $diklat,
			];

			// Step 1: Render blade
			$template = View::make('certificate.user_certificates', $certificateData)->render();

			// Step 2: Generate PDF
			$random_filename = Str::random(20);
			$filePath = storage_path('certificates'); // Direktori untuk menyimpan PDF
			if (!file_exists($filePath)) {
				mkdir($filePath, 0777, true); // Buat direktori jika belum ada
			}

			$fullFilePath = $filePath . DIRECTORY_SEPARATOR . $random_filename . '.pdf';
			$pdf = Pdf::loadHTML($template)->setPaper('A4', 'landscape');
			$pdf->save($fullFilePath);

			// Step 3: Upload to storage
			StorageServerHelper::login();
			$file = new File($fullFilePath);
			$dataupload = StorageServerHelper::multipleUploadToServer($file, $random_filename);

			$kategoriBerkas = KategoriBerkas::where('label', 'System')->first();
			if (!$kategoriBerkas) {
				throw new Exception('Kategori berkas tidak ditemukan.');
			}

			$berkas = Berkas::create([
				'user_id' => $user->id,
				'file_id' => $dataupload['id_file']['id'],
				'nama' => 'Sertifikat ' . $diklat->nama,
				'kategori_berkas_id' => $kategoriBerkas->id,
				'status_berkas_id' => 2,
				'path' => $dataupload['path'],
				'tgl_upload' => now(),
				'nama_file' => $dataupload['nama_file'],
				'ext' => $dataupload['ext'],
				'size' => $dataupload['size'],
			]);
			Log::info('Sertifikat untuk ' . $user->nama . ' berhasil di upload.');

			if (!$berkas) {
				throw new Exception('Sertifikat gagal di upload');
			}

			StorageServerHelper::logout();
		} catch (Exception $e) {
			Log::error('Error generating certificate: ' . $e->getMessage());
			throw $e;
		} finally {
			// Step 4: Cleanup files in temp storage
			if (isset($fullFilePath) && file_exists($fullFilePath)) {
				unlink($fullFilePath);
			}
		}
	}
}
