<?php

namespace App\Http\Controllers;

use App\Http\Requests\Scan\ScanUidRequest;
use App\Services\ScanService;
use App\Services\ReservasiService;
use App\Services\AbsensiService;
use App\Services\SiswaService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScanController extends Controller
{
    use ApiResponse;

    protected ScanService $scanService;
    protected AbsensiService $absensiService;
    protected ReservasiService $reservasiService;
    protected SiswaService $siswaService;

    public function __construct(
        ScanService $scanService,
        AbsensiService $absensiService,
        ReservasiService $reservasiService,
        SiswaService $siswaService
    ) {
        $this->scanService = $scanService;
        $this->absensiService = $absensiService;
        $this->reservasiService = $reservasiService;
        $this->siswaService = $siswaService;
    }

    public function register(ScanUidRequest $request)
    {
        try {
            $result = $this->scanService->validateCard($request->uid);
           
            return $result['success']
                ? $this->successResponse($result['data'])
                : $this->errorResponse($result['message'], $result['error_code']);
        } catch (\Exception $e) {
            Log::error('Register scan error: ' . $e->getMessage());
            return response()->json([
                'isSuccess' => false,
                'error' => 'Laravel Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(ScanUidRequest $request)
    {
        try {
            $result = $this->scanService->validateCard($request->uid);
           
            return $result['success']
                ? $this->successResponse($result['data'])
                : $this->errorResponse($result['message'], $result['error_code']);
        } catch (\Exception $e) {
            Log::error('Register scan error: ' . $e->getMessage());
            return response()->json([
                'isSuccess' => false,
                'error' => 'Laravel Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function login(ScanUidRequest $request)
    {
        $idCard = $request->uid;
        
        try {
            $student = $this->siswaService->getBy('id_card', $idCard);

            if (!$student) {
                return $this->errorResponse(
                    'Kartu tidak terdaftar',
                    'NOT_FOUND'
                );
            }

            return DB::transaction(function () use ($student, $idCard) {
                $scanTime = Carbon::now();
                
                $reservasiAktif = $this->reservasiService->getActiveByScanTime(
                    $student->id, 
                    $student->id_kelas, 
                    $scanTime
                );
                
                $absensiStudent = $this->absensiService->getByScanTime(
                    $scanTime, 
                    $reservasiAktif)
                ;
                
                $actionType = $absensiStudent['actionType'];
                $keterangan = $absensiStudent['keterangan'];

                if (is_null($actionType)) {
                    return $this->errorResponse($keterangan, 'EXPIRED');
                }

                $result = $this->absensiService->createTransaksiAbsen(
                    $student->id, 
                    $actionType, 
                    $scanTime
                );
                
                if (!$result['success']) {
                    return $this->errorResponse( $result['message'], 'SPAM');
                }

                return $this->successResponse(
                    [
                        'uid' => $idCard,
                        'email' => $student->no_orangtua,
                        'nama' => $student->nama,
                        'status' => $actionType,
                        'keterangan' => $keterangan,
                    ]
                );
            });

        } catch (\Exception $e) {
            return response()->json([
                'isSuccess' => false,
                'error' => 'Laravel Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}