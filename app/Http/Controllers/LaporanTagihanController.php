<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use Illuminate\Http\Request;

class LaporanTagihanController extends Controller
{
    public function index(Request $request)
    {
        $title = "";
        $tagihan = Tagihan::latest();
        if($request->filled('bulan')){
            $tagihan->whereMonth('tanggal_tagihan', $request->bulan);
            $title = "Bulan ".ubahNamaBulan($request->bulan);
        }
        if($request->filled('tahun')){
            $tagihan->whereYear('tanggal_tagihan', $request->tahun);
            $title = $title." Tahun ".$request->tahun;
        }
        if($request->filled('status')){
            $tagihan->where('status', $request->status);
            $title = $title." Status ".$request->status;
        }
        if($request->filled('kelompok')){
            $tagihan->whereHas('ananda', function ($q) use ($request){
                $q->where('kelompok', $request->kelompok);
            });
            $title = $title." Kelompok ".$request->kelompok;
        }
        if($request->filled('tahun_pelajaran')){
            $tagihan->whereHas('ananda', function ($q) use ($request){
                $q->where('tahun_pelajaran', $request->tahun_pelajaran);
            });
            $title = $title." Tahun Pelajaran ".$request->tahun_pelajaran;
        }

        $tagihan = $tagihan->get();
        return view('operator.laporantagihan_index', compact('tagihan', 'title'));
    }

    // if($request->status == 'sudah-dikonfirmasi'){
    //     $tagihan->whereNotNull('tanggal_konfirmasi');
    // }
    // if($request->status == 'belum-dikonfirmasi'){
    //     $tagihan->whereNull('tanggal_konfirmasi');
    // }
}
