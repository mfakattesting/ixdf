<?php

namespace App\Api\v1\WesternUnion;

use App\Api\v1\ZainCashBaseAction;
use App\Helpers\WesternUnion\WuCountryQuotaHelper;
use App\Models\WuTransaction;
use App\Helpers\WesternUnion\WuTransactionStatus;
use Carbon\Carbon;

class ConfirmSendMoneyAction extends ZainCashBaseAction
{
    protected $verbs = ['POST', 'GET'];

    protected $private = true;

    public $inputRules = [
        'pin' => 'required',
        'transactionId' => 'required',
    ];

    protected $inputMask = ['pin'];

    public function execute()
    {
        /* validate input */

        $transactionId = $this->request->input("transactionId");

        $transaction = WuTransaction::where('id', $transactionId)
            ->where('user_id', \Auth::user()->id)
            ->where('status', WuTransactionStatus::PENDING)
            ->where('created_at', '>', Carbon::now()->addMinutes(-5))
            ->with(['deliveryOption'])
            ->first();

        if (!$transaction) {
            $this->response->addErrorDialog('messages.invalid_transaction');
            return $this->response->statusFail('messages.invalid_transaction');
        }

        $country = $transaction->destinationCountry;
        if ($country->enable_quota_checking) {
            $totalApprovedAmount = WuCountryQuotaHelper::getTotalApprovedAmountPerCountry($country);
            $transactionAmount = $transaction->principal_amount;
            if ($totalApprovedAmount + $transactionAmount > $country->quota_main_amount) {
                $this->response->addErrorDialog(trans("messages.wu_country_quota_limit"));
                return $this->response->statusFail(trans("messages.wu_country_quota_limit"));
            }

            $pendingTransactions = WuCountryQuotaHelper::getPendingTransactionPerCountry($country);
            if (isset($pendingTransactions[$transaction->id])) {
                $cumAmount = $pendingTransactions[$transaction->id];
                if ($cumAmount + $totalApprovedAmount > $country->quota_main_amount) {
                    $this->response->addErrorDialog(trans("messages.wu_country_quota_limit"));
                    return $this->response->statusFail(trans("messages.wu_country_quota_limit"));
                }
            } else {
                $this->response->addErrorDialog(trans("messages.wu_country_quota_limit"));
                return $this->response->statusFail(trans("messages.wu_country_quota_limit"));
            }
        }

        /* todo call wu */
        /* todo update transaction */
        /* todo return a proper response */
    }
}