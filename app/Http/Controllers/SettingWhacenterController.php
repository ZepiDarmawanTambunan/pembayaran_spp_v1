<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WhacenterService;


class SettingWhacenterController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try {
            $ws = new WhacenterService();
            $statusKoneksiWa = $ws->getDeviceStatus();
        } catch (\Throwable $th) {
            $statusKoneksiWa = false;
        }
        return view('operator.settingwhacenter_form', [
            'statusKoneksiWa' => $statusKoneksiWa,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($request->has('tes_wa')){
            $ws = new WhacenterService();
            $statusSend = $ws->line('Testing koneksi WA')->to($request->tes_wa)->send();
            if($statusSend){
                flash('Data sudah diproses. Status '.$ws->getMessage());
                return back();
            }
            flash()->addError('Data gagal diproses. Status '.$ws->getMessage());
            return back();
        }
        $dataSetting = $request->except('_token');
        settings()->set($dataSetting);
        flash('Data sudah disimpan');
        return back();
    }
}
