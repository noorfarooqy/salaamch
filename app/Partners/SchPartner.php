<?php

namespace Noorfarooqy\Salaamch\Partners;

abstract class SchPartner
{
    /**
     * Query Bank Rate
     * 
     * This function is used to query the bank rate for a given partner.
     * 
     * @return object The bank rate
     */
    abstract public function QueryBankRate(): object;

    /**
     * Verify Partner Acount
     *
     * This function is used to verify the partner's bank account.
     *
     * @return object
     */
    abstract public function VerifyPartnerAcount(): object;


    /**
     * Query Transaction Status
     *
     * This function is used to query the status of a transaction.
     *
     * @return object
     */
    abstract public function QueryTransactionStatus(): object;

    /**
     * Deposit Into Account
     *
     * This function is used to deposit money into the partner's bank account.
     *
     * @return object
     */
    abstract public function DepositIntoAccount(): object;
}
