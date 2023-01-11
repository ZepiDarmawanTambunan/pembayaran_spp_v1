<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ananda;

class BerandaWaliController extends Controller
{
    public function index()
    {
        $ananda = Ananda::with('tagihan')->where('wali_id', auth()->user()->id)->orderBy('nama', 'asc')->get();
        $dataRekap = [];
        foreach ($ananda as $itemAnanda) {
            // setiap ananda, ambil data tagihan selama 1 tahun, kalau sudah dihapus ulang. utk perulangan ananda lain nya
            $dataTagihan = [];
            $tahun = date('Y');
            $bulan = date('m');
            if($bulan < bulanSpp()[0]){
                $tahun = $tahun - 1;
            }
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

                $statusBayarTeks = "baru";
                if($tagihan == null){
                    $statusBayarTeks = "-";
                }else if($tagihan->status != ''){
                    $statusBayarTeks = $tagihan->status;
                    $pembayaran = $tagihan->pembayaran->whereNull('tanggal_konfirmasi');
                    if($pembayaran->count() >= 1){
                        $statusBayarTeks = "belum dikonfirmasi";
                    }
                }

                $dataTagihan[] = [
                    'bulan' => ubahNamaBulan($bulan),
                    'tahun' => $tahun,
                    'tagihan' => $tagihan,
                    'tanggal_lunas' => $tagihan?->tanggal_lunas ?? '-',
                    // 'total_tagihan' => $tagihan?->total_tagihan ?? '-',
                    'status_bayar' => $tagihan?->status == 'baru' ? false : true,
                    'status_bayar_teks' => $statusBayarTeks,
                ];
            }
            $dataRekap[] = [
                'ananda' => $itemAnanda,
                'dataTagihan' => $dataTagihan
            ];
        }

        $data['header'] = bulanSPP();
        $data['dataRekap'] = $dataRekap;

        return view('wali.beranda_index', $data);
    }
}
