<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function signIn(Request $request): \Illuminate\Http\JsonResponse
    {
        $mobilePhone = $request->input('mobile');
        $line_auth_code = $request->input('line_auth_code'); // 可選，用於綁定或記錄

        if (empty($mobilePhone)) { 
            return apiResponse(4001, null, 'Mobile phone is required.', 400);
        }

        if (!preg_match('/^[0-9]{10,15}$/', $mobilePhone)) {
            return apiResponse(4002, null, 'Invalid mobile phone number format.', 400);
        }

        $user = User::where('mobile_phone', $mobilePhone)->first();
        $isNewUser = false;

        DB::beginTransaction();
        try {
            if (!$user) {
                $user = User::create([
                    'mobile_phone' => $mobilePhone,
                    'email' => $mobilePhone . '@temp-saabisu.com', // 臨時 Email，之後可讓用戶修改
                    'password' => Hash::make(Str::random(16)), // 創建隨機密碼
                    'name' => 'User_' . substr($mobilePhone, -4), // 預設名稱
                    'created_by' => null, // 或系統預設 ID
                    'updated_by' => null,
                    'shop_brand_id' => null, // 主控台用戶不直接隸屬特定品牌，除非有此設計
                    'line_auth_code' => $line_auth_code,
                    'country_calling_code' => '+886', // 預設或從請求獲取
                    'last_visited_at' => now(),
                    'current_points' => 0, // 新用戶初始積分
                    'status' => 0, // 正常狀態
                    'permission' => 0, // 預設為普通用戶
                    'email_verified_at' => null, // 手機驗證後再標記
                ]);
                $isNewUser = true;
            } elseif ($line_auth_code && $user->line_auth_code !== $line_auth_code) {
                // 如果用戶已存在，但 line_auth_code 不同，則更新
                $user->line_auth_code = $line_auth_code;
                $user->save();
            }

            $verificationCode = random_int(100000, 999999);
            // $verificationCode = '000000'; // 測試用

            DB::table('sms_verifications')->insert([
                'mobile_phone' => $mobilePhone,
                'verification_code' => $verificationCode,
                'is_verified' => false,
                'user_id' => $user->id, 
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 發送 SMS

            DB::commit();

            $responseMessage = $isNewUser ? 
                'Verification code sent successfully. New user created pending verification.' :
                'Verification code sent successfully to existing user.';

            $responseCode = $isNewUser ? 2002 : 2001;

            return apiResponse($responseCode, ['verification_code' => $verificationCode], $responseMessage, 200);

        } catch (\Exception $e) {
            DB::rollBack(); 
            return apiResponse(5001, null, 'Error processing your request: ' . $e->getMessage(), 500);
        }
    }
}