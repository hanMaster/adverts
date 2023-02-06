<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPayment;
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
        $dto = new PaymentDto($request);

        if ($this->processedTransaction($dto->transaction_id)) {
            return new PaymentResponse(true);
        }

        if (!$this->isValidAdvert($dto->advert_id)) {
            return new PaymentResponse(false);
        }

        ProcessPayment::dispatch($dto);
        return new PaymentResponse(true);
    }

    function isValidAdvert(int $id): bool
    {
        $advert = $this->connection->table('adverts')
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
