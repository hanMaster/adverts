<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\JsonResponse;

class PaymentResponse extends JsonResponse
{
    public function __construct(bool $success, string $message = '', $status = '200')
    {
        parent::__construct([
            'success' => $success,
            'message' => $message
        ], $status);
    }
}
