<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Services\PaymentUtilService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;

class LinePayCallbackController extends Controller
{
    protected $paymentService;
    protected $paymentUtilService;

    public function __construct(PaymentService $paymentService, PaymentUtilService $paymentUtilService)
    {
        $this->paymentService = $paymentService;
        $this->paymentUtilService = $paymentUtilService;

        Log::channel('linepay')->debug('LinePayCallbackController initialized', [
            'payment_service' => get_class($paymentService),
            'payment_util_service' => get_class($paymentUtilService),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    public function handleConfirm(Request $request): RedirectResponse
    {
        $transactionId = $request->query('transactionId');
        
        Log::channel('linepay')->info('Confirm callback received for transaction', [
            'transactionId' => $transactionId,
            'request_data' => $request->all(),
            'timestamp' => now()->toDateTimeString()
        ]);

        if (!$transactionId) {
            Log::channel('linepay')->error('Missing transaction ID in confirm callback', [
                'transactionId' => $transactionId,
                'request_data' => $request->all(),
                'timestamp' => now()->toDateTimeString()
            ]);
            return redirect()->to("https://liff.line.me/2006765838-Y4pyXLx8");
        }

        $payment = DB::table('payment_records')->where('order_3rd_no', $transactionId)->first();
        if (!$payment) {
            Log::channel('linepay')->error('Payment record not found for transaction', [
                'transactionId' => $transactionId,
                'timestamp' => now()->toDateTimeString()
            ]);
            return redirect()->to("https://liff.line.me/2006765838-Y4pyXLx8");
        }

        Log::channel('linepay')->debug('Payment record retrieved for confirm', [
            'transactionId' => $transactionId,
            'payment_record' => (array)$payment,
            'timestamp' => now()->toDateTimeString()
        ]);

        if ($payment->payment_method !== 'LINE_PAY') {
            Log::channel('linepay')->error('Invalid payment method for transaction', [
                'transactionId' => $transactionId,
                'payment_method' => $payment->payment_method,
                'expected_method' => 'LINE_PAY',
                'payment_record' => (array)$payment,
                'timestamp' => now()->toDateTimeString()
            ]);
            return redirect()->to("https://liff.line.me/2006765838-Y4pyXLx8");
        }

        if ($payment->is_paid) {
            Log::channel('linepay')->error('Payment already confirmed for transaction', [
                'transactionId' => $transactionId,
                'is_paid' => $payment->is_paid,
                'payment_record' => (array)$payment,
                'timestamp' => now()->toDateTimeString()
            ]);
            return redirect()->to("https://liff.line.me/2006765838-Y4pyXLx8");
        }

        DB::beginTransaction();
        try {
            Log::channel('linepay')->info('Confirming LinePay payment for transaction', [
                'transactionId' => $transactionId,
                'amount' => $payment->transaction_amount,
                'payment_record' => (array)$payment,
                'timestamp' => now()->toDateTimeString()
            ]);

            $this->paymentService->confirmLinePayPayment($transactionId, $payment->transaction_amount);
            Log::channel('linepay')->debug('LinePay payment confirmed via PaymentService', [
                'transactionId' => $transactionId,
                'amount' => $payment->transaction_amount,
                'payment_record' => (array)$payment,
                'timestamp' => now()->toDateTimeString()
            ]);

            $updated = DB::table('payment_records')
                ->where('order_3rd_no', $transactionId)
                ->update(['is_paid' => true, 'updated_at' => now()]);
            if (!$updated) {
                Log::channel('linepay')->error('Failed to update payment record for transaction', [
                    'transactionId' => $transactionId,
                    'update_data' => ['is_paid' => true, 'updated_at' => now()->toDateTimeString()],
                    'payment_record' => (array)$payment,
                    'timestamp' => now()->toDateTimeString()
                ]);
                throw new \Exception('Failed to update payment record.');
            }

            Log::channel('linepay')->debug('Payment record updated for transaction', [
                'transactionId' => $transactionId,
                'update_data' => ['is_paid' => true, 'updated_at' => now()->toDateTimeString()],
                'payment_record' => (array)$payment,
                'timestamp' => now()->toDateTimeString()
            ]);

            $this->paymentUtilService->processPaymentSuccess((array)$payment);
            Log::channel('linepay')->debug('Payment success processed for transaction', [
                'transactionId' => $transactionId,
                'payment_record' => (array)$payment,
                'timestamp' => now()->toDateTimeString()
            ]);

            DB::commit();
            Log::channel('linepay')->info('Payment confirmed successfully for transaction', [
                'transactionId' => $transactionId,
                'payment_record' => (array)$payment,
                'timestamp' => now()->toDateTimeString()
            ]);
            return redirect()->to("https://liff.line.me/2006765838-Y4pyXLx8");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('linepay')->error('Payment confirmation failed for transaction', [
                'transactionId' => $transactionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payment_record' => (array)$payment,
                'timestamp' => now()->toDateTimeString()
            ]);
            return redirect()->to("https://liff.line.me/2006765838-Y4pyXLx8");
        }
    }

    public function handleCancel(Request $request): RedirectResponse
    {
        $transactionId = $request->query('transactionId');

        Log::channel('linepay')->info('Cancel callback received for transaction', [
            'transactionId' => $transactionId,
            'request_data' => $request->all(),
            'timestamp' => now()->toDateTimeString()
        ]);

        if (!$transactionId) {
            Log::channel('linepay')->error('Missing transaction ID in cancel callback', [
                'transactionId' => $transactionId,
                'request_data' => $request->all(),
                'timestamp' => now()->toDateTimeString()
            ]);
            return redirect()->to("https://liff.line.me/2006765838-Y4pyXLx8");
        }

        $payment = DB::table('payment_records')->where('order_3rd_no', $transactionId)->first();
        if (!$payment) {
            Log::channel('linepay')->error('Payment record not found for transaction', [
                'transactionId' => $transactionId,
                'timestamp' => now()->toDateTimeString()
            ]);
            return redirect()->to("https://liff.line.me/2006765838-Y4pyXLx8");
        }

        Log::channel('linepay')->debug('Payment record retrieved for cancellation', [
            'transactionId' => $transactionId,
            'payment_record' => (array)$payment,
            'timestamp' => now()->toDateTimeString()
        ]);

        if ($payment->is_paid) {
            Log::channel('linepay')->error('Payment already confirmed, cannot cancel transaction', [
                'transactionId' => $transactionId,
                'is_paid' => $payment->is_paid,
                'payment_record' => (array)$payment,
                'timestamp' => now()->toDateTimeString()
            ]);
            return redirect()->to("https://liff.line.me/2006765838-Y4pyXLx8");
        }

        DB::beginTransaction();
        try {
            Log::channel('linepay')->info('Releasing blocked seats and deleting payment record for transaction', [
                'transactionId' => $transactionId,
                'session_id' => $payment->session_id,
                'attendance' => $payment->attendance,
                'payment_record' => (array)$payment,
                'timestamp' => now()->toDateTimeString()
            ]);

            $this->paymentUtilService->releaseBlockedSeats($payment->session_id, $payment->attendance);
            Log::channel('linepay')->debug('Blocked seats released for transaction', [
                'transactionId' => $transactionId,
                'session_id' => $payment->session_id,
                'attendance' => $payment->attendance,
                'payment_record' => (array)$payment,
                'timestamp' => now()->toDateTimeString()
            ]);

            $deleted = DB::table('payment_records')
                ->where('order_3rd_no', $transactionId)
                ->delete();
            if (!$deleted) {
                Log::channel('linepay')->error('Failed to delete payment record for transaction', [
                    'transactionId' => $transactionId,
                    'payment_record' => (array)$payment,
                    'timestamp' => now()->toDateTimeString()
                ]);
                throw new \Exception('Failed to delete payment record.');
            }

            Log::channel('linepay')->debug('Payment record deleted for transaction', [
                'transactionId' => $transactionId,
                'payment_record' => (array)$payment,
                'timestamp' => now()->toDateTimeString()
            ]);

            DB::commit();
            Log::channel('linepay')->info('Payment cancelled successfully for transaction', [
                'transactionId' => $transactionId,
                'session_id' => $payment->session_id,
                'timestamp' => now()->toDateTimeString()
            ]);
            return redirect()->to("https://liff.line.me/2006765838-Y4pyXLx8");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('linepay')->error('Cancellation failed for transaction', [
                'transactionId' => $transactionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payment_record' => (array)$payment,
                'timestamp' => now()->toDateTimeString()
            ]);
            return redirect()->to("https://liff.line.me/2006765838-Y4pyXLx8");
        }
    }
}