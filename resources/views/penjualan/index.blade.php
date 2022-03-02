@extends('layouts.master')

@section('title')
    {{-- Daftar Penjualan {{ \Carbon\Carbon::parse($tanggalAwal)->format('d F Y') }} - {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d F Y') }} --}}
    Daftar Penjualan
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
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
    <li class="active">Daftar Penjualan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="updatePeriode()" class="btn btn-info btn-xs btn-flat"><i class="fa fa-plus-circle"></i> Ubah Periode</button>
                <a href="{{ route('penjualan.export_pdf') }}" target="_blank" class="btn btn-success btn-xs btn-flat" onclick="event.preventDefault();document.getElementById('export-penjualan-form').submit();">
                    <i class="fa fa-file-excel-o"></i> Export PDF
                </a>
                <form id="export-penjualan-form" action="{{ route('penjualan.export_pdf') }}" method="get" style="display: none;">
                    @csrf
                    <input type="hidden" name="form_awal" id="form_awal" value="{{ $tanggalAwal }}">
                    <input type="hidden" name="form_akhir" id="form_akhir" value="{{ $tanggalAkhir }}">
                </form>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered table-penjualan">
                    <thead>
                        <th width="5%">No</th>
                        <th>Tanggal</th>
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

@includeIf('penjualan.detail')
@includeIf('penjualan.form')
@endsection

@push('scripts')
<script src="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script>
    let table;
    $(document).ready(function() {
        table = $('.table-penjualan').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('penjualan.data') }}',
                data: function(d) {
                    d.tanggal_awal = $('#tanggal_awal').val();
                    d.tanggal_akhir = $('#tanggal_akhir').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'tanggal'},
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

        $('#btn-search').on('click', function(e) {
            const months = ["January", "February", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            var tanggal_awal = new Date($('#tanggal_awal').val()).getDate() + ' ' + months[new Date($('#tanggal_awal').val()).getMonth()] + ' ' + new Date($('#tanggal_awal').val()).getFullYear();
            var tanggal_akhir = new Date($('#tanggal_akhir').val()).getDate() + ' ' + months[new Date($('#tanggal_akhir').val()).getMonth()] + ' ' + new Date($('#tanggal_akhir').val()).getFullYear();
            var content_title = `Daftar Penjualan ` + tanggal_awal + ` - ` + tanggal_akhir;
            table.draw();
            e.preventDefault();
            $('#modal-form').modal("hide");
            $('#form_awal').val($('#tanggal_awal').val());
            $('#form_akhir').val($('#tanggal_akhir').val());
            $('#content-title').html(content_title);
        });
    });

    function deleteData(url) {
        if (confirm('Yakin ingin menghapus data terpilih?')) {
            $.post(url, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'delete'
                })
                .done((response) => {
                    table.ajax.reload();
                })
                .fail((errors) => {
                    alert('Tidak dapat menghapus data');
                    return;
                });
        }
    }
</script>
<script>
    let table1;

    $(function () {
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

    function updatePeriode() {
        $('#modal-form').modal('show');
    }
</script>
@endpush