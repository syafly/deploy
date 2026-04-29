<?php

namespace App\Services;

use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SiswaService
{
    public function getWithKelasByQuery($kelas, $nama_siswa) {
        return Siswa::with('kelas:id,nama_kelas')
            ->select('id','nama','id_kelas')
            ->orderBy('id')
            ->when($kelas, fn($q,$k) =>
                $q->where('id_kelas',$k)
            )
            ->when($nama_siswa, fn($q,$sh) =>
                $q->where('nama','like',$sh.'%')
            );
    }

    public function checkSiswaByUid($uid) {
        return Siswa::select('id', 'nama', 'no_orangtua', 'id_kelas')
            ->where('id_card', $uid)->first();
    }

    public function getWithAbsensiCountByQuery(?int $kelas, ?string $nama_siswa)
    {
        return Siswa::with('kelas:id,nama_kelas')
            ->select('id', 'id_kelas', 'no_orangtua', 'nama', 'id_card')
            ->withCount([
                'absensi as hadir_count' => fn($q) => $q->where('status', 'masuk'),
                'absensi as alpa_count'  => fn($q) => $q->where('status', 'alpa'),
                'absensi as izin_count'  => fn($q) => $q->where('status', 'izin'),
            ])
            ->when($nama_siswa, fn($que,$sea) =>
                $que->where(function($q) use ($sea) {
                    $q->where('nama', 'like', $sea."%")
                    ->orWhere('id_card', 'like', $sea."%");
                })
            )
            ->when($kelas, fn($q,$kel) =>
                $q->where('id_kelas',$kel)
            );
    }

    public function getWithInfoAbsensiByQuery($kelas, $nama_siswa, $tanggal){
        return Siswa::with([
            'kelas',
            'absensi' => function($query) use ($tanggal) {
                $query->whereDate('tanggal', $tanggal);
            },
            'transaksi_absen' => function($query) use ($tanggal) {
                $query->whereDate('waktu_tap', $tanggal);
            }
        ])
        ->when($kelas, fn ($query) => $query->where('id_kelas', $kelas))
        ->when($nama_siswa, function($query) use ($nama_siswa) {
            $query->where(function($q) use ($nama_siswa) {
                $q->where('nama', 'like', $nama_siswa."%")
                  ->orWhere('id_card', 'like', $nama_siswa."%");
            });
        });
    }

    public function getWithReservasiByQuery($kelas, $nama_siswa, $tanggal){
        return Siswa::with([
            'reservasi' => function($query) use ($tanggal) {
                $query->whereDate('waktu_mulai', '<=', $tanggal)
                      ->whereDate('waktu_akhir', '>=', $tanggal)
                      ->orderBy('waktu_mulai');
            },
            'transaksi_absen' => function($query) use ($tanggal) {
                $query->whereDate('waktu_tap', $tanggal);
            },
            'absensi' => function($query) use ($tanggal) {
                $query->whereDate('tanggal', $tanggal);
            }
        ])
        ->when($kelas, fn ($q) => $q->where('id_kelas', $kelas))
        ->when($nama_siswa, function($q) use ($nama_siswa) {
            $q->where(function($query) use ($nama_siswa) {
                $query->where('id', 'LIKE', $nama_siswa."%")
                      ->orWhere('nama', 'LIKE', $nama_siswa."%");
            });
        })
        ->get();
    }

    public function getAllNotRekapByQuery($kelas, $nama_siswa, $tanggal){
        return Siswa::when($kelas, fn ($query) => $query->where('id_kelas', $kelas))
            ->when($nama_siswa, function($query) use ($nama_siswa) {
                $query->where(function($q) use ($nama_siswa) {
                    $q->where('nama', 'like', $nama_siswa."%")
                      ->orWhere('id_card', 'like', $nama_siswa."%");
                });
            })
            ->whereDoesntHave('absensi', function($query) use ($tanggal) {
                $query->whereDate('tanggal', $tanggal)
                      ->where('status', '!=', '-');
            })
            ->count();
    }

    public function createSiswa(array $data): Siswa
    {
        return DB::transaction(function () use ($data) {
            return Siswa::create([
                'nama'          => $data['nama'],
                'id_kelas'      => $data['kelas'],
                'no_orangtua'   => $data['no_ortu'],
                'id_card'       => $data['id_card'],
            ]);
        });
    }

    public function updateSiswa(int $id, array $data): Siswa
    {
        $siswa = Siswa::findOrFail($id);

        return DB::transaction(function () use ($siswa, $data) {
            $siswa->update($data);
            return $siswa;
        });
    }

    public function deleteSiswa(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return Siswa::destroy($id);
        });
    }

    public function getBy(string $by, string $value)
    {
        return Siswa::with('kelas:id')
            ->select('id', 'id_kelas', 'id_card', 'no_orangtua', 'nama')
            ->where($by, $value)
            ->first();
    }

    public function getByIds(array $siswaIds){
        return Siswa::whereIn('id', $siswaIds)->lockForUpdate()->get();
    }

    public function transformSiswa($siswas){
        return $siswas->getCollection()->transform(function ($siswa) {
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
    }
}