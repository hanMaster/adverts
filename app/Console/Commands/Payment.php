<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;

class Payment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'charge:hourly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ежечасное списание оплаты за объявления';

    protected Connection $connection;

    public function __construct(DatabaseManager $manager)
    {
        $this->connection = $manager->connection();
        parent::__construct();
    }

    public function handle(): void
    {
        $query = "UPDATE adverts, categories
                  SET adverts.balance = adverts.balance - categories.price
                  WHERE adverts.category_id = categories.id and adverts.balance >= categories.price";
        $this->connection->update($query);
    }
}
