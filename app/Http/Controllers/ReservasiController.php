<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reservasi\StoreReservasiRequest;
use App\Http\Requests\Reservasi\UpdateReservasiRequest;
use App\Http\Requests\Siswa\IndexSiswaRequest;
use App\Http\Requests\Scan\ScanUidRequest;
use App\Models\Reservasi;
use App\Services\ReservasiService;
use App\Services\SiswaService;
use App\Services\KelasService;
use App\Traits\RestResponse;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;

class ReservasiController extends Controller
{
    use ApiResponse, RestResponse {
        ApiResponse::successResponse insteadof RestResponse;
        ApiResponse::errorResponse insteadof RestResponse;

        ApiResponse::successResponse as apiSuccess;
        RestResponse::successResponse as restSuccess;

        ApiResponse::errorResponse as apiError;
        RestResponse::errorResponse as restError;
    }

    protected ReservasiService $reservasiService;
    protected SiswaService $siswaService;
    protected KelasService $kelasService;

    public function __construct(
        ReservasiService $reservasiService,
        SiswaService $siswaService,
        KelasService $kelasService
    ) {
        $this->reservasiService = $reservasiService;
        $this->siswaService = $siswaService;
        $this->kelasService = $kelasService;
    }

    /**
     * Halaman utama reservasi (web)
     */
    public function index(IndexSiswaRequest $request)
    {
        $kelas_list = $this->kelasService->getAll();

        $query = $this->siswaService->getWithKelasByQuery($request->kelas, $request->search);

        $siswa = $query->paginate(10);

        if ($request->boolean('_ajax') || $request->wantsJson()){
            $html = view('reservasi.partials.students-grid', compact('siswa'))->render();
            return $this->htmlPaginatedResponse($html, $siswa->hasMorePages());
        }

        $reservasi = $this->reservasiService->getWithSiswa();

        return view('reservasi.index', compact('kelas_list','siswa','reservasi'));
    }

    /**
     * Load more siswa (untuk infinite scroll)
     */
    public function loadMore(IndexSiswaRequest $request)
    {
        $perPage = 10;
        $page = (int) $request->input('page', 1);
        $partially = $request->boolean('partially');

        // Ambil query builder dari service
        $query = $this->siswaService->getWithKelasByQuery($request->kelas, $request->search);

        if ($partially) {
            // Infinite scroll partial load (ambil sisa data dari offset)
            $totalData = (clone $query)->count();
            $offset = ($page - 1) * $perPage;
            $remaining = max($totalData - $offset, 0); // aman, tidak negatif

            $siswa = $remaining > 0
                ? $query->skip($offset)->take($remaining)->get()
                : collect(); // kosong jika sudah habis

            $hasMore = false;
        } else {
            // Normal pagination
            $siswa = $query->paginate($perPage, ['*'], 'page', $page);
            $hasMore = $siswa->hasMorePages();
            $totalData = $siswa->total();
        }

        $html = view('reservasi.partials.students-grid', [
            'siswa' => $siswa,
            'isPartial' => $partially,
        ])->render();

        return $this->htmlPaginatedResponse(
            $html,
            $hasMore,
            $siswa instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator ? $siswa->count() : $siswa->count(),
            $totalData
        );
    }

    public function store(StoreReservasiRequest $request)
    {
        try {
            $result = $this->reservasiService->createReservasi(
                $request->siswa_ids,
                $request->keterangan_global,
                $request->jam_mulai,
                $request->jam_akhir
            );


            Log::info($result);
            // Kirim notifikasi (di background, tidak mempengaruhi response)
            $this->reservasiService->kirimNotifikasi(
                $result['dataAPI'],
                $result['waktuMulai'],
                $result['waktuAkhir'],
                $result['keterangan']
            );

            $reservasi = $this->reservasiService->getWithSiswa();

            $html = view('reservasi.partials.recent-activity', compact('reservasi'))->render();

            return $this->htmlPaginatedResponse(
                $html, 
                null, null, null, 
                'Reservasi berhasil dibuat'
            );
        } catch (\Exception $e) {
            Log::error('Gagal membuat reservasi: ' . $e->getMessage());

            return $this->restError('Gagal membuat reservasi: ' . $e->getMessage(), 422);
        }
    }

    public function update(UpdateReservasiRequest $request, Reservasi $reservasi)
    {
        try {
            $today = now()->toDateString();
            $waktuMulai = $today . ' ' . $request->jam_mulai . ':00';
            $waktuAkhir = $today . ' ' . $request->jam_akhir . ':00';

            $overlap = $this->reservasiService->checkOverlapping(
                $reservasi->id_siswa,
                $waktuMulai,
                $waktuAkhir
            );

            if ($overlap)
                return $this->restError('Tidak dapat update reservasi: bertumpukan dengan reservasi lain');

            $reservasi->update([
                'waktu_mulai' => $waktuMulai,
                'waktu_akhir' => $waktuAkhir,
                'keterangan'  => $request->keterangan,
            ]);

            return $this->restSuccess(null, 'Reservasi berhasil diupdate');
        } catch (\Exception $e) {

            return $this->restError('Gagal mengupdate reservasi: ' . $e->getMessage());
        }
    }

    public function destroy(Reservasi $reservasi)
    {
        try {
            $reservasi->delete();

            return $this->restSuccess(null, 'Reservasi berhasil dihapus');
        } catch (\Exception $e) {

            return $this->restError('Gagal menghapus reservasi: ' . $e->getMessage());
        }
    }

    public function getAllByUid(ScanUidRequest $request)
    {
        try {
            $siswa = $this->siswaService->checkSiswaByUid($request->uid);
            
            if (!$siswa) {
                return $this->apiError('Siswa tidak ditemukan', 'NOT_FOUND');
            }

            $reservasis = $this->reservasiService->getAllBySiswa($siswa->id);
            return $this->apiSuccess($reservasis);
        } catch (\Exception $e) {
            Log::error('Gagal ambil reservasi: ' . $e->getMessage());
            
            return response()->json([
                'isSuccess' => false,
                'error' => 'Gagal mengambil data reservasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function cancel(ScanUidRequest $request, $id)
    {
        try {
            $siswa = $this->siswaService->checkSiswaByUid($request->uid);
            if (!$siswa) {
                return $this->apiError('Siswa tidak ditemukan', 'NOT_FOUND');
            }

            $reservasi = Reservasi::select('id', 'keterangan', 'waktu_mulai', 'waktu_akhir') // Pilih field tertentu
                ->where('id', $id)
                ->where('id_siswa', $siswa->id)
                ->first();

            if (!$reservasi) {
                return response()->json([
                    'isSuccess' => false,
                    'error' => 'Reservasi tidak ditemukan',
                ], 500);
            }

            $reservasi->delete();

            $dataSiswa = [
                'nama' => $siswa->nama,
                'email' => $siswa->no_orangtua,
            ];

            return $this->apiSuccess(array_merge($reservasi->toArray(), $dataSiswa));
        } catch (\Exception $e) {
            Log::error('Gagal batalkan reservasi: ' . $e->getMessage());
            
            return response()->json([
                'isSuccess' => false,
                'error' => 'Gagal membatalkan reservasi: ' . $e->getMessage(),
            ], 500);
        }
    }
}