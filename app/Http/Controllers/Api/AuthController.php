<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi input dari mobile
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. Cek email dan password
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.'
            ], 401); // 401: Unauthorized
        }

        // 3. Ambil data user yang berhasil login
        $user = User::where('email', $request->email)->first();

        // 4. KEAMANAN: Pastikan yang login HANYA teknisi
        if (!$user->hasRole('teknisi')) {
            // Jika admin/user biasa mencoba login di aplikasi mobile, kita tolak
            $user->tokens()->delete(); // Hapus token jika terlanjur dibuat
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Aplikasi ini khusus untuk Teknisi IT.'
            ], 403); // 403: Forbidden
        }

        // 5. Buat Token menggunakan Sanctum
        $token = $user->createToken('MobileAppToken')->plainTextToken;

        // 6. Kirim respon sukses beserta token ke HP
        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token // Ini yang paling penting!
            ]
        ], 200);
    }
}
