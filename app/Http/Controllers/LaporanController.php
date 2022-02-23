<?php

namespace App\Http\Controllers;

// use App\Models\Pembelian;

use App\Models\Pembelian;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use PDF;
use DB;
use Illuminate\Support\Facades\DB as FacadesDB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $tanggalAkhir = date('Y-m-d');

        if ($request->has('tanggal_awal') && $request->tanggal_awal != "" && $request->has('tanggal_akhir') && $request->tanggal_akhir) {
            $tanggalAwal = $request->tanggal_awal;
            $tanggalAkhir = $request->tanggal_akhir;
        }

        return view('laporan.index', compact('tanggalAwal', 'tanggalAkhir'));
    }

    public function getData($awal, $akhir)
    {
        $no = 1;
        $data = array();
        $total_laba_kotor = 0;

        while (strtotime($awal) <= strtotime($akhir)) {
            $tanggal = $awal;
            $awal = date('Y-m-d', strtotime("+1 day", strtotime($awal)));


            $pembelian = Pembelian::where('created_at', 'LIKE', "%$tanggal%");
            $penjualan = Penjualan::where('created_at', 'LIKE', "%$tanggal%");

            $total_beli = 0;
            foreach ($pembelian->get() as $key => $beli) {
                $total_beli += $beli->harga;
            }

            $total_jual = 0;
            foreach ($penjualan->get() as $key => $jual) {
                $total_jual +=$jual->bayar;
            }

            $laba_kotor =  $total_jual - $total_beli;
            $total_laba_kotor += $laba_kotor;

            $row = array();
            $row['DT_RowIndex'] = $no++;
            $row['tanggal'] = tanggal_indonesia($tanggal, false);
            $row['pembelian'] = format_uang($total_beli);
            $row['penjualan'] = format_uang($total_jual);
            $row['laba_kotor'] = format_uang($laba_kotor);

            $data[] = $row;
        }

        $data[] = [
            'DT_RowIndex' => '',
            'tanggal' => 'Total',
            'pembelian' => '',
            'penjualan' => '',
            'laba_kotor' => format_uang($total_laba_kotor),

        ];

        return $data;
    }

    public function data($awal, $akhir)
    {
        $data = $this->getData($awal, $akhir);

        return datatables()
            ->of($data)
            ->make(true);
    }

    public function exportPDF($awal, $akhir)
    {
        $data = $this->getData($awal, $akhir);
        $pdf  = PDF::loadView('laporan.pdf', compact('awal', 'akhir', 'data'));
        $pdf->setPaper('a4', 'potrait');

        return $pdf->stream('Laporan-pendapatan-'. date('Y-m-d-his') .'.pdf');
    }
}
