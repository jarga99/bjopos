<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Penjualan</title>

    <link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <style>
        .detail td {
            font-style: italic;
            font-weight: 600;
        }
        .item td {
            border-top: 2px solid #333 !important;
            border-bottom: 2px solid #333 !important;
        }
    </style>
</head>
<body>
    <h3 class="text-center">Laporan Penjualan</h3>
    <h4 class="text-center">
        Tanggal {{ tanggal_indonesia($awal, false) }}
        s/d
        Tanggal {{ tanggal_indonesia($akhir, false) }}
    </h4>

    <table class="table table-striped">
        <tbody>
            <tr>
                <td>Jumlah Orderan</td>
                <td>: {{ $total_orderan }}</td>
            </tr>
            <tr>
                <td>Total Pemasukan Bersih</td>
                <td>: {{ 'Rp. '. format_uang($pemasukan_bersih) }}</td>
            </tr>
            <tr>
                <td>Total Pemasukan Kotor</td>
                <td>: {{ 'Rp. '. format_uang($pemasukan_kotor) }}</td>
            </tr>
            <tr>
                <td>Total Kembalian</td>
                <td>: {{ 'Rp. '. format_uang($total_kembali) }}</td>
            </tr>
            <tr>
                <td>Total Pembatalan Pesanan</td>
                <td>: {{ 'Rp. '. format_uang($total_pembatalan) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nama Customer</th>
                <th>Total Item</th>
                <th>Total Bayar</th>
                <th>Kasir</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($penjualan as $item)
                <tr class="item">
                    <td>{{ tanggal_indonesia($item->created_at, false) }}</td>
                    <td>{{ $item->nama_customer }}</td>
                    <td>{{ format_uang($item->total_item) }}</td>
                    <td>{{ 'Rp. '. format_uang($item->total_harga) }}</td>
                    <td>{{ $item->user->name ?? '' }}</td>
                </tr>
                @if ($item->detail)
                    <tr >
                        <td>No</td>
                        <td>Kode Produk</td>
                        <td>Nama Produk</td>
                        <td>Jumlah</td>
                        <td>Subtotal</td>
                    </tr>
                    @foreach ($item->detail as $detail)
                        <tr class="detail">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $detail->produk->kode_produk }}</td>
                            <td>{{ $detail->produk->nama_produk }}</td>
                            <td>{{ $detail->jumlah }}</td>
                            <td>{{ 'Rp. '. format_uang($detail->subtotal) }}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>
</body>
</html>