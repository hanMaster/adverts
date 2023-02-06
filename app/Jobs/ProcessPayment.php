<?php

namespace App\Jobs;

use App\Http\Controllers\Api\Payments\PaymentDto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private PaymentDto $dto;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(PaymentDto $param_dto)
    {
        $this->dto = $param_dto;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if (!$this->isSignatureCorrect()) {
            return;
        }
        try {
            DB::beginTransaction();
            dump($this->dto);

            DB::connection()->table('payments')->insert([
                'transaction_id' => $this->dto->transaction_id,
                'advert_id' => $this->dto->advert_id,
                'site_id' => $this->dto->site_id,
                'amount' => $this->dto->amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $advert = DB::connection()->table('adverts')
                ->where('id', $this->dto->advert_id)
                ->first(['id', 'balance']);
            DB::connection()->table('adverts')
                ->where('id', $advert->id)
                ->update(['balance' => $advert->balance + $this->dto->amount]);

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
        }
    }

    function isSignatureCorrect(): bool
    {
        $signature = $this->dto->signature;
        $key = env('APP_KEY');
        $transaction = $this->dto->transaction_id;
        $item = $this->dto->advert_id;
        $site = $this->dto->site_id;
        $amount = $this->dto->amount;
        $str = $key . "&transaction=" . $transaction . "&item_id=" . $item . "&site_id=" . $site . "&amount=" . $amount;
        return sha1($str) === $signature;
    }
}
