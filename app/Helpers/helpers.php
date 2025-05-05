<?php

use Illuminate\Support\Facades\Http;

if (!function_exists('apiResponse')) {
    /**
     * Utility function
     *
     * @param int $code The custom response code.
     * @param mixed $data The dynamic data for the response.
     * @param string $message The response message.
     * @param int $httpStatusCode The HTTP status code.
     */
    function apiResponse(int $code, $data = null, string $message = '', int $httpStatusCode = 200)
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $httpStatusCode);
    }
}
if (!function_exists('sendSms')) {
    function sendSms($dstaddr, $smbody, $smsPointFlag = 1)
    {
        $smsConfig = config('services.sms');

        if (empty($smsConfig['username']) || empty($smsConfig['password']) || empty($smsConfig['url'])) {
            return apiResponse(400, null, 'SMS API configuration is missing.', 400);
        }

        $parameters = [
            'CharsetURL' => 'UTF-8',
            'username' => $smsConfig['username'],
            'password' => $smsConfig['password'],
            'dstaddr' => $dstaddr,
            'smbody' => $smbody
        ];

        $response = Http::asForm()->get($smsConfig['url'], $parameters);

        $body = $response->body();

        if (isUnauthorized($body)) {
            return apiResponse(403, null, 'Unauthorized. You are not authorized to access this feature.', 403);
        }

        if ($response->successful()) {
            return apiResponse(200, $body, 'SMS sent successfully.', 200);
        }

        return apiResponse($response->status(), null, $response->body(), $response->status());
    }

    function isUnauthorized(string $body): bool
    {
        return str_contains($body, '403') || str_contains($body, '未被授權存取');
    }
}

