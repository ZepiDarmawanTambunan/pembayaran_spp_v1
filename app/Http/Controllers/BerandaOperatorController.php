<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\Ananda;
use App\Models\Tagihan;
use App\Charts\TagihanBulananChart;
use App\Charts\TagihanStatusChart;
use App\Charts\PembayaranStatusChart;

class BerandaOperatorController extends Controller
{
    public function index(
        TagihanBulananChart $tagihanBulananChart,
        TagihanStatusChart $tagihanStatusChart,
        PembayaranStatusChart $pembayaranStatusChart
        )
    {
        $data['tahun'] = date('Y');
        $data['bulan'] = date('m');
        $data['ananda'] = Ananda::get();

        $pembayaran = Pembayaran::whereYear('tanggal_bayar', $data['tahun'])
        ->whereMonth('tanggal_bayar', $data['bulan'])->get();
        $data['totalPembayaran'] = $pembayaran->sum('jumlah_dibayar');
        $data['totalAnandaSudahBayar'] = $pembayaran->count();

        $tagihan = Tagihan::with('ananda')->whereYear('tanggal_tagihan', $data['tahun'])
        ->whereMonth('tanggal_tagihan', $data['bulan'])->get();
        $data['tagihanPerKelompok'] = $tagihan->groupBy('ananda.kelompok')->sortKeys();
        $data['tagihanBelumBayar'] = $tagihan->where('status', '<>', 'lunas');
        $data['tagihanSudahBayar'] = $tagihan->where('status', 'lunas');
        $data['totalTagihan'] = $tagihan->count();

        $data['bulanTeks'] = ubahNamaBulan($data['bulan']);
        $data['totalPembayaranBelumKonfirmasi'] = Pembayaran::whereNull('tanggal_konfirmasi')->get();

        $data['tagihanChart'] = $tagihanBulananChart->build([
            $data['tagihanBelumBayar']->count(),
            $data['tagihanSudahBayar']->count(),
        ]);

        $labelTagihanStatusChart = ['lunas', 'angsur', 'baru'];
        $dataTagihanStatusChart = [
            $tagihan->where('status', 'lunas')->count(),
            $tagihan->where('status', 'angsur')->count(),
            $tagihan->where('status', 'baru')->count(),
        ];
        $data['tagihanStatusChart'] = $tagihanStatusChart->build($labelTagihanStatusChart, $dataTagihanStatusChart);

        $labelPembayaranStatusChart = ['Sudah Dikonfirmasi', 'Belum Dikonfirmasi'];
        $dataPembayaranStatusChart = [
            $tagihan->whereNotNull('tanggal_konfirmasi')->count(),
            $tagihan->whereNull('tanggal_konfirmasi')->count(),
        ];
        $data['pembayaranStatusChart'] = $pembayaranStatusChart->build($labelPembayaranStatusChart, $dataPembayaranStatusChart);

        return view('operator.beranda_index', $data);
    }
}
