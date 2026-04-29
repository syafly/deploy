<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Exception;
use App\Traits\RestResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Siswa\IndexSiswaRequest;
use App\Services\SiswaService;
use App\Services\AbsensiService;
use App\Services\KelasService;

class AbsensiController extends Controller
{
    use RestResponse;

    protected SiswaService $siswaService;
    protected AbsensiService $absensiService;
     protected KelasService $kelasService;

    public function __construct(
        SiswaService $siswaService,
        AbsensiService $absensiService,
        KelasService $kelasService
    ) {
        $this->siswaService = $siswaService;
        $this->absensiService = $absensiService;
        $this->kelasService = $kelasService;
    }

    public function rekap(IndexSiswaRequest $request)
    {
        $tanggalFilter = $request->input('tanggal');
        $kelasFilter = $request->input('kelas');
        $search = $request->input('search');

        $attendanceRules = $this->absensiService->getRulesAbsen();
        $waktuAbsen = $this->absensiService->getWaktuAbsenByKey('status');

        $siswaList = $this->siswaService->getWithReservasiByQuery(
            $search,
            $kelasFilter,
            $tanggalFilter
        );

        $totalSiswaDifilter = $siswaList->count();

        DB::beginTransaction();
        try {
            [$dataToSave, $reservasiIdsToDelete] = $this->absensiService->processAbsensi(
                $siswaList, 
                $tanggalFilter,
                $attendanceRules,
                $waktuAbsen
            );
            
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
            return $this->errorResponse('Rekapitulasi gagal: ' . $e->getMessage());
        }
    }        

    public function index(IndexSiswaRequest $request)
    {
        $tanggalFilter = $request->input('tanggal', Carbon::today()->toDateString());
        $kelasFilter = $request->input('kelas');
        $searchFilter = $request->input('search');
        
        $siswaQuery = $this->siswaService->getWithInfoAbsensiByQuery(
            $kelasFilter,
            $searchFilter,
            $tanggalFilter
        )->orderBy('nama');

        $totalSiswaDifilter = $siswaQuery->count();
        $absensi_list = $siswaQuery->paginate(10);

        $this->siswaService->transformSiswa($absensi_list);

        $belumDirekapCount = $this->siswaService->getAllNotRekapByQuery(
            $kelasFilter,
            $searchFilter,
            $tanggalFilter
        );

        $tampilkanTombolRekap = $belumDirekapCount > 0;
        
        $listKelas = $this->kelasService->getAllByOrder('nama_kelas');
        $waktuAbsen = $this->absensiService->getWaktuAbsenByKey('status');
        
        if ($request->boolean('_ajax') || $request->wantsJson()) {
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
    
}