<?php

namespace App\Http\Controllers;

use App\Models\Tagihan as Model;
use App\Models\Ananda;
use App\Models\Wali;
use App\Models\Biaya;
use App\Models\TagihanDetail;
use App\Models\Pembayaran;
use App\Http\Requests\StoreTagihanRequest;
use App\Http\Requests\UpdateTagihanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TagihanNotification;
use Carbon\Carbon;
use DB;

class TagihanController extends Controller
{
    private $viewIndex = 'tagihan_index';
    private $viewCreate = 'tagihan_form';
    private $viewEdit = 'tagihan_form';
    private $viewShow = 'tagihan_show';
    private $routePrefix = 'tagihan';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $models = Model::latest();
        if($request->filled('bulan')){
            $models->whereMonth('tanggal_tagihan', $request->bulan);
        }
        if($request->filled('tahun')){
            $models->whereYear('tanggal_tagihan', $request->tahun);
        }
        if($request->filled('status')){
            $models->where('status', $request->status);
        }
        if($request->filled('q')){
            $models->search($request->q, null, true);
        }

        return view('operator.'.$this->viewIndex, [
            'models' => $models->paginate(settings()->get('app_pagination', '20')),
            'routePrefix' => $this->routePrefix,
            'title' => 'DATA TAGIHAN'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $ananda = Ananda::all();
        $data = [
            'model' => new Model(),
            'method' => 'POST',
            'route' => $this->routePrefix.'.store',
            'button' => 'SIMPAN',
            'title' => 'FORM DATA TAGIHAN',
            // 'angkatan' => $ananda->pluck('angkatan', 'angkatan'),
            // 'kelas' => $ananda->pluck('kelas', 'kelas'),
            // 'biaya' => Biaya::get(),
        ];

        return view('operator.'.$this->viewCreate, $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTagihanRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTagihanRequest $request)
    {
        $requestData = $request->validated();
        DB::beginTransaction();
        $ananda = Ananda::currentStatus('aktif')->get();
        foreach ($ananda as $itemAnanda) {
            $requestData['ananda_id'] = $itemAnanda->id;
            $requestData['status'] = 'baru';
            $tanggalTagihan = Carbon::parse($requestData['tanggal_tagihan']);
            $bulanTagihan = $tanggalTagihan->format('m');
            $tahunTagihan = $tanggalTagihan->format('y');
            $cekTagihan = $itemAnanda->tagihan->filter(function($value) use ($bulanTagihan, $tahunTagihan){
                return $value->tanggal_tagihan->year == $tahunTagihan && $value->tanggal_tagihan->month == $bulanTagihan;
            })->first();
            if($cekTagihan == null){
                $tagihan = Model::create($requestData);
                if($tagihan->ananda->wali != null){
                    Notification::send($tagihan->ananda->wali, new TagihanNotification($tagihan));
                }
                $biaya = $itemAnanda->biaya->children;
                foreach ($biaya as $itemBiaya) {
                    $detail = TagihanDetail::create([
                        'tagihan_id' => $tagihan->id,
                        'nama_biaya' => $itemBiaya->nama,
                        'jumlah_biaya' => $itemBiaya->jumlah,
                    ]);
                }
            }
        }
        DB::commit();
        flash('Data berhasil ditambahkan');
        return redirect()->route('tagihan.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Model  $model
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $tahun = $request->tahun;
        if($request->bulan < bulanSpp()[0]){
            $tahun = $tahun - 1;
        }
        $arrayData = [];
        foreach (bulanSPP() as $bulan) {
            if($bulan == 1){
                $tahun = $tahun+1;
            }

            $tagihan = Model::where('ananda_id', $request->ananda_id)
            ->whereMonth('tanggal_tagihan', $bulan)
            ->whereYear('tanggal_tagihan', $tahun)
            ->first();

            $tanggalBayar = '';
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

        $tagihan = Model::with('pembayaran')->findOrFail($id);
        $data['model'] = new Pembayaran();
        $data['tagihan'] = $tagihan;
        $data['ananda'] = $tagihan->ananda;
        $data['periode'] = $tagihan->tanggal_tagihan->translatedFormat('F Y');
        return view('operator.'.$this->viewShow, $data);
    }

    public function destory()
    {
        # code...
    }
}
