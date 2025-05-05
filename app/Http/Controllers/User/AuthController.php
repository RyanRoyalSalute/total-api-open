<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Handle authentication based on input parameters.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse, 2000: valid, 2001: signIn, 2002: signUp
     */
    public function signIn(Request $request): \Illuminate\Http\JsonResponse
    {
        $mobilePhone = $request->input('mobile');
        $token = $request->input('token');
        $line_auth_code = $request->input('line_auth_code');

        // Validate input
        if (empty($mobilePhone) || empty($line_auth_code)) {
            return apiResponse(4001, null, 'Mobile phone or line code required.', 400);
        }

        if (!preg_match('/^[0-9]{10,15}$/', $mobilePhone)) {
            return apiResponse(4002, null, 'Invalid mobile phone number format.', 400);
        }

        // Case 2: If token is provided
        if (!empty($token)) {
            $user = DB::table('users')->where('mobile_phone', $mobilePhone)->where('token', $token)->first();

            if ($user) {
                return apiResponse(2000, null, 'Token verified successfully.', 200);
            }
        }

        // Case 1: Proceed to send SMS verification
        $user = DB::table('users')->where('mobile_phone', $mobilePhone)->first();

        // Generate verification code
        $verificationCode = random_int(100000, 999999);

        DB::beginTransaction();
        try {
            
            if (!$user) {
                $userId = DB::table('users')->insertGetId([
                    'mobile_phone' => $mobilePhone,
                    'created_by' => 2204,
                    'updated_by' => 2204,
                    'shop_brand_id' => 1,
                    'line_auth_code' => $line_auth_code,
                    'country_calling_code' => '+886',
                    'last_visited_at' => now(),
                    'current_points' => 30,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $userId = $user->id;
            }

            // Insert SMS verification record
            DB::table('sms_verifications')->insert([
                'mobile_phone' => $mobilePhone,
                'verification_code' => $verificationCode,
                'is_verified' => false,
                'user_id' => $userId, 
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Send SMS with verification code
            sendSms($mobilePhone, $verificationCode);

            DB::commit();

            if ($user) {
                return apiResponse(2001, ['verification_code' => $verificationCode], 'Verification code sent successfully to existing user.', 200);
            } else {
                return apiResponse(2002, ['verification_code' => $verificationCode], 'Verification code sent successfully. New user created.', 200);
            }
        } catch (\Exception $e) {
            DB::rollBack(); 
            return apiResponse(5001, null, 'Error processing your request: ' . $e->getMessage(), 500);
        }
    }
}
