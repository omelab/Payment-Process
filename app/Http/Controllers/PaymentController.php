<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SquareService;
use Exception;

class PaymentController extends Controller
{
    protected $squareService;

    public function __construct(SquareService $squareService)
    {
        $this->squareService = $squareService;
    }

    public function squarePaymentProcess(Request $request)
    {
        $request->validate([
            'nonce' => 'required',
            'amount' => 'required|numeric|min:1',
        ]);

        try {
            $payment = $this->squareService->processPayment($request->nonce, $request->amount * 100);
            return response()->json(['success' => true, 'payment' => $payment]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    public function squarePaymentForm()
    {
        return view('square_payment');
    }
}