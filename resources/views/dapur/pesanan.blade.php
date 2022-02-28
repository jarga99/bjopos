@extends('layouts.master')

@section('title')
    Pesanan Terbaru
@endsection

@push('css')
    <style>
        .fw7 {
            font-weight: 700;
        }
        .fsi {
            font-style: italic;
        }
        .text-success {
            color: #28a745 !important;
        }
        .text-warning {
            color: #ffc107 !important;
        }
        .text-danger {
            color: #dc3545 !important;
        }
    </style>
@endpush

@section('breadcrumb')
    @parent
    <li class="active">Pesanan Terbaru</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered table-penjualan">
                    <thead>
                        <th width="5%">No</th>
                        <th>Tanggal</th>
                        <th>No Meja</th>
                        <th>Nama Customer</th>
                        {{-- <th>Kode Member</th> --}}
                        <th>Total Item</th>
                        {{-- <th>Total Harga</th> --}}
                        {{-- <th>Diskon</th> --}}
                        <th>Total Bayar</th>
                        <th>Status</th>
                        <th>Kasir</th>
                        <th width="15%"><i class="fa fa-cog"></i></th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('dapur.detail')
@endsection

@push('scripts')
<script>
    let table, table1;

    $(function () {
        table = $('.table-penjualan').DataTable({
            processing: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('pesanan.data') }}',
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'tanggal'},
                {data: 'nomor_meja'},
                {data: 'nama_customer'},
                // {data: 'kode_member'},
                {data: 'total_item'},
                // {data: 'total_harga'},
                // {data: 'diskon'},
                {data: 'bayar'},
                {data: 'status'},
                {data: 'kasir'},
                {data: 'aksi', searchable: false, sortable: false},
            ]
        });

        table1 = $('.table-detail').DataTable({
            processing: true,
            bSort: false,
            dom: 'Brt',
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'kode_produk'},
                {data: 'nama_produk'},
                {data: 'harga_jual'},
                {data: 'jumlah'},
                {data: 'subtotal'},
            ]
        })
    });

    function showDetail(url) {
        $('#modal-detail').modal('show');

        table1.ajax.url(url);
        table1.ajax.reload();
    }
</script>
@endpush