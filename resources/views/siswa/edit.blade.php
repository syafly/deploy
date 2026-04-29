@extends('layouts.app')

@section('title', 'Edit Siswa')

@section('content')
    <div class="row justify-content-center" data-halaman="UPDATE">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">Formulir Edit Siswa: {{ $siswa->nama ?? 'Nama Siswa' }}</h4>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Form ID untuk JS dan Action Update -->
                    <form id="siswaFormEdit" action="{{ route('siswa.update', $siswa->id) }}" method="POST">
                        @csrf 
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Siswa</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nama" 
                                   name="nama" 
                                   value="{{ $siswa->nama ?? old('nama') }}"
                                   required 
                                   placeholder="Masukkan nama lengkap siswa">
                        </div>

                        <div class="mb-3">
                            <label for="no_orangtua" class="form-label">Email Wali</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="no_orangtua" 
                                   name="no_orangtua" 
                                   value="{{ $siswa->no_orangtua }}"
                                   required 
                                   placeholder="Masukkan email wali siswa">
                        </div>

                        <div class="mb-3">
                            <label for="id_kelas" class="form-label">Kelas</label>
                            <select class="form-select @error('id_kelas') is-invalid @enderror" 
                                    id="id_kelas" 
                                    name="id_kelas" 
                                    required>
                                <option value="">Pilih Kelas</option>
                                @foreach($kelas as $item)
                                    <option value="{{ $item->id }}" 
                                            {{ ($siswa->id_kelas == $item->id || old('id_kelas') == $item->id) ? 'selected' : '' }}>
                                        {{ $item->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="id_card" class="form-label">ID Kartu (RFID/NFC)</label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control @error('id_card') is-invalid @enderror" 
                                       id="id_card" 
                                       name="id_card" 
                                       value="{{ $siswa->id_card ?? old('id_card') }}"
                                       placeholder="ID Kartu saat ini, siap untuk diganti..."
                                       required
                                       readonly>
                            </div>
                            <small class="form-text text-muted" id="scanStatus">
                                <i class="fas fa-spinner fa-spin me-2"></i> Sistem pemindaian kartu otomatis dimulai...
                            </small>
                        </div>

                        <div class="d-flex justify-content-between pt-2">
                            <a href="{{ route('siswa') }}" class="btn btn-secondary" id="backToListLink">
                                Kembali ke Daftar
                            </a>
                            <button type="submit" class="btn btn-warning text-dark">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/halaman/siswa/update.js')
@endpush