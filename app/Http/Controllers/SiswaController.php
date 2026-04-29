<?php

namespace App\Http\Controllers;

use App\Http\Requests\Siswa\IndexSiswaRequest;
use App\Http\Requests\Siswa\StoreSiswaRequest;
use App\Http\Requests\Siswa\UpdateSiswaRequest;
use App\Services\SiswaService;
use App\Services\KelasService;
use App\Traits\RestResponse;
use Illuminate\Support\Facades\Log;

class SiswaController extends Controller
{
    use RestResponse;

    protected SiswaService $siswaService;
    protected KelasService $kelasService;

    public function __construct(
        SiswaService $siswaService,
        KelasService $kelasService
    ) {
        $this->siswaService = $siswaService;
        $this->kelasService = $kelasService;
    }

    public function index(IndexSiswaRequest $request)
    {
        $query = $this->siswaService->getWithAbsensiCountByQuery(
            $request->kelas,
            $request->search,
            10
        );

        $siswa_list = $query->paginate(10);

        $kelas_list = $this->kelasService->getAll();

        if ($request->has('_ajax') || $request->wantsJson()) {
            return response()->json([
                'html'           => view('siswa.partials.table', compact('siswa_list'))->render(),
                'infoPagination' => view('siswa.partials.info-pagination', compact('siswa_list'))->render(),
                'activeFilters'  => view('siswa.partials.active-filters', compact('kelas_list'))->render(),
                'success'        => true,
            ]);
        }

        return view('siswa.index', compact('siswa_list', 'kelas_list'));
    }


    public function tambah()
    {
        $listKelas = $this->kelasService->getAll();
        return view('siswa.create', compact('listKelas'));
    }

    public function store(StoreSiswaRequest $request)
    {
        try {
            $siswa = $this->siswaService->createSiswa($request->validated());

            return $this->successResponse(
                $siswa,
                'Data siswa berhasil ditambahkan!'
            );
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan siswa: ' . $e->getMessage());
            return $this->errorResponse('Gagal menyimpan data siswa', 500);
        }
    }

    public function edit($id)
    {
        $siswa = $this->siswaService->getBy('id', $id); // perlu method tambahan di service
        $kelas = $this->kelasService->getAll();
        return view('siswa.edit', compact('siswa', 'kelas'));
    }

    public function update(UpdateSiswaRequest $request, $id)
    {
        try {
            $siswa = $this->siswaService->updateSiswa($id, $request->validated());

            return redirect()->route('siswa')
                ->with('success', 'Data siswa berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Gagal update siswa: ' . $e->getMessage());
            return back()
                ->withErrors(['message' => $e->getMessage()])
                ->withInput();
        }
    }

    public function delete($id)
    {
        try {
            $this->siswaService->deleteSiswa($id);

            return redirect()->route('siswa')
                ->with('success', 'Data siswa berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Gagal hapus siswa: ' . $e->getMessage());
            return redirect()->route('siswa')
                ->with('error', 'Gagal menghapus data siswa');
        }
    }
}