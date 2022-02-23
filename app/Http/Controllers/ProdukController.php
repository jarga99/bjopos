<?php

namespace App\Http\Controllers;

use App\Models\GoodsMaster;
use App\Models\Discount;
use App\Models\Harga;
use App\Models\Stock;
use App\Models\Modal;
use App\Models\Kategori;
use Illuminate\Http\Request;
use App\Models\Produk;
use PDF;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $kategori = Kategori::all()->pluck('nama_kategori', 'id_kategori');

        // new
        $kategori = Kategori::all();
        $discount = Discount::all();

        return view('produk.index', compact('kategori', 'discount'));
    }

    public function data()
    {
        // $produk = Produk::leftJoin('kategori', 'kategori.id_kategori', 'produk.id_kategori')
        //     ->select('produk.*', 'nama_kategori')
        //     ->orderBy('kode_produk', 'asc')
        //     ->get();


        // new
        $produk = GoodsMaster::with('produk', 'stock', 'kategori')->orderBy('id_produk', 'desc')->get();
        return datatables()
            ->of($produk)
            ->addIndexColumn()
            ->addColumn('select_all', function ($produk) {
                return '
                    <input type="checkbox" name="id_produk[]" value="' . $produk->id . '">
                ';
            })
            ->addColumn('kode_produk', function ($produk) {
                return '<span class="label label-success">' . $produk->produk->kode_produk . '</span>';
            })
            // ->addColumn('harga_beli', function ($produk) {
            //     return format_uang($produk->modal->harga_beli);
            // })
            ->addColumn('harga_jual', function ($produk) {
                return format_uang($produk->harga->harga_jual);
            })
            ->addColumn('stok', function ($produk) {
                return format_uang($produk->stock->stok_produk);
            })
            ->addColumn('aksi', function ($produk) {
                return '
                <div class="btn-group">
                    <button type="button" onclick="editForm(`' . route('produk.update', $produk->id) . '`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                    <button type="button" onclick="deleteData(`' . route('produk.destroy', $produk->id) . '`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['aksi', 'kode_produk', 'select_all'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // if ($request->hasFile('photo')) {
        //     $file = $request->file('photo');
        //     $name = $request->nama_produk . '.' . $file->getClientOriginalExtension();
        //     $request->file('photo')->move("storage/produk/", $name);
        //     $photo = $name;
        // } else {
        //     $photo = '/img/logo.png';
        // }


        $produk = Produk::latest()->first() ?? new Produk();
        $stok = Stock::latest()->first() ?? new Stock();
        $modal = Modal::latest()->first() ?? new Modal();
        // $request['kode_produk'] = 'P'. tambah_nol_didepan((int)$produk->id +1, 6);

        // $produk = Produk::create($request->all());

        // new
        $kode = 'P' . tambah_nol_didepan((int)$produk->id + 1, 6);
        $produk = Produk::create([
            'kode_produk' => $kode,
            'id_kategori' => $request->id_kategori,
            'nama_produk' => $request['nama_produk'],
            // 'photo' => $photo,
            // 'price' => $request['harga_jual']
        ]);

        $stok_id = (int)$stok->id + 1;
        $stok = Stock::create([
            'id' => $stok_id,
            'id_produk' => $produk->id,
            'stok_produk' => $request->stok
            // 'stok_produk' => $request->stok
        ]);

        $modal_id = (int)$modal->id + 1;
        $modal = Modal::create([
            'id' => $modal_id,
            'id_produk' => $produk->id,
            // 'modal_product' => $request['harga_beli']
            'harga_beli' => (int) $request['harga_jual'] - 100 // dikurang 100 perak
        ]);

        // new
        $harga = Harga::create([
            'id_produk' => $produk->id,
            'harga_jual' => $request['harga_jual']
        ]);

        GoodsMaster::create([
            // 'kode_produk' => $kode,
            'id_produk' => $produk->id,
            'id_kategori' => $request['id_kategori'],
            'id_harga' => $harga->id,
            'id_modal' => $modal->id,
            'id_stock' => $stok->id
        ]);

        return response()->json('Data berhasil disimpan', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $produk = Produk::find($id);

        // new
        $produk = GoodsMaster::with('produk', 'stock', 'kategori', 'modal', 'harga')->where('id', $id)->first();

        return response()->json($produk);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // $produk = Produk::find($id);
        // $produk->update($request->all());

        // new

        $master = GoodsMaster::where('id', $id)->first();
        $master->update([
            'id_kategori' => $request['id_kategori']
        ]);

        if ($master) {
            Produk::where('id', $master->id_produk)->update([
                'nama_produk' => $request['nama_produk'],
                // 'price' => $request['harga_jual']
            ]);
            Modal::where('id', $master->id_modal)->update([
                'harga_beli' => $request['harga_jual'] - 100 // dikurang 100 perak
            ]);
            Stock::where('id', $master->id_stock)->update([
                'stok_produk' => $request->stok
            ]);
            Harga::where('id', $master->id_harga)->update([
                'harga_jual' => $request['harga_jual'],
            ]);
        }

        return response()->json('Data berhasil disimpan', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // $produk = Produk::find($id);
        // $produk->delete();

        // new
        $master = GoodsMaster::find($id);

        Produk::where('id', $master->id_produk)->delete();
        Discount::where('id_produk', $master->id_produk)->delete();
        Modal::where('id', $master->id_modal)->delete();
        Stock::where('id', $master->id_stock)->delete();

        $master->delete();

        return response(null, 204);
    }

    public function deleteSelected(Request $request)
    {
        foreach ($request->id_produk as $id) {
            // $produk = Produk::find($id);
            // $produk->delete();

            $master = GoodsMaster::find($id);

            Produk::where('id', $master->id_produk)->delete();
            Discount::where('id_produk', $master->id_produk)->delete();
            Modal::where('id', $master->id_modal)->delete();
            Stock::where('id', $master->id_stock)->delete();

            $master->delete();
        }

        return response(null, 204);
    }

    public function cetakBarcode(Request $request)
    {
        $dataproduk = array();
        foreach ($request->id_produk as $id) {
            // $produk = Produk::find($id);
            $produk = GoodsMaster::find($id);
            $dataproduk[] = $produk;
        }

        $no  = 1;
        $pdf = PDF::loadView('produk.barcode', compact('dataproduk', 'no'));
        $pdf->setPaper('a4', 'potrait');
        return $pdf->stream('produk.pdf');
    }
}
