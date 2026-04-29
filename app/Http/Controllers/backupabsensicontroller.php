<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\TransaksiAbsen;
use App\Models\Reservasi;
use App\Models\WaktuAbsen;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    public function rekap(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date', 
            'kelas' => 'nullable|exists:kelas,id',
            'search' => 'nullable|string'
        ]);
        
        $tanggalFilter = $request->input('tanggal');
        $kelasFilter = $request->input('kelas');
        $search = $request->input('search', '');
        $dataToSave = [];

        $attendanceRules = DB::table('attendance_rules')->get();
        $waktuAbsen = DB::table('waktu_absen')->get()->keyBy('status');

        $siswaList = Siswa::with([
            'reservasi' => function($query) use ($tanggalFilter) {
                $query->whereDate('waktu_mulai', '<=', $tanggalFilter)
                      ->whereDate('waktu_akhir', '>=', $tanggalFilter)
                      ->orderBy('waktu_mulai');
            },
            'transaksi_absen' => function($query) use ($tanggalFilter) {
                $query->whereDate('waktu_tap', $tanggalFilter);
            },
            'absensi' => function($query) use ($tanggalFilter) {
                $query->whereDate('tanggal', $tanggalFilter);
            }
        ])
        ->when($kelasFilter, fn ($q) => $q->where('id_kelas', $kelasFilter))
        ->when($search, function($q) use ($search) {
            $q->where(function($query) use ($search) {
                $query->where('id', 'LIKE', "%{$search}%")
                      ->orWhere('nama', 'LIKE', "%{$search}%");
            });
        })
        ->get();

        $totalSiswaDifilter = $siswaList->count();

        $reservasiIdsToDelete = [];

        foreach ($siswaList as $siswa) {
            $statusTersimpan = $siswa->absensi->first()->status ?? null;
            
            if ($statusTersimpan && $statusTersimpan !== '-') {
                continue;
            }

            if ($siswa->reservasi->isNotEmpty()) {
                $statusFinal = $this->prosesDenganReservasi($siswa, $attendanceRules, $waktuAbsen, $tanggalFilter);
                
                foreach ($siswa->reservasi as $reservasi) {
                    $waktuAkhir = Carbon::parse($reservasi->waktu_akhir);
                    if ($waktuAkhir->toDateString() === $tanggalFilter) {
                        $reservasiIdsToDelete[] = $reservasi->id;
                    }
                }
            } else {
                $statusFinal = $this->prosesTanpaReservasi($siswa, $attendanceRules, $waktuAbsen, $tanggalFilter);
            }

            $dataToSave[] = [
                'id_siswa' => $siswa->id,
                'tanggal' => $tanggalFilter,
                'keterangan' => $statusFinal['keterangan'],
                'status' => $statusFinal['status'],
            ];
        }

        DB::beginTransaction();
        try {
            if (!empty($dataToSave)) {
                Absensi::upsert($dataToSave, ['id_siswa', 'tanggal'], ['keterangan', 'status']);
            }
            
            if (!empty($reservasiIdsToDelete)) {
                $reservasiIdsToDelete = array_unique($reservasiIdsToDelete);
                
                DB::table('reservasis')->whereIn('id', $reservasiIdsToDelete)->delete();
            }
            
            DB::commit();
            
            $tampilkanTombolRekap = false;

            return response()->json([
                'message' => 'Rekapitulasi berhasil!',
                'success' => true,
                'headerRekap' => view('absensi.partials.header-rekap', compact('tanggalFilter', 'tampilkanTombolRekap', 'totalSiswaDifilter'))->render(),
                'data_disimpan' => count($dataToSave),
                'reservasi_dihapus' => count($reservasiIdsToDelete ?? [])
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Rekapitulasi gagal: ' . $e->getMessage()], 500);
        }
    }

    private function formatKeteranganDenganTapDanReservasi($sessionsCovered, $reservasiList, $sessionActualTap, $isReservasiMultiHari, $siswa, $waktuAbsen, $tanggalFilter)
    {
        $keteranganParts = [];
        
        // 1. KETERANGAN UNTUK SESSION YANG HANYA RESERVASI (TANPA TAP)
        $sessionHanyaReservasi = array_diff($sessionsCovered, array_keys(array_filter($sessionActualTap)));
        
        if (!empty($sessionHanyaReservasi)) {
            $sessionGroups = $this->groupSessions($sessionHanyaReservasi);
            $sessionText = '';
            
            foreach ($sessionGroups as $group) {
                if (!empty($sessionText)) $sessionText .= ', ';
                $sessionText .= count($group) > 1 ? 
                    $group[0] . ' - ' . end($group) : 
                    $group[0];
            }
            
            $statusIzin = $isReservasiMultiHari ? '(dalam izin)' : '(tanpa kabar)';
            $keteranganParts[] = 'izin: ' . $sessionText . $statusIzin;
        }
        
        // 2. KETERANGAN UNTUK TAP YANG SUDAH DILAKUKAN
        $keteranganTapParts = [];
        foreach (['masuk', 'istirahat', 'kembali_istirahat', 'pulang'] as $status) {
            if ($sessionActualTap[$status]) {
                $transaksi = $siswa->transaksi_absen->where('status', $status)->first();
                if ($transaksi && isset($waktuAbsen[$status])) {
                    $waktuTap = $transaksi->waktu_tap;
                    
                    // CEK APAKAH TAP DALAM RESERVASI
                    $isDalamReservasi = false;
                    $reservasiTerpakai = null;
                    $waktuTapTime = Carbon::parse($waktuTap);
                    
                    foreach ($reservasiList as $reservasi) {
                        $waktuMulaiReservasi = Carbon::parse($reservasi->waktu_mulai);
                        $waktuAkhirReservasi = Carbon::parse($reservasi->waktu_akhir);
                        
                        if ($waktuTapTime->between($waktuMulaiReservasi, $waktuAkhirReservasi)) {
                            $isDalamReservasi = true;
                            $reservasiTerpakai = $reservasi;
                            break;
                        }
                    }
                    
                    // HITUNG KETERLAMBATAN DENGAN BATAS YANG BENAR
                    $menitTerlambat = $this->hitungKeterlambatan(
                        $waktuTap, 
                        $waktuAbsen[$status]->to,
                        $reservasiList,
                        $status
                    );
                    
                    // FORMAT KETERANGAN
                    if ($isDalamReservasi) {
                        if ($menitTerlambat > 0) {
                            $keteranganTapParts[] = $status . '(terlambat ' . $menitTerlambat . 'm dari reservasi)';
                        } else {
                            $keteranganTapParts[] = $status . '(tepat waktu dalam reservasi)';
                        }
                    } else {
                        if ($menitTerlambat > 0) {
                            $keteranganTapParts[] = $status . '(' . $menitTerlambat . 'm)';
                        } else {
                            $keteranganTapParts[] = $status . '(tepat waktu)';
                        }
                    }
                }
            }
        }
        
        if (!empty($keteranganTapParts)) {
            $keteranganParts[] = implode(', ', $keteranganTapParts);
        }
        
        // 3. TAMBAHKAN KETERANGAN RESERVASI
        $keteranganReservasiParts = [];
        foreach ($reservasiList as $reservasi) {
            if (!empty($reservasi->keterangan)) {
                $keteranganReservasiParts[] = $reservasi->keterangan;
            }
        }
        
        $keteranganFinal = implode('; ', $keteranganParts);
        
        if (!empty($keteranganReservasiParts)) {
            $keteranganFinal .= ' - ' . implode(', ', array_unique($keteranganReservasiParts));
        }
        
        return $keteranganFinal;
    }

    private function formatKeteranganUntukReservasiTanpaTap($sessions, $reservasiList, $isCoverSemuaSession, $waktuAbsen, $tanggalFilter, $isReservasiMultiHari = false)
    {
        if (empty($sessions)) {
            return '-';
        }
        
        $sessionGroups = $this->groupSessionsByReservasi($sessions, $reservasiList, $waktuAbsen, $tanggalFilter);
        
        $keteranganParts = [];
        
        foreach ($sessionGroups as $group) {
            $sessionText = count($group) > 1 ? 
                $group[0] . ' - ' . end($group) : 
                $group[0];
                
            // TENTUKAN STATUS PER GROUP
            if ($isReservasiMultiHari) {
                // MULTI-HARI: (dalam izin)
                $statusText = '(dalam izin)';
            } else {
                // HARI INI + TANPA TAP: (tanpa kabar)
                $statusText = '(tanpa kabar)';
            }
            
            $keteranganParts[] = $sessionText . $statusText;
        }
        
        $keterangan = 'izin: ' . implode(', ', $keteranganParts);
        
        // TAMBAHKAN KETERANGAN RESERVASI
        $keteranganReservasiParts = [];
        foreach ($reservasiList as $reservasi) {
            if (!empty($reservasi->keterangan)) {
                $keteranganReservasiParts[] = $reservasi->keterangan;
            }
        }
        
        if (!empty($keteranganReservasiParts)) {
            $keterangan .= ' - ' . implode(', ', array_unique($keteranganReservasiParts));
        }
        
        return $keterangan;
    }

    private function isReservasiCoverSemuaSession($reservasiList, $waktuAbsen, $tanggalFilter)
    {
        $coveredSessions = [];
        
        foreach ($reservasiList as $reservasi) {
            $sessionReservasi = $this->tentukanSessionReservasi($reservasi, $waktuAbsen, $tanggalFilter);
            $coveredSessions = array_merge($coveredSessions, $sessionReservasi['sessions']);
        }
        
        $coveredSessions = array_unique($coveredSessions);
        
        // Cek apakah cover semua 4 session
        return count($coveredSessions) === 4; // masuk, istirahat, kembali, pulang
    }

    private function isReservasiMultiHari($reservasiList, $tanggalFilter)
    {
        foreach ($reservasiList as $reservasi) {
            $waktuMulai = Carbon::parse($reservasi->waktu_mulai);
            $waktuAkhir = Carbon::parse($reservasi->waktu_akhir);
            
            // Jika ada reservasi yang melewati tanggal filter = multi-hari
            if ($waktuAkhir->toDateString() > $tanggalFilter) {
                return true;
            }
        }
        return false;
    }

    private function tentukanStatusBerdasarkanRules($sessionFinal, $attendanceRules)
    {
        // Konversi ke format rules
        $masuk = $sessionFinal['masuk'] ? 1 : 0;
        $istirahat = $sessionFinal['istirahat'] ? 1 : 0;
        $kembali_istirahat = $sessionFinal['kembali_istirahat'] ? 1 : 0;
        $pulang = $sessionFinal['pulang'] ? 1 : 0;

        // Cari rule yang match
        $matchedRule = $attendanceRules->first(function ($rule) use ($masuk, $istirahat, $kembali_istirahat, $pulang) {
            return $rule->masuk == $masuk &&
                   $rule->istirahat == $istirahat &&
                   $rule->kembali_istirahat == $kembali_istirahat &&
                   $rule->pulang == $pulang;
        });

        if ($matchedRule) {
            return [
                'status' => $matchedRule->status_output,
                'keterangan' => '-'
            ];
        }

        return [
            'status' => 'alpa',
            'keterangan' => '-'
        ];
    }

    private function tentukanSessionReservasi($reservasi, $waktuAbsen, $tanggalFilter)
    {
        $sessions = [];
        $sessionKeterlambatan = [];
        
        $waktuMulai = Carbon::parse($reservasi->waktu_mulai);
        $waktuAkhir = Carbon::parse($reservasi->waktu_akhir);
        
        // Cek coverage untuk setiap session absensi
        $sessionTypes = ['masuk', 'istirahat', 'kembali_istirahat', 'pulang'];
        
        foreach ($sessionTypes as $session) {
            if (!isset($waktuAbsen[$session])) continue;
            
            $batasMulai = Carbon::parse($tanggalFilter . ' ' . $waktuAbsen[$session]->from);
            $batasSelesai = Carbon::parse($tanggalFilter . ' ' . $waktuAbsen[$session]->to);
            
            // Cek jika reservasi overlap dengan session ini
            if ($waktuMulai->lessThanOrEqualTo($batasSelesai) && $waktuAkhir->greaterThanOrEqualTo($batasMulai)) {
                $sessions[] = $session;
                
                // HITUNG KETERLAMBATAN BERDASARKAN WAKTU_MULAI RESERVASI
                // Jika waktu_mulai reservasi > batas selesai session = TERLAMBAT
                if ($waktuMulai->greaterThan($batasSelesai)) {
                    $menitTerlambat = $waktuMulai->diffInMinutes($batasSelesai);
                    $sessionKeterlambatan[$session] = [
                        'terlambat' => true,
                        'menit' => $menitTerlambat
                    ];
                } else {
                    // JIKA TIDAK TERLAMBAT, TAPI JUGA TIDAK BISA DIKATAKAN "TEPAT WAKTU"
                    // karena tidak ada bukti tap. Jadi statusnya "dalam izin"
                    $sessionKeterlambatan[$session] = [
                        'terlambat' => false,
                        'menit' => 0,
                        'dalam_izin' => true // Flag khusus untuk izin tanpa tap
                    ];
                }
            }
        }
        
        return [
            'sessions' => $sessions,
            'keterlambatan' => $sessionKeterlambatan,
            'keterangan_reservasi' => $reservasi->keterangan
        ];
    }

    private function groupSessionsByReservasi($sessions, $reservasiList, $waktuAbsen, $tanggalFilter)
    {
        // BUAT GROUP BERDASARKAN RESERVASI, BUKAN BERDASARKAN URUTAN
        $reservasiGroups = [];
        
        foreach ($reservasiList as $reservasi) {
            $sessionReservasi = $this->tentukanSessionReservasi($reservasi, $waktuAbsen, $tanggalFilter);
            $reservasiSessions = array_intersect($sessionReservasi['sessions'], $sessions);
            
            if (!empty($reservasiSessions)) {
                // Group session dalam reservasi ini
                $grouped = $this->groupSessions($reservasiSessions);
                $reservasiGroups = array_merge($reservasiGroups, $grouped);
            }
        }
        
        return $reservasiGroups;
    }

    private function groupSessions($sessions)
    {
        $sessionOrder = ['masuk', 'istirahat', 'kembali_istirahat', 'pulang'];
        $groups = [];
        $currentGroup = [];
        
        foreach ($sessionOrder as $session) {
            if (in_array($session, $sessions)) {
                $currentGroup[] = $session;
            } else {
                if (!empty($currentGroup)) {
                    $groups[] = $currentGroup;
                    $currentGroup = [];
                }
            }
        }
        
        if (!empty($currentGroup)) {
            $groups[] = $currentGroup;
        }
        
        return $groups;
    }

    private function prosesTanpaReservasi($siswa, $attendanceRules, $waktuAbsen, $tanggalFilter)
    {
        // Mapping data tap aktual
        $siswaData = [
            'id' => $siswa->id,
            'masuk' => $siswa->transaksi_absen->where('status', 'masuk')->isNotEmpty(),
            'istirahat' => $siswa->transaksi_absen->where('status', 'istirahat')->isNotEmpty(),
            'kembali_istirahat' => $siswa->transaksi_absen->where('status', 'kembali_istirahat')->isNotEmpty(),
            'pulang' => $siswa->transaksi_absen->where('status', 'pulang')->isNotEmpty(),
        ];

        // Konversi ke format rules
        $masuk = $siswaData['masuk'] ? 1 : 0;
        $istirahat = $siswaData['istirahat'] ? 1 : 0;
        $kembali_istirahat = $siswaData['kembali_istirahat'] ? 1 : 0;
        $pulang = $siswaData['pulang'] ? 1 : 0;

        // Cari rule yang match
        $matchedRule = $attendanceRules->first(function ($rule) use ($masuk, $istirahat, $kembali_istirahat, $pulang) {
            return $rule->masuk == $masuk &&
                   $rule->istirahat == $istirahat &&
                   $rule->kembali_istirahat == $kembali_istirahat &&
                   $rule->pulang == $pulang;
        });

        if ($matchedRule) {
            if ($matchedRule->status_output === 'alpa') {
                return [
                    'status' => 'alpa',
                    'keterangan' => '-'
                ];
            } else {
                return $this->checkKeterlambatanNormal($siswaData, $waktuAbsen, $tanggalFilter);
            }
        }

        return [
            'status' => 'alpa',
            'keterangan' => '-'
        ];
    }    

    private function prosesDenganReservasi($siswa, $attendanceRules, $waktuAbsen, $tanggalFilter)
    {
        $reservasiList = $siswa->reservasi;
        
        // 1. MAPPING SESSION YANG DI-COVER OLEH RESERVASI
        $sessionCoveredByReservasi = [
            'masuk' => false, 'istirahat' => false, 'kembali_istirahat' => false, 'pulang' => false
        ];

        foreach ($reservasiList as $reservasi) {
            $sessionReservasi = $this->tentukanSessionReservasi($reservasi, $waktuAbsen, $tanggalFilter);
            foreach ($sessionReservasi['sessions'] as $session) {
                $sessionCoveredByReservasi[$session] = true;
            }
        }

        // 2. TAP AKTUAL (TANPA RESERVASI)
        $sessionActualTap = [
            'masuk' => $siswa->transaksi_absen->where('status', 'masuk')->isNotEmpty(),
            'istirahat' => $siswa->transaksi_absen->where('status', 'istirahat')->isNotEmpty(),
            'kembali_istirahat' => $siswa->transaksi_absen->where('status', 'kembali_istirahat')->isNotEmpty(),
            'pulang' => $siswa->transaksi_absen->where('status', 'pulang')->isNotEmpty(),
        ];

        // 3. PERBAIKAN: SESSION FINAL = TAP AKTUAL ATAU RESERVASI
        $sessionFinalForRules = [
            'masuk' => $sessionActualTap['masuk'] || $sessionCoveredByReservasi['masuk'],
            'istirahat' => $sessionActualTap['istirahat'] || $sessionCoveredByReservasi['istirahat'],
            'kembali_istirahat' => $sessionActualTap['kembali_istirahat'] || $sessionCoveredByReservasi['kembali_istirahat'],
            'pulang' => $sessionActualTap['pulang'] || $sessionCoveredByReservasi['pulang'],
        ];

        // 4. CEK KONDISI LAIN
        $isCoverSemuaSession = $this->isReservasiCoverSemuaSession($reservasiList, $waktuAbsen, $tanggalFilter);
        $isReservasiMultiHari = $this->isReservasiMultiHari($reservasiList, $tanggalFilter);
        $totalTap = $siswa->transaksi_absen->count();

        // KASUS: TIDAK ADA TAP SAMA SEKALI
        if ($totalTap === 0 && !$isReservasiMultiHari) {
            if ($isCoverSemuaSession) {
                return [
                    'status' => 'izin',
                    'keterangan' => 'izin: masuk-pulang(tanpa kabar)'
                ];
            } else {
                $statusDariRules = $this->tentukanStatusBerdasarkanRules($sessionFinalForRules, $attendanceRules);
                $sessionsCovered = array_keys(array_filter($sessionCoveredByReservasi));
                
                $keteranganIzin = $this->formatKeteranganUntukReservasiTanpaTap(
                    $sessionsCovered, $reservasiList, $isCoverSemuaSession,
                    $waktuAbsen, $tanggalFilter, $isReservasiMultiHari
                );
                
                return [
                    'status' => $statusDariRules['status'],
                    'keterangan' => $keteranganIzin
                ];
            }
        }

        // PROSES NORMAL UNTUK KASUS ADA TAP
        $statusDariRules = $this->tentukanStatusBerdasarkanRules($sessionFinalForRules, $attendanceRules);
        
        // TENTUKAN STATUS FINAL
        if ($isReservasiMultiHari) {
            $statusFinal = 'izin';
        } else {
            $statusFinal = $statusDariRules['status'];
        }

        // FORMAT KETERANGAN YANG BENAR
        $sessionsCovered = array_keys(array_filter($sessionCoveredByReservasi));
        $keteranganFinal = $this->formatKeteranganDenganTapDanReservasi(
            $sessionsCovered, 
            $reservasiList, 
            $sessionActualTap, // Untuk keterangan, tetap pakai tap aktual
            $isReservasiMultiHari,
            $siswa,
            $waktuAbsen,
            $tanggalFilter
        );

        return [
            'status' => $statusFinal,
            'keterangan' => $keteranganFinal
        ];
    }

    private function checkKeterlambatanNormal(array $siswa, $waktuAbsen, $tanggalFilter): array
    {
        $keterlambatan = [];
        
        // Cek keterlambatan untuk setiap status
        $statusTypes = ['masuk', 'istirahat', 'kembali_istirahat', 'pulang'];
        
        foreach ($statusTypes as $status) {
            if ($siswa[$status] && isset($waktuAbsen[$status])) {
                $waktuTap = $this->getWaktuTapByStatus($siswa['id'], $status, $tanggalFilter);
                
                if ($waktuTap) {
                    $menitTerlambat = $this->hitungKeterlambatan(
                        $waktuTap, 
                        $waktuAbsen[$status]->to
                    );
                    
                    if ($menitTerlambat > 0) {
                        $keterlambatan[] = $status . '(' . $menitTerlambat . ' menit)';
                    }
                }
            }
        }
        
        if (!empty($keterlambatan)) {
            return [
                'status' => 'masuk',
                'keterangan' => implode(', ', $keterlambatan)
            ];
        } else {
            return [
                'status' => 'masuk',
                'keterangan' => '-'
            ];
        }
    }

    private function getWaktuTapByStatus($idSiswa, $status, $tanggalFilter)
    {
        // Query untuk mendapatkan waktu tap berdasarkan status
        $transaksi = DB::table('transaksi_absens')
            ->where('id_siswa', $idSiswa)
            ->where('status', $status)
            ->whereDate('waktu_tap', $tanggalFilter)
            ->first();
        
        return $transaksi ? $transaksi->waktu_tap : null;
    }

    private function hitungKeterlambatan($waktuTap, $batasWaktuNormal, $reservasiList = null, $status = null)
    {
        try {
            $waktuTapTime = Carbon::parse($waktuTap);
            
            // TENTUKAN BATAS WAKTU YANG BENAR
            $batasWaktuAkhir = Carbon::parse($batasWaktuNormal);
            
            // JIKA ADA RESERVASI UNTUK STATUS INI, GUNAKAN WAKTU_AKHIR RESERVASI SEBAGAI BATAS
            if ($reservasiList && $status) {
                foreach ($reservasiList as $reservasi) {
                    $waktuMulaiReservasi = Carbon::parse($reservasi->waktu_mulai);
                    $waktuAkhirReservasi = Carbon::parse($reservasi->waktu_akhir);
                    
                    // PERBAIKAN: CEK APAKAH RESERVASI INI COVER SESSION YANG DIMAKSUD
                    $sessionReservasi = $this->tentukanSessionReservasi($reservasi, DB::table('waktu_absen')->get()->keyBy('status'), $waktuTapTime->toDateString());
                    
                    if (in_array($status, $sessionReservasi['sessions'])) {
                        // RESERVASI INI COVER SESSION YANG DIMAKSUD, GUNAKAN BATAS RESERVASI
                        $batasWaktuAkhir = $waktuAkhirReservasi;
                        \Log::info("Menggunakan batas reservasi untuk $status: " . $batasWaktuAkhir->format('H:i:s'));
                        break;
                    }
                }
            }
            
            
            // Hitung keterlambatan terhadap batas waktu yang benar
            if ($waktuTapTime->greaterThan($batasWaktuAkhir)) {
                return $waktuTapTime->diffInMinutes($batasWaktuAkhir);
            }
            
            return 0;
        } catch (\Exception $e) {
            \Log::error("ERROR hitungKeterlambatan: " . $e->getMessage());
            return 0;
        }
    }

    public function absensi(Request $request)
    {
        $tanggalFilter = $request->input('tanggal', Carbon::today()->toDateString());
        $kelasFilter = $request->input('kelas');
        $searchFilter = $request->input('search');
        
        $siswaQuery = Siswa::with([
            'kelas',
            'absensi' => function($query) use ($tanggalFilter) {
                $query->whereDate('tanggal', $tanggalFilter);
            },
            'transaksi_absen' => function($query) use ($tanggalFilter) {
                $query->whereDate('waktu_tap', $tanggalFilter);
            }
        ])
        ->when($kelasFilter, fn ($query) => $query->where('id_kelas', $kelasFilter))
        ->when($searchFilter, function($query) use ($searchFilter) {
            $query->where(function($q) use ($searchFilter) {
                $q->where('nama', 'like', "%{$searchFilter}%")
                  ->orWhere('id_card', 'like', "%{$searchFilter}%");
            });
        })
        ->orderBy('nama');

        $totalSiswaDifilter = $siswaQuery->count();
        $absensi_list = $siswaQuery->paginate(10);

        $absensi_list->getCollection()->transform(function ($siswa) {
            $status = $siswa->absensi->first()->status ?? '-';
            
            $masuk = $siswa->transaksi_absen->where('status', 'masuk')->first();
            $istirahat = $siswa->transaksi_absen->where('status', 'istirahat')->first();
            $kembali = $siswa->transaksi_absen->where('status', 'kembali')->first();
            $pulang = $siswa->transaksi_absen->where('status', 'pulang')->first();

            return (object) [
                'id' => $siswa->id,
                'nama' => $siswa->nama,
                'kelas' => $siswa->kelas->nama_kelas ?? '-',
                'status' => $status,
                'keterangan' => $siswa->absensi->first()->keterangan ?? '-',
                'masuk' => $masuk ? Carbon::parse($masuk->waktu_tap)->format('H:i') : null,
                'istirahat' => $istirahat ? Carbon::parse($istirahat->waktu_tap)->format('H:i') : null,
                'kembali' => $kembali ? Carbon::parse($kembali->waktu_tap)->format('H:i') : null,
                'pulang' => $pulang ? Carbon::parse($pulang->waktu_tap)->format('H:i') : null,
            ];
        });

        $belumDirekapCount = Siswa::when($kelasFilter, fn ($query) => $query->where('id_kelas', $kelasFilter))
            ->when($searchFilter, function($query) use ($searchFilter) {
                $query->where(function($q) use ($searchFilter) {
                    $q->where('nama', 'like', "%{$searchFilter}%")
                      ->orWhere('id_card', 'like', "%{$searchFilter}%");
                });
            })
            ->whereDoesntHave('absensi', function($query) use ($tanggalFilter) {
                $query->whereDate('tanggal', $tanggalFilter)
                      ->where('status', '!=', '-');
            })
            ->count();

        $tampilkanTombolRekap = $belumDirekapCount > 0;

        $listKelas = Kelas::orderBy('nama_kelas')->get(['id', 'nama_kelas']);
        $waktuAbsen = WaktuAbsen::all()->keyBy('status');
        
        if ($request->has('_ajax') || $request->wantsJson()) {
            return response()->json([
                'html' => view('absensi.partials.table', compact('absensi_list'))->render(),
                'infoPagination' => view('absensi.partials.info-pagination', compact('absensi_list'))->render(),
                'activeFilters' => view('absensi.partials.active-filters', compact(
                    'tanggalFilter', 
                    'kelasFilter', 
                    'searchFilter', 
                    'listKelas'
                ))->render(),
                'headerRekap' => view('absensi.partials.header-rekap', compact(
                    'tanggalFilter',
                    'tampilkanTombolRekap', 
                    'totalSiswaDifilter'
                ))->render(),
                'success' => true
            ]);
        }
        return view('absensi/index', compact(
            'absensi_list',
            'tanggalFilter',
            'listKelas',
            'waktuAbsen',
            'tampilkanTombolRekap',
            'totalSiswaDifilter'
        ));
    }

    public function helperTerlambat($scanTime, $reservasiAktif = null)
    {
        $date = $scanTime->toDateString();
        $waktuAbsenDB = WaktuAbsen::all()->keyBy('status');

        $sesi = [
            'masuk'    => $waktuAbsenDB->get('masuk'),
            'istirahat' => $waktuAbsenDB->get('istirahat'),
            'kembali'   => $waktuAbsenDB->get('kembali_istirahat'),
            'pulang'    => $waktuAbsenDB->get('pulang'),
        ];

        $actionType = null;
        $keterangan = null;

        if ($scanTime->between(Carbon::parse("$date " . $sesi['masuk']->from), Carbon::parse("$date " . $sesi['istirahat']->from), true)) {
            $actionType = 'masuk';
            $batasNormal = Carbon::parse("$date " . $sesi['masuk']->to);
            $this->hitungKeterlambatann($scanTime, $batasNormal, $reservasiAktif, $keterangan);
        } 

        elseif ($scanTime->between(Carbon::parse("$date " . $sesi['istirahat']->from), Carbon::parse("$date " . $sesi['kembali']->from), true)) {
            $actionType = 'istirahat';
            $batasNormal = Carbon::parse("$date " . $sesi['istirahat']->to);
            $this->hitungKeterlambatann($scanTime, $batasNormal, $reservasiAktif, $keterangan, "Istirahat");
        }

        elseif ($scanTime->between(Carbon::parse("$date " . $sesi['kembali']->from), Carbon::parse("$date " . $sesi['pulang']->from), true)) {
            $actionType = 'kembali';
            $batasNormal = Carbon::parse("$date " . $sesi['kembali']->to); // Jam 13:51

            $this->hitungKeterlambatann($scanTime, $batasNormal, $reservasiAktif, $keterangan, "Kembali");
        }

        elseif ($scanTime->between(Carbon::parse("$date " . $sesi['pulang']->from), Carbon::parse("$date 23:59:59"), true)) {
            $actionType = 'pulang';
            $batasNormal = Carbon::parse("$date " . $sesi['pulang']->to); // 17:51

            $this->hitungKeterlambatann($scanTime, $batasNormal, $reservasiAktif, $keterangan, "Pulang");
        }

        else {
            $actionType = null;
            $keterangan = "Scan di luar jam absensi";
        }

        return [
            'actionType' => $actionType,
            'keterangan' => $keterangan,
            'reservasi'  => $reservasiAktif
        ];
    }

    private function hitungKeterlambatann($scanTime, $batasNormal, $reservasiAktif, &$keterangan, $prefix = "")
    {
        $prefix = $prefix ? "$prefix " : "";
        if ($reservasiAktif) {
            $batasIzin = Carbon::parse($reservasiAktif->waktu_akhir);

            if ($scanTime->greaterThan($batasIzin)) {
                $menit = $scanTime->diffInMinutes($batasIzin);
                $keterangan = "{$prefix}Terlambat $menit menit (Melebihi Batas Reservasi)";
            } else {
                $keterangan = "{$prefix}Tepat waktu (Dalam Masa Reservasi)";
            }
        } 
        else {
            if ($scanTime->greaterThan($batasNormal)) {
                $menit = $scanTime->diffInMinutes($batasNormal);
                $keterangan = "{$prefix}Terlambat $menit menit";
            } else {
                $keterangan = "{$prefix}Tepat waktu";
            }
        }
    }

    private function cariReservasiAktif($idSiswa, $idKelas, $scanTime)
    {
        $date = $scanTime->toDateString();

        return Reservasi::where(function($query) use ($idSiswa, $idKelas) {
                $query->where('id_siswa', $idSiswa)
                    ->orWhere('id_kelas', $idKelas);
            })
            ->whereDate('waktu_mulai', $date)
            ->latest('waktu_akhir') 
            ->first();
    }

    private function simpanTransaksiAbsen($idSiswa, $actionType, $scanTime)
    {
        $existingAbsen = TransaksiAbsen::where('id_siswa', $idSiswa)
            ->whereDate('waktu_tap', $scanTime->toDateString())
            ->where('status', $actionType) 
            ->exists();

        if ($existingAbsen) {
            return ['success' => false, 'message' => 'Sudah melakukan scan sesi ini'];
        }

        TransaksiAbsen::create([
            'id_siswa' => $idSiswa,
            'status' => $actionType,
            'waktu_tap' => $scanTime,
        ]);

        return ['success' => true];
    }
    
}