<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WaktuAbsen;

class WaktuAbsenController extends Controller
{
    public function simpan(Request $request)
    {
        try {
            $waktuSettings = $request->waktu_settings;
            
            foreach ($waktuSettings as $setting) {
                WaktuAbsen::updateOrCreate(
                    [
                        'status' => $setting['status']
                    ],
                    [
                        'from' => $setting['from'],
                        'to' => $setting['to']
                    ]
                );
            }
            
            // Ambil data terbaru untuk dikembalikan ke frontend
            $updatedSettings = WaktuAbsen::all()->keyBy('status');
            
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan waktu berhasil disimpan',
                'data' => $updatedSettings
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
