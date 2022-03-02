@extends('layouts.master')

@section('title')
    Daftar Pembelian
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endpush

@section('breadcrumb')
    @parent
    <li class="active">Daftar Pembelian</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="updatePeriode()" class="btn btn-info btn-xs btn-flat"><i class="fa fa-plus-circle"></i> Ubah Periode</button>
                <button onclick="addForm('{{route('pembelian.store')}}')" class="btn btn-success btn-xs btn-flat"><i class="fa fa-plus-circle"></i> Transaksi Baru</button>
                @empty(! session('id_pembelian'))
                <a href="{{ route('pembelian_detail.index') }}" class="btn btn-info btn-xs btn-flat"><i class="fa fa-pencil"></i> Transaksi Aktif</a>
                @endempty
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered table-pembelian">
                    <thead>
                        <th width="5%">No</th>
                        <th>Tanggal</th>
                        <th>Nama Bahan</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>User</th>
                        {{-- <th>Total Item</th>
                            <th>Total Harga</th>
                            <th>Diskon</th>
                            <th>Total Bayar</th> --}}
                        <th width="15%"><i class="fa fa-cog"></i></th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('pembelian.supplier')
@includeIf('pembelian.detail')
@includeIf('pembelian.form')
@endsection

@push('scripts')
<script src="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script>
    let table, table1;

    $(document).ready(function() {
        table = $('.table-pembelian').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('pembelian.data') }}',
                data: function(d) {
                    d.tanggal_awal = $('#tanggal_awal').val();
                    d.tanggal_akhir = $('#tanggal_akhir').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'tanggal'},
                {data: 'nama_bahan'},
                {data: 'jumlah'},
                {data: 'satuan'},
                {data: 'harga'},
                {data: 'user'},
                // {data: 'bayar'},
                {data: 'aksi', searchable: false, sortable: false},
            ]
        });

        $('#btn-search').on('click', function(e) {
            const months = ["January", "February", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            var tanggal_awal = new Date($('#tanggal_awal').val()).getDate() + ' ' + months[new Date($('#tanggal_awal').val()).getMonth()] + ' ' + new Date($('#tanggal_awal').val()).getFullYear();
            var tanggal_akhir = new Date($('#tanggal_akhir').val()).getDate() + ' ' + months[new Date($('#tanggal_akhir').val()).getMonth()] + ' ' + new Date($('#tanggal_akhir').val()).getFullYear();
            var content_title = `Daftar Pembelian ` + tanggal_awal + ` - ` + tanggal_akhir;
            table.draw();
            e.preventDefault();
            $('#modal-form').modal("hide");
            $('#content-title').html(content_title);
        });
    });

    $(function () {
        $('#modal-supplier').validator().on('submit', function (e) {
            if (! e.preventDefault()) {
                $.post($('#modal-supplier form').attr('action'), $('#modal-supplier form').serialize())
                    .done((response) => {
                        $('#modal-supplier').modal('hide');
                        table.ajax.reload();
                    })
                    .fail((errors) => {
                        alert('Tidak dapat menyimpan data');
                        return;
                    });
            }
        });

        $('.table-supplier').DataTable();
        table1 = $('.table-detail').DataTable({
            processing: true,
            bSort: false,
            dom: 'Brt',
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                // {data: 'kode_produk'},
                {data: 'nama_bahan'},
                // {data: 'harga_beli'},
                {data: 'jumlah'},
                {data: 'subtotal'},
            ]
        })
    });

    function addForm(url) {
        $('#modal-supplier').modal('show');
        $('#modal-supplier .modal-title').text('Tambah Pembelian');

        $('#modal-supplier form')[0].reset();
        $('#modal-supplier form').attr('action', url);
        $('#modal-supplier [name=_method]').val('post');
    }

    function editForm(url) {
        $('#modal-supplier').modal('show');
        $('#modal-supplier .modal-title').text('Edit Pembelian');

        $('#modal-supplier form')[0].reset();
        $('#modal-supplier form').attr('action', url);
        $('#modal-supplier [name=_method]').val('put');

        $.get(url)
            .done((response) => {
                $('#modal-supplier [name=nama_bahan]').val(response.nama_bahan);
                $('#modal-supplier [name=satuan]').val(response.satuan);
                $('#modal-supplier [name=harga]').val(response.harga);
                $('#modal-supplier [name=jumlah]').val(response.jumlah);
            })
            .fail((errors) => {
                alert('Tidak dapat menampilkan data');
                return;
            });
    }

    // function showDetail(url) {
    //     $('#modal-detail').modal('show');

    //     table1.ajax.url(url);
    //     table1.ajax.reload();
    // }

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

    function updatePeriode() {
        $('#modal-form').modal('show');
    }
</script>
@endpush