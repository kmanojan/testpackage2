<?php

use Apptimus\Transaction\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => "Apptimus\Transaction\Http\Controllers"],function(){
    Route::get('test', [TransactionController::class,'index']);
});

