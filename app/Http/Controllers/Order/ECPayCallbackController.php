<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ecpay\Sdk\Factories\Factory;
use Ecpay\Sdk\Response\VerifiedArrayResponse;
use App\Services\TicketService;
use App\Services\PaymentUtilService;
use App\Http\Controllers\Controller;

class ECPayCallbackController extends Controller
{
    protected $ticketService;
    protected $paymentUtilService;

    public function __construct(TicketService $ticketService, PaymentUtilService $paymentUtilService)
    {
        $this->ticketService = $ticketService;
        $this->paymentUtilService = $paymentUtilService;
    }

    public function handleCallback(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validatedData = $this->validateEcpayCallback($request);

            if (!$validatedData) {
                return apiResponse(4001, null, 'Invalid callback data.', 400);
            }

            $merchantTradeNo = $validatedData['MerchantTradeNo'];
            $tradeStatus = $validatedData['RtnCode'];

            return $tradeStatus === '1'
                ? $this->processPaymentSuccess($merchantTradeNo)
                : $this->handlePaymentFailure($merchantTradeNo);
        } catch (\Exception $e) {
            return apiResponse(5001, null, 'Callback processing failed: ' . $e->getMessage(), 500);
        }
    }

    private function validateEcpayCallback(Request $request): ?array
    {
        $factory = new Factory([
            'hashKey' => config('services.ecpay.hash_key'),
            'hashIv'  => config('services.ecpay.hash_iv'),
        ]);

        $checkoutResponse = $factory->create(VerifiedArrayResponse::class);

        return $checkoutResponse->get($request->all());
    }

    private function processPaymentSuccess(string $merchantTradeNo): \Illuminate\Http\JsonResponse
    {
        $payment = DB::table('payment_records')->where('order_3rd_no', $merchantTradeNo)->first();
        if (!$payment) {
            return apiResponse(4004, null, 'Payment record not found.', 400);
        }

        if ($payment->payment_method !== 'CREDIT_CARD') {
            return apiResponse(4005, null, 'Invalid payment method for this callback.', 400);
        }

        if ($payment->is_paid) {
            return apiResponse(4006, null, 'Payment already confirmed.', 400);
        }

        DB::beginTransaction();
        try {
            $updated = DB::table('payment_records')
                ->where('order_3rd_no', $merchantTradeNo)
                ->update(['is_paid' => true, 'updated_at' => now()]);
            if (!$updated) {
                throw new \Exception('Failed to update payment record.');
            }

            $this->paymentUtilService->processPaymentSuccess((array)$payment);

            DB::commit();
            return apiResponse(2000, null, 'Payment successful and tickets generated.', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return apiResponse(5002, null, 'Payment processing failed: ' . $e->getMessage(), 500);
        }
    }

    private function handlePaymentFailure(string $merchantTradeNo): \Illuminate\Http\JsonResponse
    {
        $payment = DB::table('payment_records')->where('order_3rd_no', $merchantTradeNo)->first();
        if (!$payment) {
            return apiResponse(4004, null, 'Payment record not found.', 400);
        }

        if ($payment->is_paid) {
            return apiResponse(4006, null, 'Payment already confirmed, cannot process failure.', 400);
        }

        DB::beginTransaction();
        try {
            $this->paymentUtilService->releaseBlockedSeats($payment->session_id, $payment->attendance);
            DB::table('payment_records')
                ->where('order_3rd_no', $merchantTradeNo)
                ->delete();

            DB::commit();
            return apiResponse(4002, ['merchant_trade_no' => $merchantTradeNo], 'Payment failed.', 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return apiResponse(5003, null, 'Failure processing failed: ' . $e->getMessage(), 500);
        }
    }
}