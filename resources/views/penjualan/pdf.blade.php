<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Penjualan</title>

    <link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
</head>
<body>
    <h3 class="text-center">Laporan Penjualan</h3>
    <h4 class="text-center">
        Tanggal {{ tanggal_indonesia($awal, false) }}
        s/d
        Tanggal {{ tanggal_indonesia($akhir, false) }}
    </h4>

    <table class="table table-striped">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Tanggal</th>
                <th>Nama Customer</th>
                <th>Total Item</th>
                <th>Total Bayar</th>
                <th>Kasir</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ tanggal_indonesia($item->created_at, false) }}</td>
                    <td>{{ $item->nama_customer }}</td>
                    <td>{{ format_uang($item->total_item) }}</td>
                    <td>{{ 'Rp. '. format_uang($item->total_harga) }}</td>
                    <td>{{ $item->user->name ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>