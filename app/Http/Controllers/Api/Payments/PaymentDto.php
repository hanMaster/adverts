<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Requests\PaymentRequest;

class PaymentDto
{
    public string $transaction_id;
    public int $advert_id;
    public string $site_id;
    public float $amount;
    public string $signature;

    public function __construct(PaymentRequest $request)
    {
        $this->transaction_id = $request->input('transaction_id');
        $this->advert_id = (int)$request->input('item_id');
        $this->site_id = $request->input('site_id');
        $this->amount = (int)($request->input('amount') * 100);
        $this->signature = $request->input('signature');
    }
}
