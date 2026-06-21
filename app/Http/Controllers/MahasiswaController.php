<?php
namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class MahasiswaController extends Controller
{
    public function updateUid(Request $request)
    {
        $request->validate([
            'nim' => 'required',
            'uid_ktm' => 'required'
        ]);

        $mhs = Mahasiswa::where('nim', $request->nim)->first();

        if (!$mhs) {
            return response()->json(['status' => 'error', 'message' => 'NIM tidak ditemukan'], 404);
        }

        $mhs->update(['uid_ktm' => $request->uid_ktm]);

        return response()->json(['status' => 'success', 'message' => 'Data tersimpan']);
    }
}