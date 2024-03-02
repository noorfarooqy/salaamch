<?php

namespace Noorfarooqy\Salaamch\Traits;

use Illuminate\Support\Facades\Http;
use Noorfarooqy\Salaamch\Events\PartnerDepositSentEvent;

trait ConnectionTrait
{
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
