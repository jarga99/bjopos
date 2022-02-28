<?php

namespace App\Http\Controllers;

use App\Models\GoodsMaster;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\Setting;
use Illuminate\Http\Request;
use PDF;
use App\Models\Stock;
use App\Models\Discount;
use Illuminate\Support\Facades\Auth;
use Session;
use Illuminate\Support\Facades\Validator;

class PenjualanController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $tanggalAkhir = date('Y-m-d');

        if ($request->has('tanggal_awal') && $request->tanggal_awal != "" && $request->has('tanggal_akhir') && $request->tanggal_akhir) {
            $tanggalAwal = $request->tanggal_awal;
            $tanggalAkhir = $request->tanggal_akhir;
        }

        return view('penjualan.index', compact('tanggalAwal', 'tanggalAkhir'));
    }

    public function data(Request $request)
    {
        $penjualan = Penjualan::withTrashed()->where('nama_customer', '!=', '')->orderBy('id', 'desc');

        if($request->tanggal_awal != null && $request->tanggal_akhir != null) {
            $penjualan = $penjualan->whereBetween('created_at', [$request->tanggal_awal, $request->tanggal_akhir]);
        }

        $penjualan = $penjualan->get();

        return datatables()
            ->of($penjualan)
            ->addIndexColumn()
            ->addColumn('total_item', function ($penjualan) {
                return format_uang($penjualan->total_item);
            })
            ->addColumn('nama_customer', function($penjualan) {
                return $penjualan->nama_customer;
            })
            ->addColumn('total_harga', function ($penjualan) {
                return 'Rp. '. format_uang($penjualan->total_harga);
            })
            ->addColumn('bayar', function ($penjualan) {
                return 'Rp. '. format_uang($penjualan->bayar);
            })
            ->addColumn('tanggal', function ($penjualan) {
                return tanggal_indonesia($penjualan->created_at, false);
            })
            ->addColumn('status', function($penjualan) {
                if($penjualan->status == 1) {
                    $status = '<span class="text-success fw7 fsi">Success</span>';
                } elseif($penjualan->status == 2) {
                    $status = '<span class="text-warning fw7 fsi">Edited</span>';
                } elseif($penjualan->status == 3) {
                    $status = '<span class="text-danger fw7 fsi">Canceled</span>';
                } 

                return $status;
            })
            // ->addColumn('kode_member', function ($penjualan) {
            //     $member = $penjualan->member->kode_member ?? '';
            //     return '<span class="label label-success">'. $member .'</spa>';
            // })
            // ->editColumn('diskon', function ($penjualan) {
            //     return $penjualan->diskon . '%';
            // })
            ->editColumn('kasir', function ($penjualan) {
                return $penjualan->user->name ?? '';
            })
            ->addColumn('aksi', function ($penjualan) {
                $detail = '<button onclick="showDetail(`'. route('penjualan.show', $penjualan->id) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>';
                if($penjualan->status == 1 || $penjualan->status == 2) {
                    $edit = '<a href="'. route('transaksi.edit', $penjualan->id) .'" class="btn btn-xs btn-warning btn-flat"><i class="fa fa-pencil"></i></a>';
                    $delete = '<button onclick="deleteData(`'. route('penjualan.destroy', $penjualan->id) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>';
                } else {
                    $edit = '';
                    $delete = '';
                }
                return '
                <div class="btn">
                    '. $detail .'
                    '. $edit .'
                    '. $delete .'
                </div>
                ';
            })
            ->rawColumns(['aksi', 'status']) //, 'kode_member'  jika ingin menambakan code member
            ->make(true);
    }

    public function create()
    {
        $penjualan = new Penjualan();
        // $penjualan->id_member = null;
        $penjualan->nama_customer = "";
        $penjualan->nomor_meja = "";
        $penjualan->total_item = 0;
        $penjualan->total_harga = 0;
        $penjualan->diskon = 0;
        // $penjualan->modal_product = 0;
        $penjualan->bayar = 0;
        $penjualan->diterima = 0;
        $penjualan->kembali = 0;
        $penjualan->id_user = Auth::user()->id;
        $penjualan->save();

        session(['id_penjualan' => $penjualan->id]);
        return redirect()->route('transaksi.index');
    }

    public function transaksiBelumDisimpan($id)
    {
        session(['id_penjualan' => $id]);
        return redirect()->route('transaksi.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_customer' => 'required',
            'nomor_meja' => 'required',
            'diterima' => 'required'
        ]);

        if ($validator->fails()) {
            return back()
                        ->withErrors($validator)
                        ->withInput();
        }

        $penjualan = Penjualan::findOrFail($request->id_penjualan);
        // $penjualan->id_member = $request->id_member;
        $penjualan->nama_customer = $request->nama_customer;
        $penjualan->nomor_meja = $request->nomor_meja;
        $penjualan->total_item = $request->total_item;
        $penjualan->total_harga = $request->total;
        $penjualan->diskon = $request->diskon;
        $penjualan->bayar = $request->bayar;
        $penjualan->diterima = $request->diterima;
        $penjualan->kembali = $request->kembali;
        if($request->edit == 1) {
            $status = 1;
        } elseif($request->edit == 2) {
            $status = 2;
        }
        $penjualan->status = $status;
        $penjualan->update();

        $detail = PenjualanDetail::where('id_penjualan', $penjualan->id)->get();
        foreach ($detail as $item) {

            $produk = GoodsMaster::with('stock', 'produk.discount')->where('id', $item->id_produk)->first();

            $stok = Stock::where('id_produk', $item->id_produk)->first();
            $stok->stok_produk = $stok->stok_produk - $item->jumlah;
            $stok->save();

            Discount::create([
                'id_produk' => $produk->id,
                'id_penjualan' => $item->id_penjualan,
                'amount' => $request->diskon
            ]);


        }
        $request->session()->put('penjualan', $penjualan);
        return redirect()->route('transaksi.selesai');
    }

    public function show($id)
    {
        $detail = PenjualanDetail::with('goods_master.produk', 'goods_master.stock')->where('id_penjualan', $id)->get();

        return datatables()
            ->of($detail)
            ->addIndexColumn()
            ->addColumn('kode_produk', function ($detail) {
                return '<span class="label label-success">'. $detail->goods_master->produk->kode_produk .'</span>';
            })
            ->addColumn('nama_produk', function ($detail) {
                return $detail->goods_master->produk->nama_produk;
            })
            ->addColumn('harga_jual', function ($detail) {
                return 'Rp. '. format_uang($detail->harga->harga_jual);
            })
            ->addColumn('jumlah', function ($detail) {
                return format_uang($detail->jumlah);
            })
            ->addColumn('subtotal', function ($detail) {
                return 'Rp. '. format_uang($detail->subtotal);
            })
            ->rawColumns(['kode_produk'])
            ->make(true);
    }

    public function destroy($id)
    {
        $penjualan = Penjualan::find($id);
        $detail    = PenjualanDetail::where('id_penjualan', $penjualan->id_penjualan)->get();
        foreach ($detail as $item) {
            $produk = Produk::find($item->id);
            if ($produk) {
                $produk->stok += $item->jumlah;
                $produk->update();
            }

            $item->delete();
        }
        if($penjualan) {
            $penjualan->status = 3;
            $penjualan->update();
        }
        $penjualan->delete();

        return response(null, 204);
    }

    public function selesai(Request $request)
    {
        $setting = Setting::first();
        $penjualan = $request->session()->get('penjualan');

        if(!session()->has('penjualan')){
            return redirect()->back();
        }

        return view('penjualan.selesai', compact('setting', 'penjualan'));
    }

    public function edit($id)
    {
        $data['penjualan'] = Penjualan::where('id', $id)->first();
        $data['penjualan_detail'] = PenjualanDetail::where('id_penjualan', $id)->get();
        $data['produk'] = GoodsMaster::with('produk', 'stock', 'kategori')->orderBy('id_produk', 'desc')->get();
        $data['diskon'] = Setting::first()->diskon ?? 0;
        return view('penjualan.edit', $data);
    }

    public function notaKecil()
    {
        $setting = Setting::first();
        $penjualan = Penjualan::find(session('id_penjualan'));
        if (! $penjualan) {
            abort(404);
        }
        $detail = PenjualanDetail::with('goods_master.produk.discount', 'goods_master.stock')
            ->where('id_penjualan', session('id_penjualan'))
            ->get();

        return view('penjualan.nota_kecil', compact('setting', 'penjualan', 'detail'));
        // $d = [
        //     'setting' => $setting,
        //     'penjualan' => $penjualan,
        //     'detail' => $detail
        // ];
        // return $d;
    }

    public function notaBesar()
    {
        $setting = Setting::first();
        $penjualan = Penjualan::find(session('id_penjualan'));
        if (! $penjualan) {
            abort(404);
        }
        $detail = PenjualanDetail::with('goods_master.produk.discount', 'goods_master.stock')
            ->where('id_penjualan', session('id_penjualan'))
            ->get();

        $pdf = PDF::loadView('penjualan.nota_besar', compact('setting', 'penjualan', 'detail'));
        $pdf->setPaper(0,0,609,440, 'potrait');
        return $pdf->stream('Transaksi-'. date('Y-m-d-his') .'.pdf');
    }

    public function exportPDF(Request $request)
    {
        $awal = $request->form_awal;    
        $akhir = $request->form_akhir;
        $data = Penjualan::with('detail.produk')->where('nama_customer', '!=', '')->whereBetween('created_at', [$awal, $akhir]);
        $penjualan = $data->get();
        $total_orderan = $data->count();
        $total_pembatalan = Penjualan::with('detail.produk')->withTrashed()->where('nama_customer', '!=', '')->whereBetween('created_at', [$awal, $akhir])->where('status', 3)->sum('total_harga');
        $total_kembali = Penjualan::with('detail.produk')->where('nama_customer', '!=', '')->whereBetween('created_at', [$awal, $akhir])->where('status', '!=', 3)->sum('kembali');
        $total_bayar = Penjualan::with('detail.produk')->where('nama_customer', '!=', '')->whereBetween('created_at', [$awal, $akhir])->where('status', '!=', 3)->sum('total_harga');
        $total_terima = Penjualan::with('detail.produk')->where('nama_customer', '!=', '')->whereBetween('created_at', [$awal, $akhir])->where('status', '!=', 3)->sum('diterima');
        $pemasukan_bersih = $total_terima - $total_kembali;
        $pemasukan_kotor = $total_terima;
        $pdf  = PDF::loadView('penjualan.pdf', compact('awal', 'akhir', 'penjualan', 'total_orderan', 'total_pembatalan', 'total_kembali', 'pemasukan_bersih', 'pemasukan_kotor'));
        $pdf->setPaper('a4', 'potrait');

        return $pdf->download('Laporan-penjualan-'. date('Y-m-d-his') .'.pdf');
        // return $total_kembali;
    }
}
