@extends('layouts.app')

@section('title', 'Data Siswa')

@push('styles')
    @vite('resources/halaman/siswa/index.css')
@endpush

@section('content')

<div class="container-fluid py-4" data-halaman="STANDBY">
    @admin
        @include('siswa.partials.form-crud-kelas')
    @endadmin
    <div class="d-flex justify-content-between align-items-center mt-5 mb-4">
        <div>
            <h4 class="mb-1 text-dark">Data Siswa</h4>
            <p class="text-muted mb-0">Kelola informasi data siswa</p>
        </div>
        @admin
            <a href="{{ route('siswa.create') }}" class="btn btn-primary-profesional">
                <i class="fas fa-plus me-2"></i>Tambah Siswa
            </a>
        @endadmin
    </div>

    @include('siswa.partials.filter')

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="border-0 ps-4" style="width: 60px;">No</th>
                            <th class="border-0">Nama Siswa</th>
                            <th class="border-0">Kelas</th>
                            <th class="border-0 text-center">Hadir</th>
                            <th class="border-0 text-center">Alpa</th>
                            <th class="border-0 text-center">Izin</th>
                            <th class="border-0">Email Wali</th>
                            <th class="border-0 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="tbody">
                        @include('siswa.partials.table')
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-3">
        @include('siswa.partials.info-pagination')
    </div>
</div>

@endsection

@push('scripts')
    @vite('resources/halaman/siswa/index.js')
@endpush