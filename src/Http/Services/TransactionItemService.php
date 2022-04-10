<?php

namespace Apptimus\Transaction\Http\Services;

use Apptimus\Transaction\Models\TransactionItem;

class TransactionItemService
{
    public static function saveTransactionItem($transactionId,$accountType,$partyId,$bankAccountId,$categoryId,$debit,$credit)
    {
        $transactionItem = new TransactionItem();
        $transactionItem->transaction_id = $transactionId;
        $transactionItem->account_type = $accountType;
        $transactionItem->party_id = $partyId;
        $transactionItem->bank_account_id = $bankAccountId;
        $transactionItem->category_id = $categoryId;
        $transactionItem->debit = $debit;
        $transactionItem->credit = $credit;
        $transactionItem->save();

        return $transactionItem;
    }
}
