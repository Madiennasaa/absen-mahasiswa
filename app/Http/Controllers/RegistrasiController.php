// app/Http/Controllers/RegistrasiController.php
public function updateUid(Request $request)
{
    $request->validate([
        'nim' => 'required',
        'uid_ktm' => 'required|unique:mahasiswa,uid_ktm' // Mencegah UID dipakai dua orang
    ]);

    $mhs = Mahasiswa::where('nim', $request->nim)->first();

    if (!$mhs) {
        return response()->json(['status' => 'error', 'message' => 'NIM tidak ditemukan'], 404);
    }

    $mhs->update(['uid_ktm' => $request->uid_ktm]);

    return response()->json([
        'status' => 'success', 
        'message' => 'UID berhasil disimpan untuk ' . $mhs->nama
    ]);
}