@extends('layouts.app')

@section('title', 'Tambah Siswa Baru')

@section('content')
<div class="row justify-content-center" data-halaman="REGISTRATION">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-lg border-0">
            <div class="card-header text-white" style="background:var(--dark)">
                <h4 class="mb-0">Formulir Tambah Siswa</h4>
            </div>
            <div class="card-body p-4">
                <form id="siswaForm" data-store-url="{{ route('siswa.store') }}">
                    @csrf 
                    
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Siswa</label>
                        <input type="text" class="form-control" id="nama" name="nama"  placeholder="Masukkan nama lengkap siswa">
                    </div>

                    <div class="mb-3">
                        <label for="kelas" class="form-label">Kelas</label>
                        <select class="form-select" id="kelas" name="kelas" >
                            <option value="" disabled selected>Pilih Kelas Siswa</option>
                            @foreach ($listKelas as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="no_ortu" class="form-label">Email Wali</label>
                        <input type="email" class="form-control" id="no_ortu" name="no_ortu"  placeholder="Contoh: info@gmail.com">
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_card" class="form-label">ID Kartu (RFID/NFC)</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="id_card" name="id_card" placeholder="Menunggu pemindaian kartu..." readonly>
                        </div>
                        <small class="form-text text-muted" id="scanStatus">
                            <i class="fas fa-spinner fa-spin me-2"></i> Sistem pemindaian kartu otomatis dimulai...
                        </small>
                    </div>

                    <div class="d-flex justify-content-between pt-2">
                        <a href="{{ route('siswa') }}" class="btn btn-secondary-profesional">Kembali ke Daftar</a>
                        <button type="submit" class="btn btn-primary-profesional" id="submitButton" disabled>
                            <i class="fas fa-save me-2"></i>Simpan Siswa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/halaman/siswa/create.js')
@endpush