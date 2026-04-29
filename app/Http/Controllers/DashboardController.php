<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\TransaksiAbsen;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $data = $this->getDashboardData();
        return view('home', ['data' => $data]);
    }

    public function getDashboardData()
    {
        $today = now()->format('Y-m-d');
        
        return [
            'stats' => [
                'total' => Siswa::count(),
                'present' => TransaksiAbsen::whereDate('waktu_tap', $today)
                                    ->distinct('id_siswa')
                                    ->count('id_siswa'),
                'late' => $this->getLateCount($today),
                'absent' => $this->getAbsentCount($today)
            ]
            // Activities dan system_status dihapus
        ];
    }

    private function getLateCount($today)
    {
        return TransaksiAbsen::whereDate('waktu_tap', $today)
            ->where('status', 'masuk')
            ->whereTime('waktu_tap', '>', '07:00:00')
            ->count();
    }

    private function getAbsentCount($today)
    {
        $totalSiswa = Siswa::count();
        $presentToday = TransaksiAbsen::whereDate('waktu_tap', $today)
            ->distinct('id_siswa')
            ->count('id_siswa');
            
        return max(0, $totalSiswa - $presentToday);
    }
}