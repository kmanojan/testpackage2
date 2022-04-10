<?php

namespace Apptimus\Transaction;
use Illuminate\Support\ServiceProvider;

class TransactionServiceProvider extends ServiceProvider{

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    public function register(){

    }
}
