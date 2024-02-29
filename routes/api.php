<?php


use Illuminate\Support\Facades\Route;
use Noorfarooqy\Salaamch\Controllers\ApiSchController;


Route::middleware(['auth:sanctum'])->group(function () {


    Route::group(['prefix' => '/v1/sch/partner/', 'as' => 'sch.'], function () {
        Route::post('/new', [ApiSchController::class, 'registerPartner'])->name('register');
        Route::post('/verify', [ApiSchController::class, 'verifyAccount'])->name('verify');
        Route::post('/deposit', [ApiSchController::class, 'depositAccount'])->name('deposit');
        Route::post('/status', [ApiSchController::class, 'transactionStatus'])->name('status');
    });
});
