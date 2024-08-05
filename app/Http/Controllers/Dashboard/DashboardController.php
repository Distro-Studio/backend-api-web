<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\DataKaryawan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function calculatedHeader()
    {

    }

    public function calculatedKelamin()
    {
        $dataKelamin = DataKaryawan::where('jenis_kelamin', [1, 2])->get();
    }
    
    public function calculatedJabatan()
    {

    }
    
    public function calculatedKepegawaian()
    {

    }

    public function indexLibur()
    {

    }

    // pengumuman
    public function index()
    {

    }

    public function store()
    {

    }

    public function show($id)
    {

    }
}
