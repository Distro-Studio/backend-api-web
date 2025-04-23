<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LogHelper
{
	public static function logAction(string $entity, string $action, int|string $relatedId)
    {
        $user = Auth::user();
        $actionVerb = [
            'create' => 'membuat',
            'update' => 'memperbarui',
            'delete' => 'menghapus',
			'import' => 'mengimport'
        ];

        $actionLabel = ucfirst($action) . ' ' . $entity;

        $message = "{$user->nama} telah {$actionVerb[$action]} data {$entity} dengan karyawan ID {$relatedId}.";
        
        // Bisa diarahkan ke database, file log, atau keduanya
        Log::channel('daily')->info($message);

        return $message;
    }

}