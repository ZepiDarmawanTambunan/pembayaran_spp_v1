<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRekeningYayasanRequest;
use App\Http\Requests\UpdateRekeningYayasanRequest;
use Illuminate\Http\Request;
use App\Models\Bank;
use App\Models\RekeningYayasan as Model;

class RekeningYayasanController extends Controller
{
    private $viewIndex = 'rekeningyayasan_index';
    private $viewCreate = 'rekeningyayasan_form';
    private $viewEdit = 'rekeningyayasan_form';
    private $viewShow = 'rekeningyayasan_show';
    private $routePrefix = 'rekeningyayasan';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $models = Model::paginate(settings()->get('app_pagination', '20'));

        return view('operator.'.$this->viewIndex, [
            'models' => $models,
            'routePrefix' => $this->routePrefix,
            'title' => 'DATA REKENING YAYASAN'
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
            'model' => new Model(),
            'method' => 'POST',
            'route' => $this->routePrefix.'.store',
            'button' => 'SIMPAN',
            'title' => 'FORM DATA REKENING YAYASAN',
            'listBank' => Bank::pluck('nama_bank', 'id'),
        ];

        return view('operator.'.$this->viewCreate, $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRekeningYayasanRequest $request)
    {
        $reqData = $request->validated();
        $bank = Bank::findOrFail($request['bank_id']);
        unset($reqData['bank_id']);
        $reqData['kode'] = $bank->sandi_bank;
        $reqData['nama_bank'] = $bank->nama_bank;
        Model::create($reqData);
        flash('Data berhasil disimpan');
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $model = Model::findOrFail($id);
        $data = [
            'model' => $model,
            'method' => 'PUT',
            'route' => [$this->routePrefix.'.update', $id],
            'button' => 'UPDATE',
            'title' => 'FORM DATA REKENING YAYASAN',
            'listBank' => Bank::pluck('nama_bank', 'sandi_bank'),
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
    public function update(UpdateRekeningYayasanRequest $request, $id)
    {
        $reqData = $request->validated();
        $model = Model::findOrFail($id);
        $reqData['kode'] = $model->kode;
        $reqData['nama_bank'] = $model->nama_bank;
        $model->fill($reqData);
        $model->save();
        flash('Data berhasil diubah');
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
        $model = Model::findOrFail($id);
        if($model->pembayaran->count() >= 1){
            flash()->addError('data gagal dihapus karena terkait data lain');
            return back();
        }
        $model->delete();
        flash('Data berhasil dihapus');
        return back();
    }
}
