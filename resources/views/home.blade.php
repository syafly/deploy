@extends('layouts.app')

@section('title', 'Halaman Utama Sistem')

@push('styles')
    @vite('resources/halaman/home/index.css')
@endpush

@section('content')

<!-- Hero Section -->
<section class="card hero-section">
    <div class="">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-shield-alt me-2"></i>RFID Absensi System
            </div>
            <h1 class="hero-title">Techno Creative Solusindo</h1>
            <p class="hero-subtitle">
                Dilengkapi dengan fitur sistem manajemen informasi, untuk
                membantu guru mengelola data terkait kehadiran siswa
            </p>
            <div class="hero-actions">
                <a href="{{ route('scan') }}" class="btn btn-hero-primary">
                    <i class="fas fa-rss me-2"></i>Mulai dengan Scan
                </a>
                <a href="{{ route('absensi') }}" class="btn btn-hero-secondary">
                    <i class="fas fa-chart-bar me-2"></i>Analisis
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<div class=" stats-section">
    <div class="stats-grid">
        <div class="stat-card-modern">
            <div class="stat-icon total">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-number-modern">{{ $data['stats']['total'] ?? 0 }}</div>
            <div class="stat-label-modern">Total Students</div>
        </div>
        
        <div class="stat-card-modern">
            <div class="stat-icon present">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-number-modern">{{ $data['stats']['present'] ?? 0 }}</div>
            <div class="stat-label-modern">Present Today</div>
        </div>
        
        <div class="stat-card-modern">
            <div class="stat-icon absent">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-number-modern">{{ $data['stats']['absent'] ?? 0 }}</div>
            <div class="stat-label-modern">Absent Today</div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite('resources/halaman/home/index.js')
@endpush
