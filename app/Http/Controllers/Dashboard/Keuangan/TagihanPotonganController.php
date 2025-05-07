<?php

namespace App\Http\Controllers\Dashboard\Keuangan;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Penggajian;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\TagihanPotongan;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Exports\Keuangan\TagihanPotonganExport;
use App\Imports\Keuangan\TagihanPotonganImport;
use App\Http\Requests\StoreTagihanPotonganRequest;
use App\Http\Requests\UpdateTagihanPotonganRequest;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use App\Http\Requests\Excel_Import\ImportTagihanPotonganRequest;
use App\Models\KategoriTagihanPotongan;
use Illuminate\Support\Facades\Auth;

class TagihanPotonganController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Tentukan limit default
        $limit = $request->input('limit', 10);
        $tagihanPotongan = TagihanPotongan::query()->orderBy('created_at', 'desc');

        // Search
        if (isset($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $tagihanPotongan->where(function ($query) use ($searchTerm) {
                $query->whereHas('tagihan_karyawans.users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })->orWhereHas('tagihan_karyawans', function ($query) use ($searchTerm) {
                    $query->where('nik', 'like', $searchTerm);
                });
            });
        }

        if ($limit == 0) {
            $dataTagihan = $tagihanPotongan->get();
            $paginationData = null;
        } else {
            $limit = is_numeric($limit) ? (int)$limit : 10;
            $dataTagihan = $tagihanPotongan->paginate($limit);

            $paginationData = [
                'links' => [
                    'first' => $dataTagihan->url(1),
                    'last' => $dataTagihan->url($dataTagihan->lastPage()),
                    'prev' => $dataTagihan->previousPageUrl(),
                    'next' => $dataTagihan->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $dataTagihan->currentPage(),
                    'last_page' => $dataTagihan->lastPage(),
                    'per_page' => $dataTagihan->perPage(),
                    'total' => $dataTagihan->total(),
                ]
            ];
        }

        if ($dataTagihan->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data tagihan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Format data untuk output
        $baseUrl = env('STORAGE_SERVER_DOMAIN');
        $formattedData = $dataTagihan->map(function ($tagihanPotongan) use ($baseUrl) {
            return [
                'id' => $tagihanPotongan->id,
                'user' => $tagihanPotongan->tagihan_karyawans->users ? [
                    'id' => $tagihanPotongan->tagihan_karyawans->users->id,
                    'nama' => $tagihanPotongan->tagihan_karyawans->users->nama,
                    'username' => $tagihanPotongan->tagihan_karyawans->users->username,
                    'email_verified_at' => $tagihanPotongan->tagihan_karyawans->users->email_verified_at,
                    'data_karyawan_id' => $tagihanPotongan->tagihan_karyawans->users->data_karyawan_id,
                    'foto_profil' => $tagihanPotongan->tagihan_karyawans->users->foto_profiles ? [
                        'id' => $tagihanPotongan->tagihan_karyawans->users->foto_profiles->id,
                        'user_id' => $tagihanPotongan->tagihan_karyawans->users->foto_profiles->user_id,
                        'file_id' => $tagihanPotongan->tagihan_karyawans->users->foto_profiles->file_id,
                        'nama' => $tagihanPotongan->tagihan_karyawans->users->foto_profiles->nama,
                        'nama_file' => $tagihanPotongan->tagihan_karyawans->users->foto_profiles->nama_file,
                        'path' => $baseUrl . $tagihanPotongan->tagihan_karyawans->users->foto_profiles->path,
                        'ext' => $tagihanPotongan->tagihan_karyawans->users->foto_profiles->ext,
                        'size' => $tagihanPotongan->tagihan_karyawans->users->foto_profiles->size,
                    ] : null,
                    'data_completion_step' => $tagihanPotongan->tagihan_karyawans->users->data_completion_step,
                    'status_aktif' => $tagihanPotongan->tagihan_karyawans->users->status_aktif,
                    'created_at' => $tagihanPotongan->tagihan_karyawans->users->created_at,
                    'updated_at' => $tagihanPotongan->tagihan_karyawans->users->updated_at
                ] : null,
                'kategori_tagihan' => $tagihanPotongan->tagihan_kategoris,
                'status_tagihan' => $tagihanPotongan->tagihan_status,
                'besaran' => $tagihanPotongan->besaran,
                'tenor' => $tagihanPotongan->tenor ?? null,
                'sisa_tagihan' => $tagihanPotongan->sisa_tagihan ?? null,
                'sisa_tenor' => $tagihanPotongan->sisa_tenor ?? null,
                'bulan_mulai' => $tagihanPotongan->bulan_mulai ?? null,
                'bulan_selesai' => $tagihanPotongan->bulan_selesai ?? null,
                'created_at' => $tagihanPotongan->created_at,
                'updated_at' => $tagihanPotongan->updated_at
            ];
        },);

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data tagihan potongan berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function store(StoreTagihanPotonganRequest $request)
    {
        if (!Gate::allows('create penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $userIds = $request->input('user_id', []);
        $dataKaryawanIds = User::whereIn('id', $userIds)->pluck('data_karyawan_id');

        $bulanMulai = Carbon::createFromFormat('d-m-Y', $data['bulan_mulai'], 'Asia/Jakarta');
        $bulanSelesai = Carbon::createFromFormat('d-m-Y', $data['bulan_selesai'], 'Asia/Jakarta');

        // Menghitung jarak antara bulan_mulai dan bulan_selesai dalam bulan
        $tenor = $bulanMulai->diffInMonths($bulanSelesai) + 1;

        $currentDate = Carbon::now('Asia/Jakarta');
        if ($bulanMulai->year < $currentDate->year || ($bulanMulai->year == $currentDate->year && $bulanMulai->month < $currentDate->month)) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Pembuatan tagihan potongan tidak dapat diproses, karena bulan mulai sudah terlewat."), Response::HTTP_BAD_REQUEST);
        }

        foreach ($dataKaryawanIds as $dataKaryawanId) {
            $existingTagihan = TagihanPotongan::where('data_karyawan_id', $dataKaryawanId)
                ->where('kategori_tagihan_id', $data['kategori_tagihan_id'])
                ->where('status_tagihan_id', '!=', 3)
                ->where(function ($query) use ($bulanMulai, $bulanSelesai) {
                    $query->where(function ($subQuery) use ($bulanMulai) {
                        $subQuery->whereRaw('STR_TO_DATE(bulan_mulai, "%d-%m-%Y") <= ?', [$bulanMulai->format('Y-m-d')])
                            ->whereRaw('STR_TO_DATE(bulan_selesai, "%d-%m-%Y") >= ?', [$bulanMulai->format('Y-m-d')]);
                    })->orWhere(function ($subQuery) use ($bulanSelesai) {
                        $subQuery->whereRaw('STR_TO_DATE(bulan_mulai, "%d-%m-%Y") <= ?', [$bulanSelesai->format('Y-m-d')])
                            ->whereRaw('STR_TO_DATE(bulan_selesai, "%d-%m-%Y") >= ?', [$bulanSelesai->format('Y-m-d')]);
                    });
                })
                ->exists();
            $namaKaryawan = User::where('data_karyawan_id', $dataKaryawanId)->value('nama');
            $kategoriTagihan = KategoriTagihanPotongan::where('id', $data['kategori_tagihan_id'])->value('label');
            if ($existingTagihan) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Pembuatan tagihan potongan tidak dapat diproses, karena ada tagihan '{$kategoriTagihan}' yang masih berjalan untuk karyawan '{$namaKaryawan}'."), Response::HTTP_BAD_REQUEST);
            }

            $existingPenggajian = Penggajian::where('data_karyawan_id', $dataKaryawanId)
                ->whereYear('tgl_penggajian', $bulanMulai->year)
                ->whereMonth('tgl_penggajian', $bulanMulai->month)
                ->first();
            if ($existingPenggajian) {
                return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Pembuatan tagihan potongan tidak dapat diproses, karena penggajian sudah dilakukan pada bulan '{$bulanMulai->format('F Y')}'."), Response::HTTP_BAD_REQUEST);
            }
        }

        foreach ($dataKaryawanIds as $dataKaryawanId) {
            TagihanPotongan::create([
                'data_karyawan_id' => $dataKaryawanId,
                'kategori_tagihan_id' => $data['kategori_tagihan_id'],
                'status_tagihan_id' => 1,
                'besaran' => $data['besaran'],
                'tenor' => $tenor,
                'bulan_mulai' => $data['bulan_mulai'],
                'bulan_selesai' => $data['bulan_selesai'],
            ]);
        }

        $bulan_mulai = Carbon::createFromFormat('d-m-Y', $data['bulan_mulai'])->locale('id')->isoFormat('MMMM YYYY');
        return response()->json(new WithoutDataResource(Response::HTTP_CREATED, "Data tagihan potongan periode '{$bulan_mulai}' berhasil ditambahkan untuk karyawan terkait."), Response::HTTP_CREATED);
    }

    public function show($id)
    {
        if (!Gate::allows('view penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $tagihanPotongan = TagihanPotongan::find($id);
        if (!$tagihanPotongan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tagihan potongan terkait tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $baseUrl = env('STORAGE_SERVER_DOMAIN');
        $formattedData = [
            'id' => $tagihanPotongan->id,
            'user' => $tagihanPotongan->tagihan_karyawans->users ? [
                'id' => $tagihanPotongan->tagihan_karyawans->users->id,
                'nama' => $tagihanPotongan->tagihan_karyawans->users->nama,
                'username' => $tagihanPotongan->tagihan_karyawans->users->username,
                'email_verified_at' => $tagihanPotongan->tagihan_karyawans->users->email_verified_at,
                'data_karyawan_id' => $tagihanPotongan->tagihan_karyawans->users->data_karyawan_id,
                'foto_profil' => $tagihanPotongan->tagihan_karyawans->users->foto_profiles ? [
                    'id' => $tagihanPotongan->tagihan_karyawans->users->foto_profiles->id,
                    'user_id' => $tagihanPotongan->tagihan_karyawans->users->foto_profiles->user_id,
                    'file_id' => $tagihanPotongan->tagihan_karyawans->users->foto_profiles->file_id,
                    'nama' => $tagihanPotongan->tagihan_karyawans->users->foto_profiles->nama,
                    'nama_file' => $tagihanPotongan->tagihan_karyawans->users->foto_profiles->nama_file,
                    'path' => $baseUrl . $tagihanPotongan->tagihan_karyawans->users->foto_profiles->path,
                    'ext' => $tagihanPotongan->tagihan_karyawans->users->foto_profiles->ext,
                    'size' => $tagihanPotongan->tagihan_karyawans->users->foto_profiles->size,
                ] : null,
                'data_completion_step' => $tagihanPotongan->tagihan_karyawans->users->data_completion_step,
                'status_aktif' => $tagihanPotongan->tagihan_karyawans->users->status_aktif,
                'created_at' => $tagihanPotongan->tagihan_karyawans->users->created_at,
                'updated_at' => $tagihanPotongan->tagihan_karyawans->users->updated_at
            ] : null,
            'kategori_tagihan' => $tagihanPotongan->tagihan_kategoris,
            'status_tagihan' => $tagihanPotongan->tagihan_status,
            'besaran' => $tagihanPotongan->besaran,
            'tenor' => $tagihanPotongan->tenor ?? null,
            'sisa_tagihan' => $tagihanPotongan->sisa_tagihan ?? null,
            'sisa_tenor' => $tagihanPotongan->sisa_tenor ?? null,
            'bulan_mulai' => $tagihanPotongan->bulan_mulai ?? null,
            'bulan_selesai' => $tagihanPotongan->bulan_selesai ?? null,
            'created_at' => $tagihanPotongan->created_at,
            'updated_at' => $tagihanPotongan->updated_at
        ];

        $bulan_mulai = Carbon::createFromFormat('d-m-Y', $tagihanPotongan->bulan_mulai)->locale('id')->isoFormat('MMMM YYYY');
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Data tagihan potongan kategori '{$tagihanPotongan->tagihan_kategoris->label}' periode '{$bulan_mulai}' berhasil ditampilkan.",
            'data' => $formattedData
        ], Response::HTTP_OK);
    }

    public function update(UpdateTagihanPotonganRequest $request, $id)
    {
        if (!Gate::allows('edit penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $tagihanPotongan = TagihanPotongan::find($id);
        if (!$tagihanPotongan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tagihan potongan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $data = $request->validated();

        // Konversi bulan_mulai dan bulan_selesai ke format Carbon untuk memudahkan validasi
        $bulanMulai = !empty($data['bulan_mulai'])
            ? Carbon::createFromFormat('d-m-Y', $data['bulan_mulai'], 'Asia/Jakarta')
            : Carbon::createFromFormat('d-m-Y', $tagihanPotongan->bulan_mulai, 'Asia/Jakarta');

        $bulanSelesai = !empty($data['bulan_selesai'])
            ? Carbon::createFromFormat('d-m-Y', $data['bulan_selesai'], 'Asia/Jakarta')
            : Carbon::createFromFormat('d-m-Y', $tagihanPotongan->bulan_selesai, 'Asia/Jakarta');

        // Menghitung tenor
        $tenor = $bulanMulai->diffInMonths($bulanSelesai);
        $data['tenor'] = $tenor;

        // Cek bulan_mulai di masa lalu
        $currentDate = Carbon::now('Asia/Jakarta');
        if ($bulanMulai->year < $currentDate->year || ($bulanMulai->year == $currentDate->year && $bulanMulai->month < $currentDate->month)) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Pembaruan tagihan potongan tidak dapat diproses, karena bulan mulai sudah terlewat."), Response::HTTP_BAD_REQUEST);
        }

        // Cek bulan_mulai adalah bulan saat ini (penggajian pada bulan ini)
        $existingPenggajian = Penggajian::where('data_karyawan_id', $tagihanPotongan->data_karyawan_id)
            ->whereYear('tgl_penggajian', $bulanMulai->year)
            ->whereMonth('tgl_penggajian', $bulanMulai->month)
            ->first();

        if (!$existingPenggajian) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Pembuatan tagihan potongan tidak dapat diproses, karena periode saat ini belum dilakukan penggajian."), Response::HTTP_BAD_REQUEST);
        }

        if ($bulanMulai->month === $currentDate->month && $bulanMulai->year === $currentDate->year) {
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, "Pembaruan tagihan potongan tidak dapat diproses, karena penggajian sudah dilakukan pada '{$existingPenggajian->tgl_penggajian}'."), Response::HTTP_BAD_REQUEST);
        }


        $tagihanPotongan->update($data);

        $bulan_mulai = Carbon::createFromFormat('d-m-Y', $tagihanPotongan->bulan_mulai)->locale('id')->isoFormat('MMMM YYYY');
        return response()->json(new WithoutDataResource(Response::HTTP_OK, "Data tagihan potongan kategori '{$tagihanPotongan->tagihan_kategoris->label}' periode '{$bulan_mulai}' berhasil diperbaharui."), Response::HTTP_OK);
    }

    public function pelunasan($id)
    {
        if (!Gate::allows('edit penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $tagihanPotongan = TagihanPotongan::find($id);
        if (!$tagihanPotongan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Tagihan potongan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $tagihanPotongan->update([
            'status_tagihan_id' => 3,
            'sisa_tenor' => null,
            'sisa_tagihan' => null,
            'is_pelunasan' => Auth::id(),
            'updated_at' => Carbon::now('Asia/Jakarta')
        ]);

        $response_karyawan = $tagihanPotongan->tagihan_karyawans->users->nama;
        $response_bulan_mulai = Carbon::createFromFormat('d-m-Y', $tagihanPotongan->bulan_mulai)->locale('id')->isoFormat('MMMM YYYY');
        $response_bulan_selesai = Carbon::createFromFormat('d-m-Y', $tagihanPotongan->bulan_selesai)->locale('id')->isoFormat('MMMM YYYY');
        return response()->json(new WithoutDataResource(Response::HTTP_OK, "Tagihan potongan '{$tagihanPotongan->tagihan_kategoris->label}' dari karyawan '{$response_karyawan}' berhasil dilunasi untuk preiode '{$response_bulan_mulai}' - '{$response_bulan_selesai}'."), Response::HTTP_OK);
    }

    public function downloadTagihanPotonganTemplate()
    {
        try {
            $filePath = 'templates/template_import_tagihan.xls';

            if (!Storage::exists($filePath)) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'File template tidak ditemukan.'), Response::HTTP_NOT_FOUND);
            }

            return Storage::download($filePath, 'template_import_tagihan.xls');
        } catch (\Throwable $e) {
            Log::error('| Tagihan | - Error saat download template tagihan: ' . $e->getMessage() . ' Line: ' . $e->getLine());
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error.'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function importTagihanPotongan(ImportTagihanPotonganRequest $request)
    {
        if (!Gate::allows('import penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new TagihanPotonganImport, $file['tagihan']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi kesalahan. Pesan: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data tagihan potongan karyawan berhasil di import kedalam tabel.'), Response::HTTP_OK);
    }

    public function exportTagihanPotongan()
    {
        if (!Gate::allows('export penggajianKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataTagihanPotongan = TagihanPotongan::all();
        if (!$dataTagihanPotongan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data tagihan potongan karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        try {
            return Excel::download(new TagihanPotonganExport(), 'tagihan-potongan-karyawan.xls');
        } catch (\Throwable $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_INTERNAL_SERVER_ERROR, 'Maaf sepertinya terjadi error. Pesan: ' . $e->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
