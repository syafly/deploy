<?php

namespace App\Services;

use App\Models\Reservasi;
use App\Models\Kelas;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class KelasService
{
    public function getAll() {
        return Kelas::select('id','nama_kelas')->get(['id', 'nama_kelas']);
    }

    public function getAllByOrder($by) {
        return Kelas::orderBy($by, 'asc')->get(['id', 'nama_kelas']);
    }

    public function processBatch(array $added, array $updated, array $deleted): void
    {
        DB::transaction(function () use ($added, $updated, $deleted) {
            // 1. Validasi tambahan (unique untuk update)
            $this->validateUniqueOnUpdate($updated);

            // 2. Hapus data
            if (!empty($deleted)) {
                Kelas::whereIn('id', $deleted)->delete();
            }

            // 3. Update data
            foreach ($updated as $item) {
                Kelas::where('id', $item['id'])
                    ->update(['nama_kelas' => $item['nama_kelas']]);
            }

            // 4. Tambah data baru
            foreach ($added as $item) {
                Kelas::create(['nama_kelas' => $item['nama_kelas']]);
            }
        });
    }

    /**
     * Validasi unique untuk nama_kelas pada data yang diupdate
     */
    protected function validateUniqueOnUpdate(array $updated): void
    {
        $names = array_column($updated, 'nama_kelas');
        $ids = array_column($updated, 'id');

        $duplicates = Kelas::whereIn('nama_kelas', $names)
            ->whereNotIn('id', $ids)
            ->pluck('nama_kelas')
            ->toArray();

        if (!empty($duplicates)) {
            $errors = [];
            foreach ($updated as $index => $item) {
                if (in_array($item['nama_kelas'], $duplicates)) {
                    $errors["updated.{$index}.nama_kelas"] = "Nama kelas '{$item['nama_kelas']}' sudah digunakan.";
                }
            }
            throw ValidationException::withMessages($errors);
        }
    }
}