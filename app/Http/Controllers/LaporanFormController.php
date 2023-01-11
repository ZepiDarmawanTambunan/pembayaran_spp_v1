<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ananda;

class LaporanFormController extends Controller
{
    public function create(Request $request)
    {
        $data['getTahunPelajaran'] = Ananda::groupBy('tahun_pelajaran')->pluck('tahun_pelajaran')->sortKeys()->toArray();
        if($data['getTahunPelajaran'] != null){
            $data['getTahunPelajaran'] = array_combine($data['getTahunPelajaran'], $data['getTahunPelajaran']);
        }
        return view('operator.laporanform_index', $data);
    }
}
