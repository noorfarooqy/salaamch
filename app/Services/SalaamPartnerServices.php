<?php

namespace Noorfarooqy\Salaamch\Services;

use Illuminate\Support\Facades\Http;
use Noorfarooqy\NoorAuth\Services\NoorServices;
use Noorfarooqy\Salaamch\Events\PartnerDepositSentEvent;
use Noorfarooqy\Salaamch\Helpers\ErrorCodes;

class SalaamPartnerServices extends NoorServices
{

    protected $payload;
    protected $language = "english";
    protected $security;
    protected $method;

    protected $endpoint;
    public $has_failed;
    public function verifyPartnerAccount($request)
    {

        $this->request = $request;

        $this->rules = [
            'bank_account' => 'required|numeric',
        ];

        $this->customValidate();
        if ($this->has_failed) {
            $this->setError($this->getMessage(), ErrorCodes::sch_bank_account_required->value);
            return $this->getResponse();
        }
        $this->payload = $this->validatedData();
        $this->payload = ['bankAccount' => $this->payload['bank_account']];
        $this->endpoint = config('salaamch.endpoints.methods.verification.api');
        $this->method = config('salaamch.endpoints.methods.verification.name');

        $response = $this->SendSchRequest();
        if ($response["statusCode"] != 200) {
            $this->setError($response['statusMessage'], ErrorCodes::sch_bank_account_not_found->value);
            $this->setStatus($response['statusCode']);
            return $this->getResponse();
        } else if ($response['statusMessage'] != 'A') {
            $this->setError('Account is not active', ErrorCodes::sch_bank_account_not_found->value);
            $this->setStatus($response['statusCode']);
            return $this->getResponse();
        }


        $this->setError('', 0);
        $this->setSuccess('success');

        return $this->getResponse($response);
    }

    public function SendSchRequest()
    {
        $this->security = [
            "login" => config('salaamch.login'),
            "password" => config('salaamch.password'),
            "secret" => config('salaamch.secret'),
        ];

        PartnerDepositSentEvent::dispatch($this->payload);
        $this->payload['secret'] = $this->security;
        $this->payload['method'] = $this->method;
        $this->payload['languageName'] = $this->language;
        $url = config('salaamch.endpoints.root') . $this->endpoint;
        $response = Http::withHeaders(['Content-Type' => 'application/json'])->post($url, $this->payload);

        return $response->json();
    }
}
