@extends('layouts.app')

@section('title', 'Reservasi Siswa')

@push('styles')
    @vite('resources/halaman/reservasi/index.css') 
@endpush

@section('content')
<div class="container-fluid py-3">
    
    @include('reservasi.partials.header')
    
    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('reservasi.store') }}" method="POST" id="reservasiForm">
                @csrf
                
                @include('reservasi.partials.input-form')
                @include('reservasi.partials.filter')
                
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 text-dark">Daftar Siswa</h6>
                                <small class="text-muted" id="selectedCount">Pilih siswa untuk memberikan status</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="checkAllSiswa">
                                    <label class="form-check-label small fw-medium" for="checkAllSiswa">
                                        Pilih Semua
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="row g-2 m-2" id="siswaContainer">
                            @include('reservasi.partials.students-grid', ['siswa' => $siswa])
                        </div>
                        
                        <div class="text-center mb-3 pt-3 border-top" id="submitSection" style="display: none;">
                            <button type="submit" class="btn btn-primary-profesional px-4">
                                <i class="fas fa-paper-plane me-2"></i>Simpan Status untuk <span id="submitCount">0</span> Siswa
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 text-dark">
                        <i class="fas fa-history text-primary me-2"></i>
                        Aktivitas Terbaru
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div id="activityList">
                        @include('reservasi.partials.recent-activity')
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-2">
                    <div class="text-center">
                        <small class="text-muted">{{ count($reservasi) }} aktivitas hari ini</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/halaman/reservasi/index.js')
@endpush