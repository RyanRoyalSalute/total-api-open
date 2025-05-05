<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Ecpay\Sdk\Factories\Factory;
use Ecpay\Sdk\Services\UrlService;
use yidas\linePay\Client;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $linePayClient;

    public function __construct()
    {
        $this->initializeLinePayClient();
    }

    protected function initializeLinePayClient(): void
    {
        $this->linePayClient = new Client([
            'channelId' => config('services.linepay.channel_id'),
            'channelSecret' => config('services.linepay.channel_secret'),
            'isSandbox' => config('services.linepay.is_sandbox'),
        ]);
    }

    public function generateTradeNo(): string
    {
        $base = now()->format('ymdHis');
        $tradeNo = DB::transaction(function () use ($base) {
            $count = DB::table('payment_records')
                ->where('trade_no', '>=', $base . '01')
                ->where('trade_no', '<=', $base . '99')
                ->count();
            $sequence = $count + 1;
            if ($sequence > 99) {
                sleep(1);
                return $this->generateTradeNo();
            }

            $tradeNo = $base . str_pad($sequence, 2, '0', STR_PAD_LEFT);

            if (DB::table('payment_records')->where('trade_no', $tradeNo)->exists()) {
                return $this->generateTradeNo();
            }

            return $tradeNo;
        });

        Log::info('Generated Trade No:', ['trade_no' => $tradeNo]);
        return $tradeNo;
    }

    public function insertPaymentRecord(array $paymentData): int
    {
        $recordId = DB::table('payment_records')->insertGetId($paymentData);
        Log::info('Inserted Payment Record:', [
            'record_id' => $recordId,
            'payment_data' => $paymentData,
        ]);
        return $recordId;
    }

    public function generateECPayForm(array $paymentData): string
    {
        $input = [
            'MerchantID'        => config('services.ecpay.merchant_id'),
            'MerchantTradeNo'   => $paymentData['trade_no'],
            'MerchantTradeDate' => now()->format('Y/m/d H:i:s'),
            'PaymentType'       => 'aio',
            'TotalAmount'       => $paymentData['transaction_amount'],
            'TradeDesc'         => UrlService::ecpayUrlEncode('交易描述範例'),
            'ItemName'          => $paymentData['item_name'],
            'ChoosePayment'     => 'Credit',
            'EncryptType'       => 1,
            'ReturnURL'         => config('services.ecpay.return_url'),
            'ClientBackURL'     => config('services.ecpay.client_back_url'),
        ];

        Log::info('Generating ECPay Form:', ['input' => $input]);

        $factory = new Factory([
            'hashKey' => config('services.ecpay.hash_key'),
            'hashIv'  => config('services.ecpay.hash_iv'),
        ]);
        $autoSubmitFormService = $factory->create('AutoSubmitFormWithCmvService');

        $action = config('services.ecpay.server');

        $form = $autoSubmitFormService->generate($input, $action);
        Log::info('Generated ECPay Form:', ['form' => $form]);
        return $form;
    }

    public function generateLinePayRequest(array $paymentData): array
    {
        $requestData = [
            'amount' => $paymentData['transaction_amount'],
            'currency' => 'TWD',
            'orderId' => $paymentData['trade_no'],
            'packages' => [
                [
                    'id' => 'pkg_' . $paymentData['trade_no'],
                    'amount' => $paymentData['transaction_amount'],
                    'name' => $paymentData['item_name'],
                    'products' => [
                        [
                            'name' => $paymentData['item_name'],
                            'quantity' => 1,
                            'price' => $paymentData['transaction_amount'],
                        ],
                    ],
                ],
            ],
            'redirectUrls' => [
                'confirmUrl' => config('services.linepay.confirm_url'),
                'cancelUrl' => config('services.linepay.cancel_url'),
            ],
        ];

        Log::info('LINE Pay Request:', $requestData);

        $response = $this->linePayClient->request($requestData);

        if (!$response->isSuccessful()) {
            Log::error('LINE Pay Request Failed:', [
                'returnCode' => $response['returnCode'],
                'returnMessage' => $response['returnMessage'],
            ]);
            throw new \Exception("LINE Pay Error: {$response['returnCode']} - {$response['returnMessage']}");
        }

        $result = [
            'payment_url' => $response->getPaymentUrl(),
            'transaction_id' => $response['info']['transactionId'],
        ];

        Log::info('LINE Pay Request Successful:', $result);
        return $result;
    }

    public function confirmLinePayPayment(string $transactionId, float $amount): void
    {
        $confirmData = [
            'amount' => $amount,
            'currency' => 'TWD',
        ];

        Log::info('LINE Pay Confirm Request:', [
            'transactionId' => $transactionId,
            'confirmData' => $confirmData,
        ]);

        $response = $this->linePayClient->confirm($transactionId, $confirmData);

        if (!$response->isSuccessful()) {
            Log::error('LINE Pay Confirmation Failed:', [
                'returnCode' => $response['returnCode'],
                'returnMessage' => $response['returnMessage'],
            ]);
            throw new \Exception("LINE Pay Confirmation Error: {$response['returnCode']} - {$response['returnMessage']}");
        }

        Log::info('LINE Pay Confirmation Successful:', [
            'transactionId' => $transactionId,
            'response' => $response->toArray(),
        ]);
    }
}