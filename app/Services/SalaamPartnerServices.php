<?php

namespace Noorfarooqy\Salaamch\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Noorfarooqy\NoorAuth\Services\NoorServices;
use Noorfarooqy\Salaamch\DataModels\SchPartner;
use Noorfarooqy\Salaamch\DataModels\SchPartnerUser;
use Noorfarooqy\Salaamch\DataModels\SchTransaction;
use Noorfarooqy\Salaamch\Events\PartnerDepositSentEvent;
use Noorfarooqy\Salaamch\Helpers\ErrorCodes;
use Noorfarooqy\Salaamch\Traits\ConnectionTrait;
use Noorfarooqy\Salaamch\Traits\SalaamClearingHouseTrait;
use Throwable;

/**
 * Class SalaamPartnerServices
 * @package Noorfarooqy\Salaamch\Services
 */
class SalaamPartnerServices extends NoorServices
{
    use SalaamClearingHouseTrait;
    use ConnectionTrait;
    protected $payload;
    protected $language = "english";
    protected $security;
    protected $method;

    protected $endpoint;
    public $has_failed;

    /**
     * @param $request
     * @return object
     */
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
        $this->initializeSalaamClearingHouse();
        $this->request = $request;

        $this->rules = [
            'sender_id' => 'required|numeric',
            'sender_name' => 'required|string|min:3|max:125',
            'sender_account_number' => 'required|numeric',
            'sender_telephone_number' => 'required|numeric',
            'beneficiary_telephone' => 'required|numeric',
            'beneficiary_account_number' => 'required|numeric',
            'amount_in_usd' => 'required|numeric|min:1|max:' . env('SCH_MAX_USD_AMOUNT', 1000),
            'description' => 'required|string|min:4|max:125',
            'agent_country' => 'required|string|max:45|min:2',
            'agent_branch' => 'required|string|max:45|min:3',
            'agent_name' => 'required|string|max:45|min:4',
            'checksum' => 'required|string',
        ];

        $this->customValidate();
        if ($this->has_failed) {
            $this->setError($this->getMessage(), ErrorCodes::sch_bank_account_required->value);
            return $this->getResponse();
        }
        $data = $this->validatedData();

        if ($data['checksum'] != $checksum = $this->generateChecksum($request->except('checksum'))) {
            $this->setError('Checksum does not match ' . $checksum, ErrorCodes::sch_deposit_checksum_failed->value);
            return $this->getResponse();
        }


        $data = $this->confirmBalanceAndRate($data);
        if (!$data) {
            return $this->getResponse();
        }

        $trn_id = time();
        $srcId = 'ESB' . gmdate('ymdis', time());
        $acc =  $data['sender_account_number'];
        $branch =  substr($data['sender_account_number'], 0, 3);
        $amount = $data['local_amount'];
        $hp_code = config('salaamch.block.hp_code', 'MPESA');
        $blocked_amount = $this->bank->blockAmount($data['sender_account_number'], $branch, $amount, $hp_code, $srcId);
        $blocked = $blocked_amount->original;
        if ($blocked['error_code'] != 0) {
            $this->setError($blocked["error_message"]);
            return $this->getResponse();
        }

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
        $this->endpoint = config('salaamch.endpoints.methods.deposit.api');
        $this->method = config('salaamch.endpoints.methods.deposit.name');

