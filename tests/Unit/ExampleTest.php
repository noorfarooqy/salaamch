<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Noorfarooqy\Salaamch\Services\SalaamPartnerServices;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;

test('example', function () {
    expect(true)->toBeTrue();
});


test('confirm environment is set to testing', function () {
    expect(config('app.env'))->toBe('testing');
});

test('confirm environment is has sch root api', function () {
    Config::set('salaamch.endpoints.root', env('SCH_API_ROOT'));
    expect(env('SCH_API_ROOT'))->toBe(Config::get('salaamch.endpoints.root'));
});

test('test-verification-requires-account-number', function () {
    $request = new Request();
    $service = new SalaamPartnerServices();
    $response = $service->verifyPartnerAccount($request);
    // echo json_encode($response->original);
    expect($response)->original->error_message->toBe("The bank account field is required.");
    // expect(json_encode($response)->original)->();
});

test('test-verification-account-number-must-be-number', function () {
    $request = new Request();
    $request = $request->merge(['bank_account' => 'abcd']);
    $service = new SalaamPartnerServices();
    $response = $service->verifyPartnerAccount($request);
    // echo json_encode($response->original);
    expect($response)->original->error_message->toBe("The bank account field must be a number.");
    // expect(json_encode($response)->original)->();
});

test('test-verification-accepts-account-number', function () {

    Config::set('salaamch.endpoints.root', env('SCH_API_ROOT'));
    $request = new Request();
    $request = $request->merge(['bank_account' => '1234567']);
    $service = new SalaamPartnerServices();
    $response = $service->verifyPartnerAccount($request);
    // echo json_encode($response->original);
})->throws(Exception::class);
