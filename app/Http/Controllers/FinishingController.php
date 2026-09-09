<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FinishingController extends Controller
{

    public function index()
    {
        $data = Finishing::all();
        return view('finishing.index',compact('data'));
    }

    public function store(Request $request)
    {
        Finishing::create($request->all());

        return redirect()->back()->with('success','Finishing berhasil ditambahkan');
    }

    public function destroy($id)
    {
        Finishing::findOrFail($id)->delete();

        return redirect()->back()->with('success','Finishing dihapus');
    }

}