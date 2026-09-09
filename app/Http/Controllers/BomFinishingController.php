<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
class BomFinishingController extends Controller
{

    public function store(Request $request)
    {

        BomFinishing::where('id_produk',$request->id_produk)->delete();

        foreach($request->finishing as $f){

            BomFinishing::create([
                'id_produk'=>$request->id_produk,
                'id_finishing'=>$f,
                'qty'=>1
            ]);

        }

        return back()->with('success','Finishing disimpan');

    }

}