        try {
            $partner = SchPartnerUser::where('user', $request->user()?->id)->get()->first();
            if (!$partner) {
                $this->setError('Partner not found');
                return $this->getResponse();
            }
            DB::beginTransaction();
            $deposit = SchTransaction::create([
                'partner_id' => $partner->partner_id,
                'src_transaction_id' => $trn_id,
                'bank_transaction_id' => $trn_id,
                'src_trn_head_id' => $srcId,
                'sender_id' => $data['sender_id'],
                'sender_name' => $data['sender_name'],
                'amount_in_usd' => $data['amount_in_usd'],
                'local_amount' => $data['local_amount'],
                'beneficiary_account_number' => $data['beneficiary_account_number'],
                'description' => $data['description'],
                'bank_code' => env('SCH_BANK_CODE'),
                'initiated_by' => $request->user()?->id,
            ]);

            Log::info('before sending request');
            $response = $this->SendSchRequest();

            $origin = [
                'branch' => substr($data['sender_account_number'], 0, 3),
                'account' => $data['sender_account_number'],
                'ccy' => 'USD',
            ];

            Log::info('response');
            Log::info($response);
            if ($response["statusCode"] != 200) {
                $this->setError($response['statusMessage']);
                // $this->setStatus($response['statusCode']);
                $deposit->status_code = $response['statusCode'];
                $deposit->status_message = $response['statusMessage'];
                $deposit->save();
                DB::commit();
                return $this->getResponse();
            }
            Log::info('after sending request');

            $transaction = $this->bank->createTransaction($amount, config('salaamch.product'), $origin, $offset = null);

            $transaction = $transaction->original;
            Log::info($transaction);
            Log::info('after create transaction');
            if ($transaction['error_code'] != 0) {
                $this->setError($transaction["error_message"]);
                return $this->getResponse();
            }
            $unblocked = $this->bank->closeBlockAmount($data['sender_account_number'], $srcId);

            $deposit->charge_amount = $response['transactionInformation']['chargeAmount'];
            $deposit->bank_transaction_id = $response['transactionInformation']['bankTransactionId'];
            $deposit->current_balance = $response['transactionInformation']['currentBalance'];
            $deposit->status_code = $response['statusCode'];
            $deposit->status_message = $response['statusMessage'];
            $deposit->bank_account_pan = $response['beneficiaryInfo']['banakAccountPAN'];
            $deposit->bank_account_title = $response['beneficiaryInfo']['bankAccountTitle'];
            $deposit->is_success = $response['statusCode'] == 200;
            $deposit->save();

            Log::info('post sending request');
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
        $this->endpoint = config('salaamch.endpoints.methods.status.api');
        $this->method = config('salaamch.endpoints.methods.status.name');

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

    public function confirmBalanceAndRate($data)
    {
        $this->initializeSalaamClearingHouse();

        $balance = $this->bank->getBalance($data['sender_account_number']);
        if (!$balance) {
            $this->setError('Account balance could not be fetched - ' . $this->bank->getMessage(), ErrorCodes::sch_bank_account_not_found->value);
            return false;
        }
        Log::info($balance);
        $balance = $balance->original['data'];
        if ($balance['ccy'] == 'USD') {
            $data['local_amount'] = $data['amount_in_usd'];
        } else {
            $rate = $this->bank->getExchangeRate($from = 'USD', $to = 'KES');
            $rate = $rate->original['data'];
            if (!$rate) {
                $this->setError('Exchange rate could not be fetched - ' . $this->bank->getMessage(), ErrorCodes::sch_bank_could_not_fetch_fx_rate->value);
                return false;
            } else if ($rate['salerate'] <= 0 || $rate['salerate'] == null) {
                $this->setError('Exchange rate is not accurate - ' . $rate['salerate'] . ' - ' . $this->bank->getMessage(), ErrorCodes::sch_bank_could_not_fetch_fx_rate->value);
                return false;
            }
            $data['local_amount'] = $data['amount_in_usd'] * $rate['salerate'];
        }
        if ($balance['current_balance'] < ($data['local_amount'] + $this->bank->getTransactionCharge($data['local_amount'], 'sch'))) {
            $this->setError('Insufficient account balance - ' . $balance['current_balance'], ErrorCodes::sch_insufficient_account_balance->value);
            return false;
        }
        return $data;
    }


    public function registerPartner($request)
    {
        $this->request = $request;
        $this->rules = [
            'partner_name' => 'required|string|max:75|unique:sch_partners',
            'partner_country' => 'required|string|max:45',
            'partner_city' => 'required|string|max:45',
            'partner_email' => 'required|email|unique:sch_partners',
            'partner_telephone' => 'required|string|max:12|unique:sch_partners',
            'partner_contact_name' => 'required|string|max:45'
        ];
        $this->customValidate();
        if ($this->has_failed) {
            return $this->getResponse();
        }
        $data = $this->validatedData();
        $data['created_by'] = $request->user()?->id;
        try {
            $partner = SchPartner::create($data);
            $partner_user = SchPartnerUser::create([
                'partner_id' => $partner->id,
                'user' => $request->user()?->id,
                'created_by' => $request->user()?->id,
            ]);
            $this->setError('', 0);
            $this->setSuccess('success');
            return $this->getResponse($partner);
        } catch (Throwable $th) {
            $this->setError($th->getMessage());
            return $this->getResponse();
        }
    }
}
