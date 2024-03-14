<?php


use Illuminate\Support\Facades\Route;
use Noorfarooqy\Salaamch\Controllers\ApiSchController;


Route::middleware(['auth:sanctum'])->group(function () {


    Route::group(['prefix' => '/v1/sch/partner/', 'as' => 'sch.'], function () {
        Route::post('/new', [ApiSchController::class, 'registerPartner'])->name('register');
        Route::post('/verify', [ApiSchController::class, 'verifyAccount'])->name('verify');
        Route::post('/transaction/withdraw', [ApiSchController::class, 'withdrawFromAccount'])->name('withdraw');
        Route::post('/transaction/deposit', [ApiSchController::class, 'depositIntoAccount'])->name('deposit');
        Route::post('/transaction/status', [ApiSchController::class, 'schTransactionStatus'])->name('status');
        Route::post('/transaction/reverse', [ApiSchController::class, 'postAccountReverseTransaction'])->name('reverse');
    });
});
