<?php

namespace Noorfarooqy\Salaamch\Helpers;

enum ErrorCodes: string
{
    case sch_bank_account_required = "Bank Account Required";
    case sch_bank_account_not_found = "Bank Account Not Found";
    case sch_bank_deposit_data_entry_error = "Bank Deposit Data Entry Error";
    case sch_transaction_not_found = "Bank Transaction Status Not Found";
}
