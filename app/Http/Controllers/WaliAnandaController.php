<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ananda;

class WaliAnandaController extends Controller
{
    public function store(Request $request)
    {
        $reqData = $request->validate([
            'wali_id' => 'required|exists:users,id',
            'ananda_id' => 'required|exists:anandas,id'
        ]);

        $ananda = Ananda::find($request->ananda_id);
        $ananda->wali_id = $request->wali_id;
        $ananda->wali_status = 'ok';
        $ananda->save();
        flash('Data sudah ditambahkan');
        return back();
    }

    public function update(Request $request, $id)
    {
        $ananda = Ananda::findOrFail($id);
        $ananda->wali_id = null;
        $ananda->wali_status = null;
        $ananda->save();
        flash('Data sudah dihapus');
        return back();
    }
}
