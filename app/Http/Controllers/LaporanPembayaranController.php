<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Illuminate\Http\Request;

class LaporanPembayaranController extends Controller
{
    public function index(Request $request)
    {
        $title = "";
        $pembayaran = Pembayaran::latest();
        if($request->filled('bulan')){
            $pembayaran->whereMonth('tanggal_bayar', $request->bulan);
            $title = "Bulan ".ubahNamaBulan($request->bulan);
        }
        if($request->filled('tahun')){
            $pembayaran->whereYear('tanggal_bayar', $request->tahun);
            $title = $title." Tahun ".$request->tahun;
        }
        if($request->filled('status')){
            if($request->status == 'sudah-dikonfirmasi'){
                $pembayaran->whereNotNull('tanggal_konfirmasi');
            }
            if($request->status == 'belum-dikonfirmasi'){
                $pembayaran->whereNull('tanggal_konfirmasi');
            }
            $title = $title." Status ".$request->status;
        }
        if($request->filled('kelompok')){
            $pembayaran->whereHas('tagihan', function ($t) use ($request){
                $t->whereHas('ananda', function($s) use ($request){
                    $s->where('kelompok', $request->kelompok);
                });
            });
            $title = $title." Kelompok ".$request->kelompok;
        }
        if($request->filled('tahun_pelajaran')){
            $pembayaran->whereHas('tagihan', function ($t) use ($request){
                $t->whereHas('ananda', function($s) use ($request){
                    $s->where('tahun_pelajaran', $request->tahun_pelajaran);
                });
            });
            $title = $title." Tahun Pelajaran ".$request->tahun_pelajaran;
        }
        $pembayaran = $pembayaran->get();
        $totalPembayaran = $pembayaran->sum('jumlah_dibayar') ?? 0;
        return view('operator.laporanpembayaran_index', compact('pembayaran', 'title', 'totalPembayaran'));
    }
}
