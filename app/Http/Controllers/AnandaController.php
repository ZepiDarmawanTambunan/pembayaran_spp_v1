<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnandaRequest;
use App\Http\Requests\UpdateAnandaRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Biaya;
use App\Charts\AnandaKelompokChart;
use App\Models\Ananda as Model;
use Illuminate\Support\Facades\Storage;

class AnandaController extends Controller
{
    private $viewIndex = 'ananda_index';
    private $viewCreate = 'ananda_form';
    private $viewEdit = 'ananda_form';
    private $viewShow = 'ananda_show';
    private $routePrefix = 'ananda';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, AnandaKelompokChart $anandaKelompokChart)
    {
        $models = Model::with('wali', 'user')->latest();
        if($request->filled('q')){
            $models = $models->search($request->q);
        }

        return view('operator.'.$this->viewIndex, [
            'models' => $models->paginate(settings()->get('app_pagination', '20')),
            'routePrefix' => $this->routePrefix,
            'title' => 'DATA ANANDA',
            'anandaKelompokChart' => $anandaKelompokChart->build(),
        ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = [
            'listBiaya' => Biaya::whereNull('parent_id')->has('children')->pluck('nama', 'id'),
            'model' => new Model(),
            'method' => 'POST',
            'route' => $this->routePrefix.'.store',
            'button' => 'SIMPAN',
            'title' => 'FORM DATA ANANDA',
            'wali' => User::where('akses', 'wali')->pluck('name', 'id'),
        ];
        return view('operator.'.$this->viewCreate, $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAnandaRequest $request)
    {
        // $reqData = $request->validate([...]); old
        $reqData = $request->validated();

        if($request->hasFile('foto')){
            $reqData['foto'] = $request->file('foto')->store('public');
        }

        if($request->filled('wali_id')){
            $reqData['wali_status'] = 'ok';
        }
        $siswa = Model::create($reqData);

        // return response()->json([
        //     'message' => 'Data berhasil disimpan',
        // ], 200);
        flash()->addSuccess('Data berhasil disimpan');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view('operator.'.$this->viewShow, [
            'model' => Model::findOrFail($id),
            'title' => 'DETAIL ANANDA'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = [
            'listBiaya' => Biaya::whereNull('parent_id')->has('children')->pluck('nama', 'id'),
            'model' => Model::findOrFail($id),
            'method' => 'PUT',
            'route' => [$this->routePrefix.'.update', $id],
            'button' => 'UPDATE',
            'title' => 'FORM DATA ANANDA',
            'wali' => User::where('akses', 'wali')->pluck('name', 'id'),
        ];

        return view('operator.'.$this->viewEdit, $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAnandaRequest $request, $id)
    {
        $reqData = $request->validated();

        $model = Model::findOrFail($id);

        if($request->hasFile('foto')){
            if($model->foto != null && Storage::exists($model->foto)){
                Storage::delete($model->foto);
            }
            $reqData['foto'] = $request->file('foto')->store('public');
        }

        if($request->filled('wali_id')){
            $reqData['wali_status'] = 'ok';
        }

        $model->fill($reqData);
        $model->save();
        flash()->addSuccess('Data berhasil diubah');
        return redirect()->route($this->routePrefix.'.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $ananda = Model::findOrFail($id);
        if($ananda->tagihan->count() >= 1){
            flash()->addError('Data tidak bisa dihapus karena memiliki relasi dengan data tagihan');
            return back();
        }
        if($ananda->foto != null && Storage::exists($ananda->foto)){
            Storage::delete($ananda->foto);
        }
        $ananda->delete();
        flash()->addSuccess('Data berhasil dihapus');
        return back();
    }
}
