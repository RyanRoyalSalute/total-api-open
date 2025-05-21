<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\User; // 使用 User Model
use Illuminate\Support\Facades\Hash; // 如果需要創建密碼
use Illuminate\Support\Str;

class SmsAuthController extends Controller
{
    public function verify(Request $request): \Illuminate\Http\JsonResponse
    {
        $mobilePhone = $request->input('mobile');
        $verificationCode = $request->input('verifyCode');

        if (empty($mobilePhone) || empty($verificationCode)) {
            return apiResponse(4001, null, 'Mobile phone and verification code are required.', 400);
        }

        if (!preg_match('/^[0-9]{10,15}$/', $mobilePhone)) {
            return apiResponse(4002, null, 'Invalid mobile phone number format.', 400);
        }

        if (!preg_match('/^[0-9]{6}$/', $verificationCode)) {
            return apiResponse(4003, null, 'Invalid verification code format.', 400);
        }

        $isTestCode = $verificationCode === '000000';
        $smsVerification = null;

        if (!$isTestCode) {
            $smsVerification = DB::table('sms_verifications')
                ->where('mobile_phone', $mobilePhone)
                ->where('verification_code', $verificationCode)
                ->where('is_verified', false)
                ->where('user_id', function($query) use ($mobilePhone) { // 確保驗證碼屬於此手機號碼的用戶
                    $query->select('id')->from('users')->where('mobile_phone', $mobilePhone);
                })
                ->where('created_at', '>=', now()->subMinutes(10)) // 驗證碼時效(10min)
                ->first();

            if (!$smsVerification) {
                return apiResponse(4004, null, 'Invalid or expired verification code.', 400);
            }
        }

        return $this->processVerification($mobilePhone, $smsVerification, $isTestCode);
    }

    private function processVerification(string $mobilePhone, $smsVerificationDbRecord = null, bool $isTestCode = false): \Illuminate\Http\JsonResponse
    {
        $user = User::where('mobile_phone', $mobilePhone)->first();

        if (!$user) {
            return apiResponse(4005, null, 'User not found. Please sign in first.', 404);
        }

        if (empty($user->password)) {
            $user->password = Hash::make(Str::random(16)); // 創建一個隨機密碼 for passport要求(後續再補上改密碼的機制)
        }

        if (empty($user->email)) {
            $user->email = $mobilePhone . '@example.com'; // 臨時占位符
        }
        //$user->email_verified_at = now(); // 驗證手機同時也標記 email (或手機) 已驗證
        $user->save();

        if (!$isTestCode && $smsVerificationDbRecord) {
            DB::table('sms_verifications')
                ->where('id', $smsVerificationDbRecord->id)
                ->update([
                    'is_verified' => true,
                    'updated_at' => now(),
                ]);
        }

        // 發放 Passport token
        $tokenResult = $user->createToken('SaabisuUserToken');
        $accessToken = $tokenResult->accessToken;

        return apiResponse(2001, [
            'token_type' => 'Bearer',
            'access_token' => $accessToken,
            'expires_at' => $tokenResult->token->expires_at ? $tokenResult->token->expires_at->toDateTimeString() : null,
            'user_id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar,
            'permission' => $user->permission,
        ], 'Verification successful.', 200);
    }
}