<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Services\PaymentService;

class PaymentController extends Controller
{
    private PaymentService $service;

    public function __construct(PaymentService $service)
    {
        $this->service = $service;
    }

    public function paymentReceived(PaymentRequest $request): PaymentResponse
    {

        $dto = new PaymentDto($request);
        return $this->service->proceedPayment($dto);
    }
}
