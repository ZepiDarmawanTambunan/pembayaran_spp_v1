<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ananda;
use App\Models\Biaya;
use App\Models\User;
use DB;

class MigrasiController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'listKelompok' => getNamaKelompok(),
            'listStatus' => getStatusAnanda(),
            'listBiaya' => Biaya::whereNull('parent_id')->pluck('nama', 'id'),
            'method' => 'POST',
            'route' => ['migrasiform.store'],
            'button' => 'PINDAH',
            'title' => 'FORM MIGRASI ANANDA',
            'url' => route('migrasiform.index'),
            'ananda' => Ananda::orderBy('kelompok', 'DESC')->orderBy('biaya_id', 'DESC'),
        ];
        $data['ananda_all'] = $data['ananda']->pluck('id')->toArray();

        if($request->kelompok_asal_id != null){
            $data['ananda'] = $data['ananda']->where('kelompok', $request->kelompok_asal_id);
        }
        if($request->biaya_asal_id != null){
            $data['ananda'] = $data['ananda']->where('biaya_id', $request->biaya_asal_id);
        }
        if($request->status_asal_id != null){
            $data['ananda'] = $data['ananda']->get()->where('status', $request->status_asal_id);
        }else{
            $data['ananda'] = $data['ananda']->get();
        }

        return view('operator.migrasi_form', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ananda_id.*' => 'required|exists:anandas,id',
            'kelompok_tujuan_id' => 'nullable',
            'biaya_tujuan_id' => 'nullable',
            'status_tujuan_id' => 'nullable',
        ]);

        if($request->ananda_id != null){
            $ananda = Ananda::query()->whereIn('id', $request->ananda_id);

            if($request->kelompok_tujuan_id != null){
                // ubah kelas
                $ananda->update(['kelompok' => $request->kelompok_tujuan_id]);
            }
            if($request->biaya_tujuan_id != null){
                // ubah biaya
                $ananda->update(['biaya_id' => $request->biaya_tujuan_id]);
            }
            if($request->status_tujuan_id != null){
                // ubah status
                foreach ($ananda->get() as $key => $value) {
                    $value->setStatus($request->status_tujuan_id);
                    $value->save();
                }
            }
        }

        flash()->addSuccess('Data berhasil dimigrasi');
        return back();
    }
}
