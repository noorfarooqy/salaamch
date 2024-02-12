<?php

namespace Noorfarooqy\Salaamch\Controllers;

use Illuminate\Http\Request;
use Noorfarooqy\Salaamch\Controllers\Controller;
use Noorfarooqy\Salaamch\Services\SalaamPartnerServices;

class ApiSchController extends Controller
{

    public function verifyAccount(Request $request, SalaamPartnerServices $salaamPartnerServices)
    {
        return $salaamPartnerServices->verifyPartnerAccount($request);
    }

    public function depositAccount(Request $request, SalaamPartnerServices $salaamPartnerServices)
    {
        return $salaamPartnerServices->depositIntoAccount($request);
    }
}
