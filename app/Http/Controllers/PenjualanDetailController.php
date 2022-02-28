<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\GoodsMaster;

class PenjualanDetailController extends Controller
{
    public function index()
    {
        // $produk = Produk::orderBy('nama_produk')->get();
        // new
        $produk = GoodsMaster::with('produk', 'stock', 'kategori')->orderBy('id_produk', 'desc')->get();
        $diskon = Setting::first()->diskon ?? 0;

        // Cek apakah ada transaksi yang sedang berjalan
        if (session('id_penjualan') != null) {
            $id_penjualan = session('id_penjualan');
            $penjualan = Penjualan::find($id_penjualan);
            return view('penjualan_detail.index', compact('produk', 'diskon', 'id_penjualan', 'penjualan'));
        } else {
            dd('sesi abis');
            if (auth()->user()->level == 1) {
                return redirect()->route('transaksi.baru');
            } else {
                return redirect()->route('home');
            }
        }
    }

    public function data($id)
    {
        $detail = PenjualanDetail::with('goods_master.produk', 'goods_master.stock')
                ->where('id_penjualan', $id)
                ->get();
        $data = array();
        $total = 0;
        $total_item = 0;

        foreach ($detail as $item) {
            $row = array();
            $row['kode_produk'] = '<span class="label label-success">'. $item->goods_master->produk['kode_produk'] .'</span';
            $row['nama_produk'] = $item->goods_master->produk->nama_produk;
            $row['harga_jual']  = 'Rp. '. format_uang($item->goods_master->harga->harga_jual);
            $row['stok']        = $item->goods_master->stock->stok_produk . '<input type="hidden" class="stok'. $item->id .'" data-id="'. $item->id .'" value="'. $item->goods_master->stock->stok_produk .'">';
            $row['jumlah']      = '<input type="number" class="form-control input-sm quantity" data-id="'. $item->id .'" value="'. $item->jumlah .'" min="1" oninput="validity.valid">';
            // $row['diskon']      = $item->diskon . '%';
            $row['subtotal']    = 'Rp. '. format_uang($item->subtotal);
            $row['aksi']        = '<div class="btn-group">
                                    <button onclick="deleteData(`'. '/transaksi/' . $item->id .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                                </div>';
            $data[] = $row;

            $total += $item->goods_master->harga->harga_jual * $item->jumlah;
            $total_item += $item->jumlah;
        }
        $data[] = [
            'kode_produk' => '
                <div class="total hide">'. $total .'</div>
                <div class="total_item hide">'. $total_item .'</div>',
            'nama_produk' => '',
            'harga_jual'  => '',
            'stok'        => '',
            'jumlah'      => '',
            // 'diskon'      => '',
            'subtotal'    => '',
            'aksi'        => '',
        ];

        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->rawColumns(['aksi', 'kode_produk', 'jumlah', 'stok'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $produks = GoodsMaster::where('id', $request->id)->first();
        if (! $produks) {
            return response()->json('Data gagal disimpan', 400);
        }

        $detail = new PenjualanDetail();
        $detail->id_penjualan = session('id_penjualan');
        $detail->id_produk = $produks->id_produk;
        $detail->harga_jual = $produks->harga->id;
        $detail->jumlah = 1;
        $detail->diskon = 0;
        $detail->subtotal = $produks->harga->harga_jual;
        $detail->save();
        return response()->json('Data berhasil disimpan', 200);

    }

    public function update(Request $request, $id)
    {
        $detail = PenjualanDetail::findOrFail($id);
        $detail->jumlah = $request->jumlah;
        $detail->subtotal = $detail->harga->harga_jual * $request->jumlah;
        $detail->update();

        return response()->json('Data berhasil disimpan', 200);
    }

    public function destroy($id)
    {
        $detail = PenjualanDetail::find($id);
        $detail->delete();

        return response(null, 204);
    }

    public function loadForm($diskon = 0, $total = 0, $diterima = 0)
    {
        $bayar   = $total - ($diskon / 100 * $total);
        $kembali = ($diterima != 0) ? $diterima - $bayar : 0;
        $data    = [
            'totalrp' => format_uang($total),
            'bayar' => $bayar,
            'bayarrp' => format_uang($bayar),
            'terbilang' => ucwords(terbilang($bayar). ' Rupiah'),
            'kembali' => $kembali,
            'kembalirp' => format_uang($kembali),
            'kembali_terbilang' => ucwords(terbilang($kembali). ' Rupiah'),
        ];

        return response()->json($data);
    }
}
