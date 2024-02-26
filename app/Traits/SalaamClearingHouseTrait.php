<?php

namespace Noorfarooqy\Salaamch\Traits;

use Noorfarooqy\BankGateway\Services\BankServices;
use Noorfarooqy\Salaamch\Helpers\ErrorCodes;

trait SalaamClearingHouseTrait
{
    public $bank;

    public function initializeSalaamClearingHouse()
    {
        $gateway_key = config('bankgateway.configured_gateway');
        $bank_class = config('bankgateway.bank_gateways')[$gateway_key];
        $this->bank = new \ReflectionClass($bank_class);
    }
    public function generateChecksum($data)
    {
        return md5(collect($data)->sortKeys()->join(','));
    }
}
