<?php

namespace App\Http\Controllers;

use App\Http\Requests\Kelas\BatchKelasRequest;
use App\Services\KelasService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Traits\RestResponse;

class KelasController extends Controller
{
    use RestResponse;

    protected $kelasService;

    public function __construct(KelasService $kelasService)
    {
        $this->kelasService = $kelasService;
    }

    public function index()
    {
        $kelas = $this->kelasService->getAllByOrder('id');

        return $this->successResponse($kelas);
    }

    public function saveBatchChanges(BatchKelasRequest $request)
    {
        try {
            $this->kelasService->processBatch(
                $request->input('added', []),
                $request->input('updated', []),
                $request->input('deleted', [])
            );

            return $this->successResponse(null, 'Semua perubahan berhasil disimpan');

        } catch (ValidationException $e) {
            return $this->errorResponse('Validasi gagal', 422);

        } catch (\Exception $e) {
            Log::error('Batch kelas error: ' . $e->getMessage());

            return $this->errorResponse('Gagal menyimpan perubahan: ' . $e->getMessage());
        }
    }
}