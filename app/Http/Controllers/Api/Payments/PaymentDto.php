<?php

namespace App\Http\Controllers\Api\Payments;

use Illuminate\Http\Request;

class PaymentDto
{
    public string $transaction_id;
    public int $advert_id;
    public string $site_id;
    public float $amount;
    public string $signature;

    public function __construct(Request $request )
    {
        $this->transaction_id = $request->input('transaction_id');
        $this->advert_id = (int)$request->input('item_id');
        $this->site_id = $request->input('site_id');
        $this->amount = (float)$request->input('amount');
        $this->signature = $request->input('signature');
    }
}
