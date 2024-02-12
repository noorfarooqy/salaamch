<?php

namespace Noorfarooqy\Salaamch\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Noorfarooqy\NoorAuth\Services\NoorServices;
use Noorfarooqy\Salaamch\DataModels\ClientSchTransaction;
use Noorfarooqy\Salaamch\Events\PartnerDepositSentEvent;
use Noorfarooqy\Salaamch\Helpers\ErrorCodes;
use Throwable;

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

    public function DepositIntoAccount($request)
    {
        $this->request = $request;

        $this->rules = [
            'sender_id' => 'required|numeric',
            'sender_name' => 'required|string|min:3|max:125',
            'sender_account_number' => 'required|numeric',
            'sender_telephone_number' => 'required|numeric',
            'beneficiary_telephone' => 'required|numeric',
            'beneficiary_account_number' => 'required|numeric',
            'amount_in_usd' => 'required|numeric|min:1|max:' . env('SCH_MAX_AMOUNT', 10000),
            'description' => 'required|string|min:4|max:125',
            'agent_country' => 'required|string|max:45|min:2',
            'agent_branch' => 'required|string|max:45|min:3',
            'agent_name' => 'required|string|max:45|min:4',

        ];

        $this->customValidate();
        if ($this->has_failed) {
            $this->setError($this->getMessage(), ErrorCodes::sch_bank_account_required->value);
            return $this->getResponse();
        }
        $data = $this->validatedData();

        $trn_id = time();
        $srcId = 'ESB' . gmdate('ymdis', time());
        $data['request_ip'] = $request->ip();
        $data['initiator'] = $request->user()?->id;
        $data['request_ref'] = $trn_id;

        $this->payload = [
            'bankCode' => env('SCH_BANK_CODE'),
            'srcTransactionId' => $trn_id,
            'srcTranheadId' => $srcId,
            'operation' => 'transferToAnotherAccount',
            'serviceName' => 'transferToAnotherAccount',
            'ParityCheck' => '',
            'amountInformation' => [
                'amount' => $data['amount_in_usd'],
                'currency' => '$',
            ],
            'description' => $data['description'],
            'senderInfo' => [
                'senderId' => $data['sender_id'],
                'senderName' => $data['sender_name'],
            ],
            'beneficiaryInfo' => [
                'bankAccount' => $data['beneficiary_account_number'],
            ],
            'channelName' => env('SCH_CHANNEL_NAME', 'SMFB'),
        ];
        $this->endpoint = config('sch_config.endpoints.methods.deposit.api');
        $this->method = config('sch_config.endpoints.methods.deposit.name');

        try {
            DB::beginTransaction();
            $deposit = ClientSchTransaction::create([
                'src_transaction_id' => $trn_id,
                'bank_transaction_id' => $trn_id,
                'src_trn_head_id' => $srcId,
                'sender_id' => $data['sender_id'],
                'sender_name' => $data['sender_name'],
                'amount_in_usd' => $data['amount_in_usd'],
                'local_amount' => $data['amount_in_usd'],
                'beneficiary_account_number' => $data['beneficiary_account_number'],
                'description' => $data['description'],
                'bank_code' => env('SCH_BANK_CODE'),
                'initiated_by' => $request->user()?->id,
            ]);

            $response = $this->SendSchRequest();

            if ($response["statusCode"] != 200) {
                $this->setError($response['statusMessage']);
                // $this->setStatus($response['statusCode']);
                $deposit->statusCode = $response['statusCode'];
                $deposit->statusMessage = $response['statusMessage'];
                $deposit->save();
                DB::commit();
                return $this->getResponse();
            }

            $deposit->charge_amount = $response['transactionInformation']['chargeAmount'];
            $deposit->bank_transaction_id = $response['transactionInformation']['bankTransactionId'];
            $deposit->current_balance = $response['transactionInformation']['currentBalance'];
            $deposit->status_code = $response['statusCode'];
            $deposit->status_message = $response['statusMessage'];
            $deposit->bank_account_pan = $response['beneficiaryInfo']['banakAccountPAN'];
            $deposit->bank_account_title = $response['beneficiaryInfo']['bankAccountTitle'];
            $deposit->is_success = $response['statusCode'] == 200;
            $deposit->save();

            $this->setError('', 0);
            $this->setSuccess('success');

            DB::commit();

            return $this->getResponse($response);
        } catch (Throwable $th) {
            $this->setError($th->getMessage(), ErrorCodes::sch_bank_deposit_data_entry_error->value);
            return $this->getResponse();
        }
    }


    public function TransactionStatus($request)
    {
        $this->request = $request;

        $this->rules = [
            'srcTransactionId' => 'required|numeric',
            'bankAccount' => 'required|numeric',
            'transactionAmount' => 'required|numeric',
        ];

        $this->customValidate();
        if ($this->has_failed) {
            $this->setError($this->getMessage(), ErrorCodes::sch_bank_account_required->value);
            return $this->getResponse();
        }
        $data = $this->validatedData();
        $this->payload = [
            'bankCode' => "111683",
            'bankAccount' => $data['bankAccount'],
            'srcTransactionId' => $data['srcTransactionId'],
            'transactionAmount' => $data['transactionAmount'],
            'channel' => "SOAP",
        ];
        $this->endpoint = config('sch_config.endpoints.methods.status.api');
        $this->method = config('sch_config.endpoints.methods.status.name');

        $response = $this->SendSchRequest();
        if ($response["statusCode"] != 200) {
            $this->setError($response['statusMessage'], ErrorCodes::sch_transaction_not_found->value);
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
