<?php


namespace Noorfarooqy\Salaamch\Partners;

use Noorfarooqy\NoorAuth\Traits\ResponseHandler;

class SalaamKenya extends SchPartner
{
    use ResponseHandler;
    public function DepositIntoAccount(): object
    {
        return $this->getResponse();
    }

    public function QueryBankRate(): object
    {
        return $this->getResponse();
    }

    public function QueryTransactionStatus(): object
    {
        return $this->getResponse();
    }

    public function VerifyPartnerAcount(): object
    {
        return $this->getResponse();
    }
} {
}
