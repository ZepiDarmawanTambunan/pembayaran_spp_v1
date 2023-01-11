<?php

namespace App\Http\Controllers;

use App\Models\Ananda;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function update(Request $request)
    {
        if($request->model == 'ananda'){
            $model = Ananda::findOrFail($request->id);
            $model->setStatus($request->status);
            $model->save();
            flash('Status berhasil diubah');
            return back();
        }
    }
}
