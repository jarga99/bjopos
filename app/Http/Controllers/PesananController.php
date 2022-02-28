<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;

class PesananController extends Controller
{
    public function baru()
    {
        return view('dapur.pesanan');
    }

    public function data()
    {
        $penjualan = Penjualan::withTrashed()->where('nama_customer', '!=', '')->orderBy('id', 'desc')->where('status', '!=', 3)->get();

        return datatables()
            ->of($penjualan)
            ->addIndexColumn()
            ->addColumn('total_item', function ($penjualan) {
                return format_uang($penjualan->total_item);
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
            // ->addColumn('kode_member', function ($penjualan) {
            //     $member = $penjualan->member->kode_member ?? '';
            //     return '<span class="label label-success">'. $member .'</spa>';
            // })
            // ->editColumn('diskon', function ($penjualan) {
            //     return $penjualan->diskon . '%';
            // })
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
            ->editColumn('kasir', function ($penjualan) {
                return $penjualan->user->name ?? '';
            })
            ->addColumn('aksi', function ($penjualan) {
                $url = route('pesanan.status', ['id' => $penjualan->id]);
                if($penjualan->status != 1) {
                    $update = '<a href="'.$url.'" class="btn btn-xs btn-success btn-flat"><i class="fa fa-check"></i></a>';
                } else {
                    $update = '';
                }
                $html = '<div class="btn">
                        <button onclick="showDetail(`'. route('pesanan.detail', $penjualan->id) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
                        '. $update .'
                    </div>';
                return $html;
            })
            ->rawColumns(['aksi', 'status']) //, 'kode_member'  jika ingin menambakan code member
            ->make(true);
    }

    public function history()
    {
        return view('dapur.history_pesanan');
    }

    public function historyData()
    {
        $penjualan = Penjualan::withTrashed()->where('nama_customer', '!=', '')->orderBy('id', 'desc')->get();

        return datatables()
            ->of($penjualan)
            ->addIndexColumn()
            ->addColumn('total_item', function ($penjualan) {
                return format_uang($penjualan->total_item);
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
            // ->addColumn('kode_member', function ($penjualan) {
            //     $member = $penjualan->member->kode_member ?? '';
            //     return '<span class="label label-success">'. $member .'</spa>';
            // })
            // ->editColumn('diskon', function ($penjualan) {
            //     return $penjualan->diskon . '%';
            // })
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
            ->editColumn('kasir', function ($penjualan) {
                return $penjualan->user->name ?? '';
            })
            ->addColumn('aksi', function ($penjualan) {
                $html = '<div class="btn">
                <button onclick="showDetail(`'. route('pesanan.detail', $penjualan->id) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
            </div>';
            return $html;
            })
            ->rawColumns(['aksi', 'status']) //, 'kode_member'  jika ingin menambakan code member
            ->make(true);
    }

    public function updateStatus($id)
    {
        Penjualan::where('id', $id)->update([
            'status' => 1
        ]);
        return redirect()->back()->with(['success' => "Change Status Success"]);
    }

    public function detail($id)
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
}
