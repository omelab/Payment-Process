<?php

namespace App\Services;

use Square\SquareClient;
use Square\Models\Money;
use Square\Models\CreatePaymentRequest;
use Exception;

class SquareService
{
    protected $client;

    public function __construct()
    {
        $this->client = new SquareClient([
            'accessToken' => config('services.square.access_token'),
            'environment' => config('services.square.environment'), // sandbox or production
        ]);
    }

    public function processPayment($nonce, $amount)
    {
        $paymentsApi = $this->client->getPaymentsApi();

        $money = new Money();
        $money->setAmount($amount); // Amount in cents (e.g., $5.00 is 500)
        $money->setCurrency('USD');

        $createPaymentRequest = new CreatePaymentRequest(
            $nonce,                // Payment nonce from the frontend
            uniqid()               // Idempotency key (a unique identifier for the request)
        );

        // Set the amount and currency in the request body
        $createPaymentRequest->setAmountMoney($money);

        try {
            $response = $paymentsApi->createPayment($createPaymentRequest);
            if ($response->isSuccess()) {
                return $response->getResult()->getPayment();
            } else {
                $errors = $response->getErrors();
                throw new Exception('Payment failed: ' . $errors[0]->getDetail());
            }
        } catch (Exception $e) {
            throw new Exception('Payment processing error: ' . $e->getMessage());
        }
    }
}