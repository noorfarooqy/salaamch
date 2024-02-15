<?php

namespace Noorfarooqy\Salaamch\Helpers;

enum ErrorCodes: string
{
    case sch_bank_account_required = "Bank Account Required";
    case sch_bank_account_not_found = "Bank Account Not Found";
    case sch_bank_deposit_data_entry_error = "Bank Deposit Data Entry Error";
    case sch_transaction_not_found = "Bank Transaction Status Not Found";
    case sch_missing_config = "Missing Config Parameter";
    case sch_deposit_checksum_failed = "Checksum does not match";
    case sch_bank_could_not_fetch_fx_rate = "Bank could not fetch fx rate";
    case sch_insufficient_account_balance = "Insufficient Account Balance";
}
