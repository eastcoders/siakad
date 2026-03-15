<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public static function kirim($nomor, $pesan)
    {
        $apiUrl = env('WA_API_URL');
        $apiKey = env('WA_API_KEY', 'dhimas'); // Default key sesuai server Anda

        try {
            $nomor = self::formatNomor($nomor);

            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post($apiUrl, [
                'number' => $nomor,
                'message' => $pesan,
            ]);

            // kalau gagal (misalnya 4xx atau 5xx), log errornya
            if (! $response->successful()) {
                Log::error('WA API gagal', [
                    'number' => $nomor,
                    'message' => trim($pesan),
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }

            return $response->successful();
        } catch (\Throwable $e) {
            // simpan error ke laravel.log tapi jangan hentikan proses
            Log::error('Kirim WA gagal: '.$e->getMessage(), [
                'number' => $nomor ?? null,
                'pesan' => $pesan ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            // tetap return false agar proses lain tetap bisa lanjut
            return false;
        }
    }

    public static function kirimDenganFile($nomor, $pesan, $filePath = null)
    {
        $apiUrl = env('WA_API_URL');
        $apiKey = env('WA_API_KEY', 'dhimas');

        $nomor = self::formatNomor($nomor);

        $request = Http::withHeaders([
            'x-api-key' => $apiKey,
        ]);

        if ($filePath && file_exists($filePath)) {
            $response = $request->attach(
                'file',
                file_get_contents($filePath),
                basename($filePath)
            )->post($apiUrl, [
                'number' => $nomor,
                'message' => $pesan,
            ]);
        } else {
            $response = $request->post($apiUrl, [
                'number' => $nomor,
                'message' => $pesan,
            ]);
        }

        return $response->successful();
    }

    private static function formatNomor($nomor)
    {
        $nomor = preg_replace('/[^0-9]/', '', $nomor);

        if (substr($nomor, 0, 1) === '0') {
            return '62'.substr($nomor, 1);
        }

        if (substr($nomor, 0, 2) === '62') {
            return $nomor;
        }

        return '62'.$nomor;
    }
}
