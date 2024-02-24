<?php

namespace App\Api\v1\WesternUnion;

use App\Api\v1\ZainCashBaseAction;
use App\Helpers\WesternUnion\WuCountryQuotaHelper;
use App\Models\WuCountry;

class PreviewSendMoneyAction extends ZainCashBaseAction
{
    protected $verbs = ['POST', 'GET'];

    protected $private = true;

    public function execute()
    {
        /* todo validate inputs */

        $transactionAmount = $this->request->get('transaction_amount');

        $country = WuCountry::where('id', $this->request->get('destination_country_id'))
            ->first();
        if ($country->enable_quota_checking) {
            $totalApprovedAmount = WuCountryQuotaHelper::getTotalApprovedAmountPerCountry($country);
            $totalPendingAmount = WuCountryQuotaHelper::getTotalPendingAmountPerCountry($country);
            if ($totalApprovedAmount + $transactionAmount > $country->quota_main_amount) {
                $this->response->addErrorDialog(trans("messages.wu_country_quota_limit"));
                return $this->response->statusFail(trans("messages.wu_country_quota_limit"));
            }
            if ($totalApprovedAmount + $totalPendingAmount + $transactionAmount > $country->quota_main_amount) {
                $this->response->addErrorDialog(trans("messages.wu_country_quota_limit"));
                return $this->response->statusFail(trans("messages.wu_country_quota_limit"));
            }
        }

        /* todo call wu */
        /* todo insert pending transaction */
        /* todo return a proper response */
    }

}