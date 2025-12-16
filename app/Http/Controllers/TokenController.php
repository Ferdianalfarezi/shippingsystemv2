<?php

namespace App\Http\Controllers;

use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TokenController extends Controller
{
    //jadi nanti logicnya dalam 32 digit itu akan berisi 2 code pass dan next nah ketika pass di gunakan otomatis yang digit next akan menjadi pass dan akan men generate kode next yang baru sesuai input. 
    // Posisi digit untuk pass code (untuk validasi)
    const PASS_POSITIONS = [16, 5, 11, 21, 13];
    
    // Posisi digit untuk next code (untuk disimpan sebagai passcode berikutnya)
    const NEXT_POSITIONS = [31, 7, 25, 17, 4];
    
    /**
     * Extract pass code dari token 32 digit
     */
    private function extractPassCode($token)
    {
        if (strlen($token) !== 32) {
            return null;
        }
        
        $passCode = '';
        foreach (self::PASS_POSITIONS as $position) {
            $passCode .= $token[$position - 1];
        }
        
        return $passCode;
    }
    
    /**
     * Extract next code dari token 32 digit
     */
    private function extractNextCode($token)
    {
        if (strlen($token) !== 32) {
            return null;
        }
        
        $nextCode = '';
        foreach (self::NEXT_POSITIONS as $position) {
            $nextCode .= $token[$position - 1];
        }
        
        return $nextCode;
    }
    
    /**
     * Cek apakah sistem expired
     */
    public function isSystemExpired()
    {
        try {
            $token = Token::first();
            
            if (!$token) {
                return response()->json(true);
            }
            
            $isExpired = Carbon::now()->gt($token->expired_at);
            return response()->json($isExpired);
            
        } catch (\Exception $e) {
            Log::error('Error checking system expiry: ' . $e->getMessage());
            return response()->json(true); // Default to expired if error
        }
    }
    
    /**
     * Validasi dan update token sistem
     */
    public function validateToken(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string|size:32'
            ]);
            
            $inputToken = $request->token;
            
            // Extract pass code untuk validasi
            $passCode = $this->extractPassCode($inputToken);
            
            // Extract next code untuk disimpan sebagai passcode berikutnya
            $nextCode = $this->extractNextCode($inputToken);
            
            if (!$passCode || !$nextCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid'
                ], 400);
            }
            
            $tokenRecord = Token::first();
            
            if ($tokenRecord) {
                // VALIDASI: cek apakah pass code cocok dengan passcode di database
                if ($tokenRecord->passcode !== $passCode) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Token tidak valid atau sudah tidak berlaku'
                    ], 401);
                }
                
                // Jika valid, update dengan next code sebagai passcode baru
                $tokenRecord->update([
                    'passcode' => $nextCode,
                    'expired_at' => Carbon::now()->addYear()
                ]);
            } else {
                // Aktivasi pertama kali (tidak perlu validasi)
                Token::create([
                    'passcode' => $nextCode,
                    'expired_at' => Carbon::now()->addYear()
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Token berhasil divalidasi',
                'expired_at' => Carbon::now()->addYear()->format('d/m/Y')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error validating token: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }
    
    /**
     * Get masa berlaku sistem untuk ditampilkan di login
     */
    public function getSystemExpiry()
    {
        try {
            $token = Token::first();
            
            if (!$token) {
                return response('Sistem belum diaktivasi', 200)
                    ->header('Content-Type', 'text/plain');
            }
            
            $formattedDate = $token->expired_at->format('d/m/Y');
            return response($formattedDate, 200)
                ->header('Content-Type', 'text/plain');
                
        } catch (\Exception $e) {
            Log::error('Error getting system expiry: ' . $e->getMessage());
            return response('Error loading date', 200)
                ->header('Content-Type', 'text/plain');
        }
    }
}