<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelian;
use Illuminate\Support\Facades\Auth;

class PembelianController extends Controller
{
    public function index()
    {
        return view('pembelian.index');
    }

    public function data(Request $request)
    {
        $pembelian = Pembelian::orderBy('id', 'asc');

        if($request->tanggal_awal != null && $request->tanggal_akhir != null) {
            $pembelian->whereBetween('created_at', [$request->tanggal_awal, $request->tanggal_akhir]);
        }

        $pembelian = $pembelian->get();
        
        return datatables()
            ->of($pembelian)
            ->addIndexColumn()


            ->addColumn('tanggal', function ($pembelian) {
                return tanggal_indonesia($pembelian->created_at, false);
            })
            ->addColumn('nama_bahan', function ($pembelian) {
                return $pembelian->nama_bahan;
            })
            ->addColumn('jumlah', function ($pembelian) {
                return $pembelian->jumlah;
            })
            ->addColumn('satuan', function ($pembelian) {
                return $pembelian->satuan;
            })
            ->addColumn('harga', function ($pembelian) {
                return format_uang($pembelian->harga);
            })
            ->addColumn('user', function ($pembelian) {
                return $pembelian->user->name;
            })

            ->addColumn('aksi', function ($pembelian) {
                return '
                <div class="btn-group">
                    <button onclick="editForm(`'. route('pembelian.show', $pembelian->id) .'`)" class="btn btn-xs btn-warning btn-flat"><i class="fa fa-pencil"></i></button>
                    <button onclick="deleteData(`'. route('pembelian.destroy', $pembelian->id) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create($id)
    {
        //
    }

    public function store(Request $request)
    {
        $pembelian = new Pembelian();
        $pembelian->id_user = Auth::user()->id;
        $pembelian->nama_bahan = $request->nama_bahan;
        $pembelian->satuan = $request->satuan;
        $pembelian->harga = $request->harga;
        $pembelian->jumlah = $request->jumlah;
        $pembelian->created_at = date(now());
        $pembelian->updated_at = date(now());
        $pembelian->save();

        return response()->json('Data berhasil disimpan', 200);
    }

    public function update(Request $request, $id)
    {
        $pembelian = Pembelian::find($id);
        $pembelian->id_user = Auth::user()->id;
        $pembelian->nama_bahan = $request->nama_bahan;
        $pembelian->satuan = $request->satuan;
        $pembelian->harga = $request->harga;
        $pembelian->jumlah = $request->jumlah;
        $pembelian->created_at = date(now());
        $pembelian->updated_at = date(now());
        $pembelian->save();

        return response()->json('Data berhasil disimpan', 200);
    }

    public function show($id)
    {
        $detail = Pembelian::find($id);
        return response()->json($detail);
    }

    public function destroy($id)
    {
        $pembelian = Pembelian::find($id);
        $pembelian->delete();

        return response(null, 204);
    }
}
