<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class SmsAuthController extends Controller
{
    /**
     * Verify SMS Code.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request): \Illuminate\Http\JsonResponse
    {
        $mobilePhone = $request->input('mobile');
        $verificationCode = $request->input('verifyCode');

        // Validate input
        if (empty($mobilePhone) || empty($verificationCode)) {
            return apiResponse(4001, null, 'Mobile phone and verification code are required.', 400);
        }

        if (!preg_match('/^[0-9]{10,15}$/', $mobilePhone)) {
            return apiResponse(4002, null, 'Invalid mobile phone number format.', 400);
        }

        if (!preg_match('/^[0-9]{6}$/', $verificationCode)) {
            return apiResponse(4003, null, 'Invalid verification code format.', 400);
        }

        // Allow "000000" as a test code
        if ($verificationCode === '000000') {
            // Proceed with the verification as if the code is valid for testing
            return $this->processVerification($mobilePhone);
        }

        // Check database for matching verification code
        $smsVerification = DB::table('sms_verifications')
            ->where('mobile_phone', $mobilePhone)
            ->where('verification_code', $verificationCode)
            ->where('is_verified', false)
            ->first();
        
        if (!$smsVerification) {
            return apiResponse(4004, null, 'Invalid or expired verification code.', 400);
        }

        // Proceed with the verification
        return $this->processVerification($mobilePhone, $smsVerification);
    }

    /**
     * Process the verification logic.
     *
     * @param string $mobilePhone
     * @param object|null $smsVerification
     * @return \Illuminate\Http\JsonResponse
     */
    private function processVerification(string $mobilePhone, $smsVerification = null): \Illuminate\Http\JsonResponse
    {
        // Generate a bearer token
        $token = Str::random(60);

        $user = DB::table('users')
            ->where('mobile_phone', $mobilePhone)
            ->first();

        if (!$user) {
            return apiResponse(4005, null, 'User not found.', 400);
        }

        // Update user's token
        DB::table('users')
            ->where('mobile_phone', $mobilePhone)
            ->update([
                'token' => $token,
                'updated_at' => now(),
            ]);

        // If not a test code, mark the verification code as verified
        if ($smsVerification) {
            DB::table('sms_verifications')
                ->where('id', $smsVerification->id)
                ->update([
                    'is_verified' => true,
                    'updated_at' => now(),
                ]);
        }

        // Return the response with token and userId
        return apiResponse(2001, [
            'token' => $token,
            'userId' => $user->id
        ], 'Verification successful.', 200);
    }
}
