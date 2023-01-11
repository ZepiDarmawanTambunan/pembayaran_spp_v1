<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ananda;
use App\Models\Tagihan;

class LaporanRekapPembayaran extends Controller
{
    public function index(Request $request)
    {
        $ananda = Ananda::with('tagihan')->orderBy('nama', 'asc');
        if($request->filled('kelompok')){
            $ananda->where('kelompok', $request->kelompok);
        }
        if($request->filled('tahun_pelajaran')){
            $ananda->where('tahun_pelajaran', $request->tahun_pelajaran);
        }
        $ananda = $ananda->get();
        foreach ($ananda as $itemAnanda) {
            // setiap ananda, ambil data tagihan selama 1 tahun, kalau sudah dihapus ulang. utk perulangan ananda lain nya
            $dataTagihan = [];
            $tahun = $request->tahun;
            foreach (bulanSPP() as $bulan) {
                // jika bulan 1 maka tahun ditambah 1
                if($bulan == 1){
                    $tahun = $tahun+1;
                }

                // mencari tagihan berdasarkan ananda, bulan dan tahun
                $tagihan = $itemAnanda->tagihan->filter(function($value) use($bulan, $tahun){
                    return $value->tanggal_tagihan->year == $tahun
                    && $value->tanggal_tagihan->month == $bulan;
                })->first();

                $dataTagihan[] = [
                    'bulan' => ubahNamaBulan($bulan),
                    'tahun' => $tahun,
                    'tanggal_lunas' => $tagihan->tanggal_lunas ?? '-',
                    'total_tagihan' => $tagihan->total_tagihan ?? '-',
                ];
            }
            $dataRekap[] = [
                'ananda' => $itemAnanda,
                'dataTagihan' => $dataTagihan
            ];
        }

        $data['header'] = bulanSPP();
        $data['dataRekap'] = $dataRekap;
        $data['title'] = 'Rekap Pembayaran SPP';
        return view('operator.laporanrekappembayaran_index', $data);
    }
}
