<?php

namespace App\Http\Controllers\Dashboard\Karyawan;

use Carbon\Carbon;
use App\Models\Ptkp;
use App\Models\User;
use App\Models\Premi;
use App\Models\Berkas;
use App\Models\Jabatan;
use App\Models\Presensi;
use App\Models\UnitKerja;
use App\Models\Kompetensi;
use Illuminate\Support\Str;
use App\Models\DataKaryawan;
use App\Models\KelompokGaji;
use Illuminate\Http\Request;
use App\Helpers\RandomHelper;
use Illuminate\Http\Response;
use App\Models\StatusKaryawan;
use App\Mail\SendAccoundUsersMail;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Helpers\StorageServerHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Mail\SendAccountResetPassword;
use App\Exports\Karyawan\KaryawanExport;
use App\Imports\Karyawan\KaryawanImport;
use App\Http\Requests\StoreDataKaryawanRequest;
use App\Jobs\EmailNotification\AccountEmailJob;
use App\Http\Requests\UpdateDataKaryawanRequest;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Http\Requests\Excel_Import\ImportKaryawanRequest;
use App\Http\Resources\Dashboard\Karyawan\KaryawanResource;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;

class DataKaryawanController extends Controller
{
    public function getAllDataUser()
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $user = User::where('nama', '!=', 'Super Admin')->get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all user for dropdown',
            'data' => $user
        ], Response::HTTP_OK);
    }

    public function getAllDataUnitKerja()
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $unit_kerja = UnitKerja::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all unit kerja for dropdown',
            'data' => $unit_kerja
        ], Response::HTTP_OK);
    }

    public function getAllDataJabatan()
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $jabatan = Jabatan::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all jabatan for dropdown',
            'data' => $jabatan
        ], Response::HTTP_OK);
    }

    public function getAllDataStatusKaryawan()
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $status_karyawan = StatusKaryawan::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all status karyawan for dropdown',
            'data' => $status_karyawan
        ], Response::HTTP_OK);
    }

    public function getAllDataKompetensi()
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kompetensi = Kompetensi::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all kompetensi for dropdown',
            'data' => $kompetensi
        ], Response::HTTP_OK);
    }

    public function getAllDataRole()
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $role = Role::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all role for dropdown',
            'data' => $role
        ], Response::HTTP_OK);
    }

    public function getAllDataKelompokGaji()
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $kelompok_gaji = KelompokGaji::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all kelompok gaji for dropdown',
            'data' => $kelompok_gaji
        ], Response::HTTP_OK);
    }

    public function getAllDataPTKP()
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $ptkp = Ptkp::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all ptkp for dropdown',
            'data' => $ptkp
        ], Response::HTTP_OK);
    }

    public function getAllDataPremi()
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $premi = Premi::all();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all premi for dropdown',
            'data' => $premi
        ], Response::HTTP_OK);
    }

    public function getAllDataKaryawan()
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $dataKaryawan = DataKaryawan::where('email', '!=', 'super_admin@admin.rski')->get();
        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Retrieving all karyawan for dropdown',
            'data' => $dataKaryawan
        ], Response::HTTP_OK);
    }

    public function getDataPresensi($data_karyawan_id)
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Mendapatkan tanggal hari ini
        $today = Carbon::today()->format('Y-m-d');

        // Mendapatkan data presensi karyawan berdasarkan data_karyawan_id dan filter hari ini
        $presensi = Presensi::with([
            'users',
            'jadwals.shifts',
            'data_karyawans.unit_kerjas',
            'kategori_presensis'
        ])
            ->where('data_karyawan_id', $data_karyawan_id)
            ->whereDate('jam_masuk', $today)
            ->first();

        if (!$presensi) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data presensi tidak ditemukan untuk hari ini.'), Response::HTTP_NOT_FOUND);
        }

        $fotoMasukBerkas = Berkas::where('id', $presensi->foto_masuk)->first();
        $fotoKeluarBerkas = Berkas::where('id', $presensi->foto_keluar)->first();

        $baseUrl = env('STORAGE_SERVER_DOMAIN'); // Ganti dengan URL domain Anda

        $fotoMasukExt = $fotoMasukBerkas ? StorageServerHelper::getExtensionFromMimeType($fotoMasukBerkas->ext) : null;
        $fotoMasukUrl = $fotoMasukBerkas ? $baseUrl . $fotoMasukBerkas->path . '.' . $fotoMasukExt : null;

        $fotoKeluarExt = $fotoKeluarBerkas ? StorageServerHelper::getExtensionFromMimeType($fotoKeluarBerkas->ext) : null;
        $fotoKeluarUrl = $fotoKeluarBerkas ? $baseUrl . $fotoKeluarBerkas->path . '.' . $fotoKeluarExt : null;

        $formattedData = [
            'id' => $presensi->id,
            'user' => $presensi->users,
            'unit_kerja' => $presensi->data_karyawans->unit_kerjas,
            'jadwal' => $presensi->jadwals,
            'jam_masuk' => $presensi->jam_masuk,
            'jam_keluar' => $presensi->jam_keluar,
            'durasi' => $presensi->durasi,
            'lat_masuk' => $presensi->lat,
            'long_masuk' => $presensi->long,
            'lat_keluar' => $presensi->latkeluar,
            'long_keluar' => $presensi->longkeluar,
            'foto_masuk' => [
                'id' => $fotoMasukBerkas->id,
                'user_id' => $fotoMasukBerkas->user_id,
                'file_id' => $fotoMasukBerkas->file_id,
                'nama' => $fotoMasukBerkas->nama,
                'nama_file' => $fotoMasukBerkas->nama_file,
                'path' => $fotoMasukUrl,
                'ext' => $fotoMasukBerkas->ext,
                'size' => $fotoMasukBerkas->size,
            ],
            'foto_keluar' => [
                'id' => $fotoKeluarBerkas->id,
                'user_id' => $fotoKeluarBerkas->user_id,
                'file_id' => $fotoKeluarBerkas->file_id,
                'nama' => $fotoKeluarBerkas->nama,
                'nama_file' => $fotoKeluarBerkas->nama_file,
                'path' => $fotoKeluarUrl,
                'ext' => $fotoKeluarBerkas->ext,
                'size' => $fotoKeluarBerkas->size,
            ],
            'kategori_presensi' => $presensi->kategori_presensis,
            'created_at' => $presensi->created_at,
            'updated_at' => $presensi->updated_at
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail data presensi karyawan berhasil ditampilkan.",
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }


    public function index(Request $request)
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Per page
        $limit = $request->input('limit', 10); // Default per page is 10

        $karyawan = DataKaryawan::query()->where('email', '!=', 'super_admin@admin.rski');

        // Filter
        if ($request->has('nama_unit')) {
            $namaUnitKerja = $request->nama_unit;

            $karyawan->whereHas('unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('nama_unit', $namaUnitKerja);
                } else {
                    $query->where('nama_unit', '=', $namaUnitKerja);
                }
            });
        }

        if ($request->has('status_karyawan')) {
            if (is_array($request->status_karyawan)) {
                $karyawan->whereHas('status_karyawans', function ($query) use ($request) {
                    $query->whereIn('label', $request->status_karyawan);
                });
            } else {
                $karyawan->whereHas('status_karyawans', function ($query) use ($request) {
                    $query->where('label', $request->status_karyawan);
                });
            }
        }

        if ($request->has('masa_kerja')) {
            $masa_kerja = $request->masa_kerja;
            $karyawan->whereRaw('TIMESTAMPDIFF(YEAR, tgl_masuk, COALESCE(tgl_keluar, NOW())) = ?', [$masa_kerja]);
        }

        if ($request->has('status_aktif')) {
            $statusAktif = $request->status_aktif;
            $karyawan->whereHas('users', function ($query) use ($statusAktif) {
                $query->where('status_aktif', $statusAktif);
            });
        }

        if ($request->has('tgl_masuk')) {
            $tglMasuk = $request->tgl_masuk;
            $tglMasuk = Carbon::parse($tglMasuk)->format('Y-m-d');
            $karyawan->where('tgl_masuk', $tglMasuk);
        }

        // Search
        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';

            $karyawan->where(function ($query) use ($searchTerm) {
                $query->whereHas('users', function ($query) use ($searchTerm) {
                    $query->where('nama', 'like', $searchTerm);
                })
                    ->orWhereHas('unit_kerjas', function ($query) use ($searchTerm) {
                        $query->where('nama_unit', 'like', $searchTerm);
                    })
                    ->orWhere('nik', 'like', $searchTerm)
                    ->orWhere('no_rm', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm)
                    ->orWhereHas('data_keluargas', function ($query) use ($searchTerm) {
                        $query->where('nama_keluarga', 'like', $searchTerm)
                            ->whereIn('hubungan', ['Ayah', 'Ibu']);
                    });
            });
        }

        // Pastikan limit adalah integer
        $limit = is_numeric($limit) ? (int)$limit : 10;

        $dataKaryawan = $karyawan->paginate($limit);
        if ($dataKaryawan->isEmpty()) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Format data untuk output
        $formattedData = $dataKaryawan->items();
        $formattedData = array_map(function ($karyawan) {
            $dataKeluargas = $karyawan->data_keluargas;
            $ayah = $dataKeluargas->where('hubungan', 'Ayah')->first();
            $ibu = $dataKeluargas->where('hubungan', 'Ibu')->first();
            $jumlahKeluarga = $dataKeluargas->count();

            return [
                'id' => $karyawan->id,
                'user' => $karyawan->users,
                'role' => $karyawan->users->roles, // role_id
                'email' => $karyawan->email,
                'no_rm' => $karyawan->no_rm,
                'no_manulife' => $karyawan->no_manulife,
                'tgl_masuk' => $karyawan->tgl_masuk,
                'unit_kerja' => $karyawan->unit_kerjas, // unit_kerja_id
                'jabatan' => $karyawan->jabatans, // jabatan_id
                'kompetensi' => $karyawan->kompetensis, // kompetensi_id
                'nik_ktp' => $karyawan->nik_ktp,
                'status_karyawan' => $karyawan->status_karyawans, // status_karyawan_id
                'tempat_lahir' => $karyawan->tempat_lahir,
                'tgl_lahir' => $karyawan->tgl_lahir,
                'kelompok_gaji' => $karyawan->kelompok_gajis, // kelompok_gaji_id
                'no_rekening' => $karyawan->no_rekening,
                'tunjangan_jabatan' => $karyawan->tunjangan_jabatan,
                'tunjangan_fungsional' => $karyawan->tunjangan_fungsional,
                'tunjangan_khusus' => $karyawan->tunjangan_khusus,
                'tunjangan_lainnya' => $karyawan->tunjangan_lainnya,
                'uang_lembur' => $karyawan->uang_lembur,
                'uang_makan' => $karyawan->uang_makan,
                'ptkp' => $karyawan->ptkps, // ptkp_id
                'tgl_keluar' => $karyawan->tgl_keluar,
                'no_kk' => $karyawan->no_kk,
                'alamat' => $karyawan->alamat,
                'gelar_depan' => $karyawan->gelar_depan,
                'no_hp' => $karyawan->no_hp,
                'no_bpjsksh' => $karyawan->no_bpjsksh,
                'no_bpjsktk' => $karyawan->no_bpjsktk,
                'tgl_diangkat' => $karyawan->tgl_diangkat,
                'masa_kerja' => $karyawan->masa_kerja,
                'npwp' => $karyawan->npwp,
                'jenis_kelamin' => $karyawan->jenis_kelamin,
                'agama' => $karyawan->kategori_agamas, // agama_id
                'golongan_darah' => $karyawan->kategori_darahs, // golongan_darah_id
                'tinggi_badan' => $karyawan->tinggi_badan,
                'berat_badan' => $karyawan->berat_badan,
                'no_ijazah' => $karyawan->no_ijazah,
                'tahun_lulus' => $karyawan->tahun_lulus,
                'no_str' => $karyawan->no_str,
                'masa_berlaku_str' => $karyawan->masa_berlaku_str,
                'tgl_berakhir_pks' => $karyawan->tgl_berakhir_pks,
                'masa_diklat' => $karyawan->masa_diklat,
                'jumlah_keluarga' => $jumlahKeluarga,
                'ibu' => $ibu ? $ibu->nama_keluarga : null,
                'ayah' => $ayah ? $ayah->nama_keluarga : null,
                'created_at' => $karyawan->created_at,
                'updated_at' => $karyawan->updated_at
            ];
        }, $formattedData);

        $paginationData = [
            'links' => [
                'first' => $dataKaryawan->url(1),
                'last' => $dataKaryawan->url($dataKaryawan->lastPage()),
                'prev' => $dataKaryawan->previousPageUrl(),
                'next' => $dataKaryawan->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $dataKaryawan->currentPage(),
                'last_page' => $dataKaryawan->lastPage(),
                'per_page' => $dataKaryawan->perPage(),
                'total' => $dataKaryawan->total(),
            ]
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Data karyawan berhasil ditampilkan.',
            'data' => $formattedData,
            'pagination' => $paginationData
        ], Response::HTTP_OK);
    }

    public function store(StoreDataKaryawanRequest $request)
    {
        if (!Gate::allows('create dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $requestedRoleId = $request->input('role_id');
        $premis = $request->input('premi_id', []); // Mengambil daftar premi yang dipilih

        // Generate password secara otomatis
        $generatedPassword = RandomHelper::generatePassword();

        DB::beginTransaction();
        try {
            $userData = [
                'nama' => $data['nama'],
                'role_id' => $data['role_id'],
                // 'username' => $data['username'],
                'password' => Hash::make($generatedPassword),
            ];

            $createUser = User::create($userData);
            $createUser->roles()->attach($requestedRoleId);

            $createDataKaryawan = new DataKaryawan([
                'user_id' => $createUser->id,
                'email' => $data['email'],
                'no_rm' => $data['no_rm'],
                'no_manulife' => $data['no_manulife'],
                'tgl_masuk' => $data['tgl_masuk'],
                'unit_kerja_id' => $data['unit_kerja_id'],
                'jabatan_id' => $data['jabatan_id'],
                'kompetensi_id' => $data['kompetensi_id'],
                'status_karyawan_id' => $data['status_karyawan_id'],
                'kelompok_gaji_id' => $data['kelompok_gaji_id'],
                'no_rekening' => $data['no_rekening'],
                'tunjangan_jabatan' => $data['tunjangan_jabatan'],
                'tunjangan_fungsional' => $data['tunjangan_fungsional'],
                'tunjangan_khusus' => $data['tunjangan_khusus'],
                'tunjangan_lainnya' => $data['tunjangan_lainnya'],
                'uang_makan' => $data['uang_makan'],
                'uang_lembur' => $data['uang_lembur'],
                'ptkp_id' => $data['ptkp_id'],
            ]);
            $createDataKaryawan->save();

            // Masukkan data ke tabel pengurang_gajis jika ada premi yang dipilih
            if (!empty($premis)) {
                $premisData = DB::table('premis')->whereIn('id', $premis)->get();
                if ($premisData->isEmpty()) {
                    return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Potongan yang dipilih tidak valid.'), Response::HTTP_NOT_FOUND);
                }

                foreach ($premisData as $premi) {
                    DB::table('pengurang_gajis')->insert([
                        'data_karyawan_id' => $createDataKaryawan->id,
                        'premi_id' => $premi->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }

            DB::commit();

            // AccountEmailJob::dispatch($data['email'], $generatedPassword, $data['nama']);

            // Mail::to($data['email'])->send(new SendAccoundUsersMail($data['email'], $generatedPassword, $data['nama']));
            return response()->json(new KaryawanResource(Response::HTTP_OK, 'Data karyawan berhasil dibuat.', $createDataKaryawan), Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(new WithoutDataResource(Response::HTTP_BAD_REQUEST, 'Maaf sepertinya pembuatan data karyawan bermasalah, Error: ' . $th->getMessage()), Response::HTTP_BAD_REQUEST);
        }
    }

    // show by user id
    public function showByUserId($user_id)
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Cari user berdasarkan user_id
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Akun karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        // Ambil data_karyawan_id dari user
        $data_karyawan_id = $user->data_karyawan_id;

        // Cari data karyawan berdasarkan data_karyawan_id
        $karyawan = DataKaryawan::where('email', '!=', 'super_admin@admin.rski')->find($data_karyawan_id);

        if (!$karyawan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = [
            'id' => $karyawan->id,
            'user' => $karyawan->users,
            'role' => $karyawan->users->roles, // role_id
            'email' => $karyawan->email,
            'no_rm' => $karyawan->no_rm,
            'no_manulife' => $karyawan->no_manulife,
            'tgl_masuk' => $karyawan->tgl_masuk,
            'unit_kerja' => $karyawan->unit_kerjas, // unit_kerja_id
            'jabatan' => $karyawan->jabatans, // jabatan_id
            'kompetensi' => $karyawan->kompetensis, // kompetensi_id
            'nik_ktp' => $karyawan->nik_ktp,
            'status_karyawan' => $karyawan->status_karyawans, // status_karyawan_id
            'tempat_lahir' => $karyawan->tempat_lahir,
            'tgl_lahir' => $karyawan->tgl_lahir,
            'kelompok_gaji' => $karyawan->kelompok_gajis, // kelompok_gaji_id
            'no_rekening' => $karyawan->no_rekening,
            'tunjangan_jabatan' => $karyawan->tunjangan_jabatan,
            'tunjangan_fungsional' => $karyawan->tunjangan_fungsional,
            'tunjangan_khusus' => $karyawan->tunjangan_khusus,
            'tunjangan_lainnya' => $karyawan->tunjangan_lainnya,
            'uang_lembur' => $karyawan->uang_lembur,
            'uang_makan' => $karyawan->uang_makan,
            'ptkp' => $karyawan->ptkps, // ptkp_id
            'tgl_keluar' => $karyawan->tgl_keluar,
            'no_kk' => $karyawan->no_kk,
            'alamat' => $karyawan->alamat,
            'gelar_depan' => $karyawan->gelar_depan,
            'no_hp' => $karyawan->no_hp,
            'no_bpjsksh' => $karyawan->no_bpjsksh,
            'no_bpjsktk' => $karyawan->no_bpjsktk,
            'tgl_diangkat' => $karyawan->tgl_diangkat,
            'masa_kerja' => $karyawan->masa_kerja,
            'npwp' => $karyawan->npwp,
            'jenis_kelamin' => $karyawan->jenis_kelamin,
            'agama' => $karyawan->kategori_agamas, // agama_id
            'golongan_darah' => $karyawan->kategori_darahs, // golongan_darah_id
            'tinggi_badan' => $karyawan->tinggi_badan,
            'berat_badan' => $karyawan->berat_badan,
            'no_ijazah' => $karyawan->no_ijazah,
            'tahun_lulus' => $karyawan->tahun_lulus,
            'no_str' => $karyawan->no_str,
            'masa_berlaku_str' => $karyawan->masa_berlaku_str,
            'tgl_berakhir_pks' => $karyawan->tgl_berakhir_pks,
            'masa_diklat' => $karyawan->masa_diklat,
            'created_at' => $karyawan->created_at,
            'updated_at' => $karyawan->updated_at
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail karyawan {$karyawan->users->nama} berhasil ditampilkan.",
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    // show by data karyawan
    public function showByDataKaryawanId($data_karyawan_id)
    {
        if (!Gate::allows('view dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        // Cari data karyawan berdasarkan data_karyawan_id
        $karyawan = DataKaryawan::where('email', '!=', 'super_admin@admin.rski')->find($data_karyawan_id);

        if (!$karyawan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $formattedData = [
            'id' => $karyawan->id,
            'user' => $karyawan->users,
            'role' => $karyawan->users->roles, // role_id
            'email' => $karyawan->email,
            'no_rm' => $karyawan->no_rm,
            'no_manulife' => $karyawan->no_manulife,
            'tgl_masuk' => $karyawan->tgl_masuk,
            'unit_kerja' => $karyawan->unit_kerjas, // unit_kerja_id
            'jabatan' => $karyawan->jabatans, // jabatan_id
            'kompetensi' => $karyawan->kompetensis, // kompetensi_id
            'nik_ktp' => $karyawan->nik_ktp,
            'status_karyawan' => $karyawan->status_karyawans, // status_karyawan_id
            'tempat_lahir' => $karyawan->tempat_lahir,
            'tgl_lahir' => $karyawan->tgl_lahir,
            'kelompok_gaji' => $karyawan->kelompok_gajis, // kelompok_gaji_id
            'no_rekening' => $karyawan->no_rekening,
            'tunjangan_jabatan' => $karyawan->tunjangan_jabatan,
            'tunjangan_fungsional' => $karyawan->tunjangan_fungsional,
            'tunjangan_khusus' => $karyawan->tunjangan_khusus,
            'tunjangan_lainnya' => $karyawan->tunjangan_lainnya,
            'uang_lembur' => $karyawan->uang_lembur,
            'uang_makan' => $karyawan->uang_makan,
            'ptkp' => $karyawan->ptkps, // ptkp_id
            'tgl_keluar' => $karyawan->tgl_keluar,
            'no_kk' => $karyawan->no_kk,
            'alamat' => $karyawan->alamat,
            'gelar_depan' => $karyawan->gelar_depan,
            'no_hp' => $karyawan->no_hp,
            'no_bpjsksh' => $karyawan->no_bpjsksh,
            'no_bpjsktk' => $karyawan->no_bpjsktk,
            'tgl_diangkat' => $karyawan->tgl_diangkat,
            'masa_kerja' => $karyawan->masa_kerja,
            'npwp' => $karyawan->npwp,
            'jenis_kelamin' => $karyawan->jenis_kelamin,
            'agama' => $karyawan->kategori_agamas, // agama_id
            'golongan_darah' => $karyawan->kategori_darahs, // golongan_darah_id
            'tinggi_badan' => $karyawan->tinggi_badan,
            'berat_badan' => $karyawan->berat_badan,
            'no_ijazah' => $karyawan->no_ijazah,
            'tahun_lulus' => $karyawan->tahun_lulus,
            'no_str' => $karyawan->no_str,
            'masa_berlaku_str' => $karyawan->masa_berlaku_str,
            'tgl_berakhir_pks' => $karyawan->tgl_berakhir_pks,
            'masa_diklat' => $karyawan->masa_diklat,
            'created_at' => $karyawan->created_at,
            'updated_at' => $karyawan->updated_at
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Detail karyawan {$karyawan->users->nama} berhasil ditampilkan.",
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function update(UpdateDataKaryawanRequest $request, $id)
    {
        if (!Gate::allows('edit dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();
        $karyawan = DataKaryawan::find($id);

        if (!$karyawan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $user = $karyawan->users;
        $oldEmail = $karyawan->email;
        $newEmail = $data['email'];

        // Memeriksa apakah email telah berubah
        if ($oldEmail !== $newEmail) {
            // Memeriksa status_aktif
            if ($user->status_aktif !== User::STATUS_BELUM_AKTIF) {
                return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Email tidak dapat diubah, Status akun harus dalam keadaan belum aktif.'), Response::HTTP_NOT_ACCEPTABLE);
            }

            // Update email di data karyawan
            $karyawan->email = $newEmail;
            Mail::to($newEmail)->send(new SendAccountResetPassword($newEmail, $data['nama']));
        }

        $karyawan->update($data);

        $formattedData = [
            'id' => $karyawan->id,
            'user' => $karyawan->users,
            'role' => $karyawan->users->roles, // role_id
            'email' => $karyawan->email,
            'no_rm' => $karyawan->no_rm,
            'no_manulife' => $karyawan->no_manulife,
            'tgl_masuk' => $karyawan->tgl_masuk,
            'unit_kerja' => $karyawan->unit_kerjas, // unit_kerja_id
            'jabatan' => $karyawan->jabatans, // jabatan_id
            'kompetensi' => $karyawan->kompetensis, // kompetensi_id
            'nik_ktp' => $karyawan->nik_ktp,
            'status_karyawan' => $karyawan->status_karyawans, // status_karyawan_id
            'tempat_lahir' => $karyawan->tempat_lahir,
            'tgl_lahir' => $karyawan->tgl_lahir,
            'kelompok_gaji' => $karyawan->kelompok_gajis, // kelompok_gaji_id
            'no_rekening' => $karyawan->no_rekening,
            'tunjangan_jabatan' => $karyawan->tunjangan_jabatan,
            'tunjangan_fungsional' => $karyawan->tunjangan_fungsional,
            'tunjangan_khusus' => $karyawan->tunjangan_khusus,
            'tunjangan_lainnya' => $karyawan->tunjangan_lainnya,
            'uang_lembur' => $karyawan->uang_lembur,
            'uang_makan' => $karyawan->uang_makan,
            'ptkp' => $karyawan->ptkps, // ptkp_id
            'tgl_keluar' => $karyawan->tgl_keluar,
            'no_kk' => $karyawan->no_kk,
            'alamat' => $karyawan->alamat,
            'gelar_depan' => $karyawan->gelar_depan,
            'no_hp' => $karyawan->no_hp,
            'no_bpjsksh' => $karyawan->no_bpjsksh,
            'no_bpjsktk' => $karyawan->no_bpjsktk,
            'tgl_diangkat' => $karyawan->tgl_diangkat,
            'masa_kerja' => $karyawan->masa_kerja,
            'npwp' => $karyawan->npwp,
            'jenis_kelamin' => $karyawan->jenis_kelamin,
            'agama' => $karyawan->kategori_agamas, // agama_id
            'golongan_darah' => $karyawan->kategori_darahs, // golongan_darah_id
            'tinggi_badan' => $karyawan->tinggi_badan,
            'berat_badan' => $karyawan->berat_badan,
            'no_ijazah' => $karyawan->no_ijazah,
            'tahun_lulus' => $karyawan->tahun_lulus,
            'no_str' => $karyawan->no_str,
            'masa_berlaku_str' => $karyawan->masa_berlaku_str,
            'tgl_berakhir_pks' => $karyawan->tgl_berakhir_pks,
            'masa_diklat' => $karyawan->masa_diklat,
            'created_at' => $karyawan->created_at,
            'updated_at' => $karyawan->updated_at
        ];

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => "Data karyawan {$data['nama']} berhasil diperbarui.",
            'data' => $formattedData,
        ], Response::HTTP_OK);
    }

    public function exportKaryawan()
    {
        if (!Gate::allows('export dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        try {
            return Excel::download(new KaryawanExport(), 'karyawan-data.xls');
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Error $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi error. Message: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data karyawan berhasil di download.'), Response::HTTP_OK);
    }

    public function importKaryawan(ImportKaryawanRequest $request)
    {
        if (!Gate::allows('import dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $file = $request->validated();

        try {
            Excel::import(new KaryawanImport, $file['karyawan_file']);
        } catch (\Exception $e) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_ACCEPTABLE, 'Maaf sepertinya terjadi kesalahan. Pesan: ' . $e->getMessage()), Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json(new WithoutDataResource(Response::HTTP_OK, 'Data karyawan berhasil di import kedalam table.'), Response::HTTP_OK);
    }

    public function deactivateKaryawan($id)
    {
        if (!Gate::allows('edit dataKaryawan')) {
            return response()->json(new WithoutDataResource(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses untuk melakukan proses ini.'), Response::HTTP_FORBIDDEN);
        }

        $karyawan = DataKaryawan::where('email', '!=', 'super_admin@admin.rski')->find($id);

        if (!$karyawan) {
            return response()->json(new WithoutDataResource(Response::HTTP_NOT_FOUND, 'Data karyawan tidak ditemukan.'), Response::HTTP_NOT_FOUND);
        }

        $user = $karyawan->users;
        $user->status_aktif = User::STATUS_DINONAKTIFKAN;
        $user->save();

        return response()->json(new WithoutDataResource(Response::HTTP_OK, "Karyawan {$karyawan->users->nama} berhasil dinonaktifkan."), Response::HTTP_OK);
    }
}