@extends('layouts.app')

@section('title', 'Area Scan Kartu')

@push('styles')
    @vite('resources/halaman/scan/index.css')
@endpush

@section('content')

<div class="row justify-content-center" data-halaman="LOGIN">
    <div class="col-md-8 col-lg-6">
        <h2 class="text-center mb-4 text-primary fw-bold">
            <i class="fas fa-fingerprint me-2"></i> Area Scan Kartu
        </h2>

        <div class="scanner-container">
            <p class="h5 text-white mb-4">Detail Scan</p>
            
            <div class="scanner-area" id="scannerArea">
                <div class="scan-line"></div>
                <i class="fas fa-id-card rfid-tag" id="rfidIcon"></i>
            </div>

            <div class="scan-status h4" id="scanStatus">
                <i class="fas fa-spinner fa-spin me-2"></i> Menunggu Kartu...
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite('resources/halaman/scan/index.js')
@endpush