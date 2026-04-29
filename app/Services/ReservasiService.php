<?php

namespace App\Services;

use App\Models\Reservasi;
use App\Services\SiswaService;
use App\Services\APIGateway;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReservasiService
{
    protected APIGateway $apiGateway;
    protected SiswaService $siswaService;

    public function __construct(
        APIGateway $apiGateway,
        SiswaService $siswaService
    ) {
        $this->apiGateway = $apiGateway;
        $this->siswaService = $siswaService;
    }

    public function createReservasi(array $siswaIds, string $keterangan, string $jamMulai, string $jamAkhir): array
    {
        $waktuMulai = Carbon::parse($jamMulai)->format('Y-m-d H:i:s');
        $waktuAkhir = Carbon::parse($jamAkhir)->format('Y-m-d H:i:s');

        return DB::transaction(function () use ($siswaIds, $keterangan, $waktuMulai, $waktuAkhir) {
            $dataAPI = [];
            $allSiswas = $this->siswaService->getByIds($siswaIds);

            foreach ($allSiswas as $siswa) {
                if ($this->cekOverlapping($siswa->id, $waktuMulai, $waktuAkhir)) {
                    throw new \Exception("Siswa {$siswa->nama} sudah memiliki reservasi pada waktu ini.");
                }

                Reservasi::create([
                    'id_siswa'      => $siswa->id,
                    'id_kelas'      => $siswa->id_kelas,
                    'waktu_mulai'   => $waktuMulai,
                    'waktu_akhir'   => $waktuAkhir,
                    'keterangan'    => $keterangan,
                ]);

                $dataAPI[] = [
                    'nama'  => $siswa->nama,
                    'email' => $siswa->no_orangtua,
                ];
            }

            // Pastikan selalu return array
            return [
                'dataAPI'   => $dataAPI,
                'waktuMulai' => $waktuMulai,
                'waktuAkhir' => $waktuAkhir,
                'keterangan' => $keterangan,
            ];
        }) ?? [
            'dataAPI' => [],
            'waktuMulai' => $waktuMulai,
            'waktuAkhir' => $waktuAkhir,
            'keterangan' => $keterangan,
        ];
    }

    public function getActiveByScanTime($idSiswa, $idKelas, $scanTime)
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

    /**
     * Cek overlapping reservasi untuk siswa yang sama pada hari yang sama
     */
    public function cekOverlapping(int $siswaId, string $waktuMulai, string $waktuAkhir): bool
    {
        $today = now()->toDateString();
        // return Reservasi::where('id_siswa', $siswaId)
        //     ->whereDate('created_at', $today)
        //     ->where(function ($query) use ($waktuMulai, $waktuAkhir) {
        //         $query->where('waktu_mulai', '<', $waktuAkhir)
        //               ->where('waktu_akhir', '>', $waktuMulai);
        //     })
        //     ->exists();

        return Reservasi::where('id_siswa', $siswaId)
            ->where(function ($query) use ($waktuMulai, $waktuAkhir) {
                $query->where('waktu_mulai', '<', $waktuAkhir)
                    ->where('waktu_akhir', '>', $waktuMulai);
            })
            ->exists();
    }

    /**
     * Kirim notifikasi via API Gateway
     */
    public function kirimNotifikasi(array $dataAPI, string $waktuMulai, string $waktuAkhir, string $keterangan): void
    {
        $payload = [
            'event'    => 'create-reservasi',
            'channels' => ['mail'],
            'recipients' => array_map(function ($item) {
                return [
                    'nama'  => $item['nama'],
                    'email' => $item['email'],
                ];
            }, $dataAPI),
            'context' => [
                'waktu_mulai' => $waktuMulai,
                'waktu_akhir' => $waktuAkhir, 
                'keterangan' => $keterangan,
            ],
        ];

        try {
            
            $this->apiGateway->sendWithToken('POST', '/notifications', $payload);
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi reservasi: ' . $e->getMessage());
        }
    }

    /**
     * Ambil daftar reservasi untuk seorang siswa dengan format tertentu
     */
    public function getAllBySiswa(int $siswaId): array
    {
        return Reservasi::selectRaw("
                id,
                DATE_FORMAT(waktu_mulai, '%H:%i') AS waktu_mulai,
                DATE_FORMAT(waktu_akhir, '%H:%i') AS waktu_akhir,
                keterangan
            ")
            ->where('id_siswa', $siswaId)
            ->orderBy('waktu_mulai', 'desc')
            ->get()
            ->toArray();
    }

    public function getWithSiswa(){
        return Reservasi::with(['siswa:id,nama'])
            ->select('id', 'id_siswa', 'keterangan', 'waktu_mulai', 'waktu_akhir')
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function tentukanSessionReservasi($reservasi, $waktuAbsen, $tanggalFilter)
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

    public function isReservasiCoverSemuaSession($reservasiList, $waktuAbsen, $tanggalFilter)
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

    public function isReservasiMultiHari($reservasiList, $tanggalFilter)
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
}