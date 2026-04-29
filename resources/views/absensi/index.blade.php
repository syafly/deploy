@extends('layouts.app')

@section('title', 'Absensi Siswa')

@push('styles')
    @vite('resources/halaman/absensi/index.css')
@endpush

@section('content')

<div class="container-fluid py-4">
    
    <!-- Time Settings Card -->
    @admin
        @include('absensi.partials.time-setting')
    @endadmin
    
    @include('absensi.partials.filter') 

    <!-- Recap Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3 card-header-rekap"> {{-- ⬅️ TAMBAHKAN CLASS --}}
            @include('absensi.partials.header-rekap', compact('tanggalFilter', 'tampilkanTombolRekap', 'totalSiswaDifilter'))
        </div>
    </div>

    <!-- Table Section -->
    <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 ps-4" style="width: 5%">No</th>
                        <th class="border-0" style="width: 25%">Nama Siswa</th>
                        <th class="border-0 text-center" style="width: 12%">Masuk</th>
                        <th class="border-0 text-center" style="width: 12%">Istirahat</th>
                        <th class="border-0 text-center" style="width: 12%">Kembali</th>
                        <th class="border-0 text-center" style="width: 12%">Pulang</th>
                        <th class="border-0 text-center" style="width: 12%">Status</th>
                        <th class="border-0 text-center" style="width: 22%">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @include('absensi.partials.table')
                </tbody>
            </table>
        </div>
    </div>
</div>
    <!-- Info & Pagination Section -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        @include('absensi.partials.info-pagination')
    </div>
</div>

@endsection

@push('scripts')
    @vite('resources/halaman/absensi/index.js')
@endpush