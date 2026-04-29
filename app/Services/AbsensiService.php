<?php

namespace App\Services;

use App\Services\SiswaService;
use Carbon\Carbon;
use App\Models\TransaksiAbsen;
use App\Models\WaktuAbsen;
use App\Models\Absensi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AbsensiService
{
    protected SiswaService $siswaService;
    protected ReservasiService $reservasiService;

    public function __construct(
        SiswaService $siswaService,
        ReservasiService $reservasiService
    ) {
        $this->siswaService = $siswaService;
        $this->reservasiService = $reservasiService;
    }
    
    public function getByScanTime($scanTime, $reservasiAktif = null)
    {
        $date = $scanTime->toDateString();
        $waktuAbsenDB = $this->getWaktuAbsenByKey('status');

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

    public function getWaktuAbsenByKey($key){
        return WaktuAbsen::select('status', 'from', 'to')
            ->get()
            ->keyBy($key);
    }

    public function getRulesAbsen(){
        return DB::table('attendance_rules')->get();
    }

    public function cekAbsensiBySiswa($idSiswa, $scanTime, $actionType) {
        return TransaksiAbsen::where('id_siswa', $idSiswa)
            ->whereDate('waktu_tap', $scanTime->toDateString())
            ->where('status', $actionType) 
            ->exists();
    }

    public function createTransaksiAbsen($idSiswa, $actionType, $scanTime)
    {
        $existingAbsen = $this->cekAbsensiBySiswa($idSiswa, $scanTime, $actionType);

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

    public function processAbsensi($siswaList, $tanggal, $attendanceRules, $waktuAbsen){
        $dataToSave = [];
        $reservasiIdsToDelete = [];

        foreach ($siswaList as $siswa) {
            $statusTersimpan = $siswa->absensi->first()->status ?? null;
            
            if ($statusTersimpan && $statusTersimpan !== '-') {
                continue;
            }

            if ($siswa->reservasi->isNotEmpty()) {
                $statusFinal = $this->prosesDenganReservasi($siswa, $attendanceRules, $waktuAbsen, $tanggal);
                
                foreach ($siswa->reservasi as $reservasi) {
                    $waktuAkhir = Carbon::parse($reservasi->waktu_akhir);
                    if ($waktuAkhir->toDateString() === $tanggal) {
                        $reservasiIdsToDelete[] = $reservasi->id;
                    }
                }
            } else {
                $statusFinal = $this->prosesTanpaReservasi($siswa, $attendanceRules, $waktuAbsen, $tanggal);
            }

            $dataToSave[] = [
                'id_siswa' => $siswa->id,
                'tanggal' => $tanggal,
                'keterangan' => $statusFinal['keterangan'],
                'status' => $statusFinal['status'],
            ];
        }

        if (!empty($dataToSave)) {
            Absensi::upsert($dataToSave, ['id_siswa', 'tanggal'], ['keterangan', 'status']);
        }
        
        if (!empty($reservasiIdsToDelete)) {
            $reservasiIdsToDelete = array_unique($reservasiIdsToDelete);
            
            DB::table('reservasis')->whereIn('id', $reservasiIdsToDelete)->delete();
        }

        return [$dataToSave, $reservasiIdsToDelete];
    }

    private function prosesDenganReservasi($siswa, $attendanceRules, $waktuAbsen, $tanggalFilter)
    {
        $reservasiList = $siswa->reservasi;
        
        // 1. MAPPING SESSION YANG DI-COVER OLEH RESERVASI
        $sessionCoveredByReservasi = [
            'masuk' => false, 'istirahat' => false, 'kembali_istirahat' => false, 'pulang' => false
        ];

        foreach ($reservasiList as $reservasi) {
            $sessionReservasi = $this->reservasiService->tentukanSessionReservasi($reservasi, $waktuAbsen, $tanggalFilter);
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
        $isCoverSemuaSession = $this->reservasiService->isReservasiCoverSemuaSession($reservasiList, $waktuAbsen, $tanggalFilter);
        $isReservasiMultiHari = $this->reservasiService->isReservasiMultiHari($reservasiList, $tanggalFilter);
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

    private function groupSessionsByReservasi($sessions, $reservasiList, $waktuAbsen, $tanggalFilter)
    {
        // BUAT GROUP BERDASARKAN RESERVASI, BUKAN BERDASARKAN URUTAN
        $reservasiGroups = [];
        
        foreach ($reservasiList as $reservasi) {
            $sessionReservasi = $this->reservasiService->tentukanSessionReservasi($reservasi, $waktuAbsen, $tanggalFilter);
            $reservasiSessions = array_intersect($sessionReservasi['sessions'], $sessions);
            
            if (!empty($reservasiSessions)) {
                // Group session dalam reservasi ini
                $grouped = $this->groupSessions($reservasiSessions);
                $reservasiGroups = array_merge($reservasiGroups, $grouped);
            }
        }
        
        return $reservasiGroups;
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
}