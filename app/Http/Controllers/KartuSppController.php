<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tagihan;
use App\Models\Ananda;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class KartuSppController extends Controller
{
    public function index(Request $request)
    {
        $ananda = Ananda::with('tagihan')->findOrFail($request->ananda_id);
        if(Auth::user()->akses == 'wali'){
            $ananda = Ananda::where('wali_id', Auth::user()->id)
            ->where('id', $request->ananda_id)
            ->firstOrFail();
        }

        $tahun = $request->tahun;
        if($request->bulan < bulanSpp()[0]){
            $tahun = $tahun - 1;
        }
        $arrayData = [];
        foreach (bulanSPP() as $bulan) {
            // jika bulan 1 maka tahun ditambah 1
            if($bulan == 1){
                $tahun = $tahun+1;
            }

            // mencari tagihan berdasarkan ananda, bulan dan tahun
            $tagihan = $ananda->tagihan->filter(function($value) use($bulan, $tahun){
                return $value->tanggal_tagihan->year == $tahun &&
                $value->tanggal_tagihan->month == $bulan;
            })->first();

            $tanggalBayar = '';
            // jika tagihan tdk kosong dan status != baru maka sudah bayar, kita ambil tgl bayarny
            if($tagihan != null && $tagihan->status != 'baru'){
                $tanggalBayar = $tagihan->pembayaran->first()->tanggal_bayar->format('d/m/y');
            }

            $arrayData[] = [
                'bulan' => ubahNamaBulan($bulan),
                'tahun' => $tahun,
                'total_tagihan' => $tagihan->total_tagihan ?? 0,
                'status_tagihan' => ($tagihan == null) ? false : true,
                'status_pembayaran' => ($tagihan == null) ? 'Belum Bayar' : $tagihan->status,
                'tanggal_bayar' => $tanggalBayar,
            ];
        }

        $data['kartuSpp'] = collect($arrayData);
        $data['ananda'] = $ananda;

        if(request('output') == 'pdf'){
            $pdf = Pdf::loadView('kartuspp_index', $data);
            $namaFile = 'kartu spp '.$ananda->nama.' tahun '.$request->tahun.'.pdf';
            return $pdf->download($namaFile);
        }
        return view('kartuspp_index', $data);
    }
}
