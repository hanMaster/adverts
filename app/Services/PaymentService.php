<?php

namespace App\Services;

use App\Http\Controllers\Api\Payments\PaymentDto;
use App\Http\Controllers\Api\Payments\PaymentResponse;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    private Connection $connection;

    public function __construct(DatabaseManager $manager)
    {
        $this->connection = $manager->connection();
    }

    public function proceedPayment(PaymentDto $dto): PaymentResponse
    {
        if ($this->processedTransaction($dto->transaction_id)) {
            return new PaymentResponse(true);
        }

        if (!$this->isValidAdvert($dto->advert_id)) {
            return new PaymentResponse(false);
        }
        if (!$this->isSignatureCorrect($dto)) {
            return new PaymentResponse(false, 422);
        }
        try {
            $this->connection->beginTransaction();

            $this->connection->table('payments')->insert([
                'transaction_id' => $dto->transaction_id,
                'advert_id' => $dto->advert_id,
                'site_id' => $dto->site_id,
                'amount' => $dto->amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $advert = $this->connection->table('adverts')
                ->where('id', $dto->advert_id)
                ->first(['id', 'balance']);
            $this->connection->table('adverts')
                ->where('id', $advert->id)
                ->update(['balance' => $advert->balance + $dto->amount]);

            $this->connection->commit();

        } catch (\Throwable $e) {
            $this->connection->rollBack();
            return new PaymentResponse(false, 500);
        }
        return new PaymentResponse(true);
    }

    function isSignatureCorrect(PaymentDto $dto): bool
    {
        $signature = $dto->signature;
        $key = env('APP_KEY');
        $transaction = $dto->transaction_id;
        $item = $dto->advert_id;
        $site = $dto->site_id;
        $amount = $dto->amount;
        $str = $key . "&transaction=" . $transaction . "&item_id=" . $item . "&site_id=" . $site . "&amount=" . $amount;
        dump(sha1($str));
        return sha1($str) === $signature;
    }

    function isValidAdvert(int $id): bool
    {
        $advert = DB::connection()->table('adverts')
            ->where('adverts.id', '=', $id)
            ->get()
            ->first();

        return !!$advert;
    }

    function processedTransaction(string $tx_id): bool
    {
        $tx = $this->connection->table('payments')
            ->where('payments.transaction_id', '=', $tx_id)
            ->get()
            ->first();

        return !!$tx;
    }

}
