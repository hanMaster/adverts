<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private Connection $connection;

    public function __construct(DatabaseManager $manager)
    {
        $this->connection = $manager->connection();
    }

    public function paymentReceived(Request $request): PaymentResponse
    {
        if (!$this->isSignatureCorrect($request)) {
            return new PaymentResponse(false, "Parameters are incorrect", 422);
        }
        if (!$this->isAdvertAvailable($request->input('item_id'))) {
            return new PaymentResponse(false, "Parameters are incorrect");
        }

        $this->connection->table('payments')->insert([
            'transaction_id' => $request->input('transaction_id'),
            'advert_id' => $request->input('item_id'),
            'site_id' => $request->input('site_id'),
            'amount' => $request->input('amount'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $advert = $this->connection->table('adverts')
            ->where('id', $request->input('item_id'))
            ->first(['id', 'balance']);
        $this->connection->table('adverts')
            ->where('id', $advert->id)
            ->update(['balance' => $advert->balance + $request->input('amount')]);
        return new PaymentResponse(true, "All set");
    }

    function isAdvertAvailable(int $id): bool
    {
        $advert = $this->connection->table('adverts')
            ->where('adverts.id', '=', $id)
            ->get()
            ->first();

        return !!$advert;
    }

    function isSignatureCorrect(Request $request): bool
    {
        $signature = $request->input('signature');
        $key = env('APP_KEY');
        $transaction = $request->input('transaction_id');
        $item = $request->input('item_id');
        $site = $request->input('site_id');
        $amount = $request->input('amount');
        $str = $key . "&transaction=" . $transaction . "&item_id=" . $item . "&site_id=" . $site . "&amount=" . $amount;
        return sha1($str) === $signature;
    }
}
