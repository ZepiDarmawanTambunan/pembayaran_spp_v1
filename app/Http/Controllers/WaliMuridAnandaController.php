<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ananda;

class WaliMuridAnandaController extends Controller
{
    public function index()
    {
        $models = Auth::user()->ananda;
        return view('wali.ananda_index', compact('models'));
    }


    function show($id)
    {
        $data['model'] = Ananda::with('biaya', 'biaya.children')
        ->where('id', $id)
        ->where('wali_id', Auth::user()->id)
        ->firstOrFail();
        return view('wali.ananda_show',$data);
    }
}
