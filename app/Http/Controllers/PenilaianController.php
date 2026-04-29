<?php

namespace App\Http\Controllers;

use App\Models\Penilaian;
use Illuminate\Http\Request;

class PenilaianController extends Controller
{
    // Menampilkan semua aturan di view admin
    public function index()
    {
        $rules = Penilaian::all();
        return view('penilaian.index', compact('rules'));
    }

    // Update status aturan (AJAX)
    public function update(Request $request, $id)
    {
        $request->validate([
            'status_output' => 'required|in:masuk,alpa'
        ]);

        try {
            $rule = Penilaian::findOrFail($id);
            $rule->update([
                'status_output' => $request->status_output
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status aturan berhasil diperbarui!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status aturan: ' . $e->getMessage()
            ], 500);
        }
    }
}