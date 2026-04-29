<?php

namespace App\Services;

use App\Services\SiswaService;
use App\Models\Siswa;
use Illuminate\Support\Facades\Log;

class ScanService
{
    protected SiswaService $siswaService;

    public function __construct(
        SiswaService $siswaService
    ) {
        $this->siswaService = $siswaService;
    }

    public function validateCard(string $idCard, $studentId = null): array 
    {
        $query = Siswa::where('id_card', $idCard);

        if ($studentId) {
            $query->where('id', '!=', $studentId);
        }

        $existingStudent = $query->first();

        if ($existingStudent) {
            return [
                'success' => false,
                'error_code' => 'ALREADY_REGISTERED',
                'message' => 'Kartu sudah terdaftar atas nama ' . $existingStudent->nama,
            ];
        }

        return [
            'success' => true,
            'data' => ['uid' => $idCard],
        ];
    }
}