<?php

namespace Apptimus\Transaction\Http\Controllers;

use App\Http\Controllers\Controller;
use Apptimus\Transaction\Http\Services\TransactionItemService;
use Apptimus\Transaction\Models\BankDepositPayment;
use Apptimus\Transaction\Models\CardPayement;
use Apptimus\Transaction\Models\CashPayement;
use Apptimus\Transaction\Models\ChequePayement;
use Apptimus\Transaction\Models\CreditPayment;
use Apptimus\Transaction\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class TransactionController extends Controller
{
    public function saveTransaction(Request $request)
    {
        try {
            DB::beginTransaction();
            $maxId = DB::table('acc_transactions')->count()+1;
            try{
                $maxId = DB::table('acc_transactions')->count()+1;
            }
            catch(\Exception $e){
            }
            $maxId = "T".(10000000 + $maxId);

            $sobj = json_decode($request->data, false);

            $dt = new Carbon();

            if($sobj->total_amount <= 0){
                $response["msg"] = "Invalid Amount";
                $response["status"] = "Failed";
                $response["is_success"] = false;
            }
            else{
                //build transaction details
                $transaction = new Transaction();
                $transaction->invoice_number = $maxId;
                if($sobj->transaction_type == 'PARTY TRANSACTION' || $sobj->transaction_type == 'CATEGORY TRANSACTION') {
                    $transaction->ref_number = $sobj->amount->paid > 0 ? 'Paid Payment' : ($sobj->amount->payable > 0 ? 'Payable Payment' : ($sobj->amount->received > 0 ? 'Received Payment' : 'Receivable Payment'));
                }
                else if($sobj->transaction_type == 'BANK TRANSACTION') {
                    $transaction->ref_number = $sobj->amount->paid > 0 ? 'Deposit' : 'Withdrawal';
                }
                else {
                    $transaction->ref_number = $sobj->ref_number;
                }
                $transaction->transaction_type = $sobj->transaction_type;
                $transaction->date = $sobj->date;
                $transaction->note = $sobj->note;
                // $transaction->attachment = $sobj->attachment;
                $transaction->total_amount = $sobj->total_amount;

                if($request->hasFile('attachment')){
                    $extension =  $request->file('attachment')->getClientOriginalExtension();
                    $file_name = "transaction-".$maxId. time()."." .$extension;
                    $png_url = "/storage/transaction/". $file_name ;
                    \Storage::disk('transaction')->put( $file_name ,file_get_contents($request->attachment));
                    $transaction->attachment = $png_url;
                }else{
                    $transaction->attachment = null;
                }

                $transaction->save();

                //build main transaction item
                $debit = 0; $credit = 0;
                if($sobj->amount->paid+$sobj->amount->receivable > 0) { $debit = $sobj->total_amount; }
                else { $credit = $sobj->total_amount; }

                if(!empty($sobj->account_info->account)) {
                    TransactionItemService::saveTransactionItem($transaction->id,$sobj->account_info->account,null,null,null,$debit,$credit);
                }
                else if(!empty($sobj->account_info->person)) {
                    TransactionItemService::saveTransactionItem($transaction->id,'Party',$sobj->account_info->person,null,null,$debit,$credit);
                }
                else if(!empty($sobj->account_info->bank_account)) {
                    TransactionItemService::saveTransactionItem($transaction->id,'Bank',null,$sobj->account_info->bank_account,null,$debit,$credit);
                }
                else if(!empty($sobj->account_info->transaction_category)) {
                    TransactionItemService::saveTransactionItem($transaction->id,'Transaction Category',null,null,$sobj->account_info->transaction_category,$debit,$credit);
                }

                if(!empty($sobj->to_account_info->account) || !empty($sobj->to_account_info->person) || !empty($sobj->to_account_info->bank_account) || !empty($sobj->to_account_info->transaction_category))
                {
                    $debit = 0; $credit = 0;
                    if($sobj->amount->paid+$sobj->amount->receivable > 0) { $credit = $sobj->total_amount; }
                    else { $debit = $sobj->total_amount; }
                    //build other transaction item
                    if(!empty($sobj->to_account_info->account)) {
                        TransactionItemService::saveTransactionItem($transaction->id,$sobj->to_account_info->account,null,null,null,$debit,$credit);
                    }
                    else if(!empty($sobj->to_account_info->person)) {
                        TransactionItemService::saveTransactionItem($transaction->id,'Party',$sobj->to_account_info->person,null,null,$debit,$credit);
                    }
                    else if(!empty($sobj->to_account_info->bank_account)) {
                        TransactionItemService::saveTransactionItem($transaction->id,'Bank',null,$sobj->to_account_info->bank_account,null,$debit,$credit);
                    }
                    else if(!empty($sobj->to_account_info->transaction_category)) {
                        TransactionItemService::saveTransactionItem($transaction->id,'Transaction Category',null,null,$sobj->to_account_info->transaction_category,$debit,$credit);
                    }
                }
                else
                {

                    $this->buildTransactionItemFromPaymentModes($sobj,$transaction);

                }

                DB::commit();

                $response["msg"] = 'Data has been successfully saved.';
                $response["status"] = "Success";
                $response["is_success"] = true;
                $response["transaction_id"] = $transaction->id;
            }
        } catch(\Exception $e) {
            DB::rollback();
            $response["msg"] = $e->getMessage();
            $response["status"] = "Failed";
            $response["is_success"] = false;
        } catch(\Throwable $e) {
            DB::rollback();
            $response["msg"] = $e->getMessage();
            $response["status"] = "Failed";
            $response["is_success"] = false;
        }
        return $response;
    }

    public function buildTransactionItemFromPaymentModes($sobj,$transaction)
    {
        // CASH MODE


        if($sobj->payment_modes->cash->amount > 0)
        {
            $debit = 0; $credit = 0;
            if($sobj->amount->paid+$sobj->amount->receivable > 0) {
                $credit = $sobj->payment_modes->cash->amount;
            }
            else {
                $debit = $sobj->payment_modes->cash->amount;
            }
            $transactionItem = TransactionItemService::saveTransactionItem($transaction->id,'Cash',null,null,null,$debit,$credit);

            $paymentCash = new CashPayement();
            $paymentCash->payments_id = $transactionItem->id;
            $paymentCash->amount = $sobj->payment_modes->cash->amount;
            $paymentCash->save();

        }

        // CREDIT MODE
        if($sobj->payment_modes->credit->amount > 0)
        {
            $debit = 0; $credit = 0;
            if($sobj->amount->paid+$sobj->amount->receivable > 0) {
                $credit = $sobj->payment_modes->credit->amount;
            }
            else {
                $debit = $sobj->payment_modes->credit->amount;
            }

            $transactionItem = TransactionItemService::saveTransactionItem($transaction->id,'Party',$sobj->account_info->person,null,null,$debit,$credit);

            $paymentCredit = new CreditPayment();
            $paymentCredit->payments_id = $transactionItem->id;
            $paymentCredit->amount = $sobj->payment_modes["credit"]["amount"];
            $paymentCredit->save();
        }

        // CARD MODE
        if(count(array($sobj->payment_modes->card)) > 0)
        {
            foreach ($sobj->payment_modes->card as $key => $value) {
                if($value->amount > 0)
                {
                    $debit = 0; $credit = 0;
                    if($sobj->amount->paid+$sobj->amount->receivable > 0) { $credit = $value->amount; }
                    else { $debit = $value->amount; }
                    $transactionItem = TransactionItemService::saveTransactionItem($transaction->id,'Bank',null,$value->bank_account,null,$debit,$credit);

                    $paymentCard = new CardPayement();
                    $paymentCard->payments_id = $transactionItem->id;
                    $paymentCard->amount = $value->amount;
                    $paymentCard->bank_accounts_id = $value->bank_account;
                    $paymentCard->save();
                }
            }
        }

        // CHEQUE MODE
        if(count(array($sobj->payment_modes->cheque)) > 0)
        {
            foreach ($sobj->payment_modes->cheque as $key => $value) {
                if($value->amount > 0)
                {
                    $debit = 0; $credit = 0;
                    if($sobj->amount->paid+$sobj->amount->receivable > 0) { $credit = $value->amount; }
                    else { $debit = $value->amount; }
                    $transactionItem = TransactionItemService::saveTransactionItem($transaction->id,'Bank',null,$value->bank_account,null,$debit,$credit);

                    $paymentCheque = new ChequePayement();
                    $paymentCheque->payments_id = $transactionItem->id;
                    $paymentCheque->bank_accounts_id = $value->bank_account;
                    $paymentCheque->amount = $value->amount;
                    $paymentCheque->cheque_number = $value->chequeNo;
                    $paymentCheque->status = "OPEN";
                    $paymentCheque->due_date = $value->expiryDate;
                    $paymentCheque->cheque_type = $value->chequeType;
                    $paymentCheque->banks_id = null;
                    $paymentCheque->save();
                }
            }
        }

        // DEPOSIT MODE
        if(count($sobj->payment_modes->deposit) > 0)
        {
            foreach ($sobj->payment_modes->deposit as $key => $value) {
                if($value->amount > 0)
                {
                    $debit = 0; $credit = 0;
                    if($sobj->amount->paid+$sobj->amount->receivable > 0) { $credit = $value->amount; }
                    else { $debit = $value->amount; }
                    $transactionItem = TransactionItemService::saveTransactionItem($transaction->id,'Bank',null,$value->bank_account,null,$debit,$credit);

                    $bankCash = new BankDepositPayment();
                    $bankCash->payments_id = $transactionItem->id;
                    $bankCash->amount = $value->amount;
                    $bankCash->bank_accounts_id = $value->bank_account;
                    $bankCash->save();
                }
            }
        }
    }

}
