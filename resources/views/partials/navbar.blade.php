<nav class="navbar navbar-expand-lg">
    <div class="container-fluid container">
        <div>
            <a class="navbar-brand" href="{{ route('/') }}">
                <i class="fas fa-id-card-alt me-2"></i>Absensi RFID
            </a>
            
            <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
        
        <div class="d-flex w-100">
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="d-flex flex-row gap-3">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('/') ? 'active' : '' }}" 
                            aria-current="{{ request()->routeIs('/') ? 'page' : '' }}" 
                            href="{{ route('/') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('siswa*') ? 'active' : '' }}" 
                            href="{{ route('siswa') }}">Data Siswa</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('absensi*') ? 'active' : '' }}" 
                            href="{{ route('absensi') }}">Absensi</a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reservasi*') ? 'active' : '' }}" 
                            href="{{ route('reservasi') }}">Reservasi</a>
                        </li>
                        @admin
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('penilaian*') ? 'active' : '' }}" 
                            href="{{ route('penilaian') }}">Penilaian</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('scan*') ? 'active' : '' }}" 
                            href="{{ route('scan') }}">Scan Kartu</a>
                        </li>
                        @endadmin
                        <li class="nav-item">
                            <a class="nav-link" id="navMonitoring">Monitoring</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="navSetting">Setting</a>
                        </li>
                    </ul>

                    @include('partials.monitor-area')
                </div>
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="me-2">
                                <i class="fas fa-user-circle fa-lg"></i>
                            </div>
                            <div class="d-none d-sm-block">
                                <div class="text-white-50" style="font-size: 0.75rem;">
                                    @admin
                                        <span class="badge bg-success">Administrator</span>
                                    @else
                                        <span class="badge bg-info">Guru</span>
                                    @endadmin
                                </div>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li>
                                <form id="logoutForm" action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>