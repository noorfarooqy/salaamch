<?php

namespace Noorfarooqy\Salaamch\Controllers;

use Illuminate\Http\Request;
use Noorfarooqy\Salaamch\Controllers\Controller;
use Noorfarooqy\Salaamch\Services\SalaamPartnerServices;

class ApiSchController extends Controller
{


    public function registerPartner(Request $request, SalaamPartnerServices $salaamPartnerServices)
    {
        return $salaamPartnerServices->registerPartner($request);
    }
    public function verifyAccount(Request $request, SalaamPartnerServices $salaamPartnerServices)
    {
        return $salaamPartnerServices->verifyPartnerAccount($request);
    }

    public function withdrawFromAccount(Request $request, SalaamPartnerServices $salaamPartnerServices)
    {
        return $salaamPartnerServices->WithdrawFromAccount($request);
    }

    public function depositIntoAccount(Request $request, SalaamPartnerServices $salaamPartnerServices)
    {
        return $salaamPartnerServices->DepostIntoAccount($request);
    }

    public function schTransactionStatus(Request $request, SalaamPartnerServices $salaamPartnerServices)
    {
        return $salaamPartnerServices->SchTransactionStatus($request);
    }
}
