<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Notifikasi;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class InboxController extends Controller
{
    public function calculatedUnread()
    {
        $user = Auth::user();
        $unreadCount = Notifikasi::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Jumlah notifikasi yang belum dibaca berhasil dihitung.',
            'unread_count' => $unreadCount
        ], Response::HTTP_OK);
    }

    public function index()
    {
        $user = Auth::user();
        $notifikasi = Notifikasi::where('user_id', $user->id)
            ->orderBy('is_read', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($notifikasi->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data notifikasi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = $notifikasi->map(function ($item) {
            return [
                'id' => $item->id,
                'kategori_notifikasi' => $item->kategori_notifikasis,
                'user' => $item->users,
                'message' => $item->message,
                'is_read' => $item->is_read,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at
            ];
        });

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data notifikasi berhasil ditampilkan.',
            'data' => $formattedData
        ], Response::HTTP_OK);
    }

    public function show($id)
    {
        $user = Auth::user();
        $notifikasi = Notifikasi::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$notifikasi) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data notifikasi tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Check if the notifikasi is unread, then mark it as read
        if (!$notifikasi->is_read) {
            $notifikasi->is_read = true;
            $notifikasi->save();
        }

        $formattedData = [
            'id' => $notifikasi->id,
            'kategori_notifikasi' => $notifikasi->kategori_notifikasis,
            'user' => $notifikasi->users,
            'message' => $notifikasi->message,
            'is_read' => $notifikasi->is_read,
            'created_at' => $notifikasi->created_at,
            'updated_at' => $notifikasi->updated_at
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data notifikasi berhasil ditampilkan.',
            'data' => $formattedData
        ], Response::HTTP_OK);
    }

    public function destroyRead()
    {
        $user = Auth::user();
        $deletedCount = Notifikasi::where('user_id', $user->id)
            ->where('is_read', true)
            ->delete();

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Berhasil menghapus {$deletedCount} notifikasi yang sudah dibaca.",
            'deleted_count' => $deletedCount
        ], Response::HTTP_OK);
    }
}
