@extends('layouts.app')

@section('title', 'Penilaian')

@push('styles')
    @vite('resources/halaman/penilaian/index.css')
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h3 mb-1 fw-bold text-primary">Pengaturan Aturan Klasifikasi Kehadiran</h2>
                        <p class="text-muted mb-0">Kelola 16 kombinasi aktivitas dan tentukan hasil statusnya</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-circle text-success me-1"></i>Ada
                            </span>
                            <span class="badge bg-light text-dark border ms-2">
                                <i class="fas fa-circle text-secondary me-1"></i>Tidak
                            </span>
                        </div>
                        <button class="btn btn-outline-primary-profesional" data-bs-toggle="modal" data-bs-target="#infoModal">
                            <i class="fas fa-info-circle me-1"></i> Panduan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @include('penilaian.partials.table')
    </div>

    @include('penilaian.partials.modal')
@endsection

@push('scripts')
    @vite('resources/halaman/penilaian/index.js')
@endpush