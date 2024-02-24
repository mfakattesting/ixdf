<?php


namespace App\Helpers\WesternUnion;


use App\Models\WuTransaction;
use Carbon\Carbon;

class WuCountryQuotaHelper
{

    public static function getTotalApprovedAmountPerCountry($country)
    {
        return WuTransaction::query()
            ->where('destination_country_id', $country->id)
            ->where('type', WuTransactionType::SEND_MONEY)
            ->whereIn('status', [WuTransactionStatus::CLAIMED, WuTransactionStatus::UNCLAIMED])
            ->where('created_at', '>=', $country->quota_start_period)
            ->where('created_at', '<=', $country->quota_end_period)
            ->sum('principal_amount');
    }

    public static function getTotalPendingAmountPerCountry($country)
    {
        return WuTransaction::query()
            ->where('destination_country_id', $country->id)
            ->where('type', WuTransactionType::SEND_MONEY)
            ->where('status', [WuTransactionStatus::PENDING])
            ->where('created_at', '>=', Carbon::now()->addMinutes(-5))
            ->sum('principal_amount');
    }

    public static function getPendingTransactionPerCountry($country)
    {
        $query = WuTransaction::query()
            ->select(['id', 'principal_amount'])
            ->where('destination_country_id', $country->id)
            ->where('type', WuTransactionType::SEND_MONEY)
            ->where('status', [WuTransactionStatus::PENDING])
            ->where('created_at', '>=', Carbon::now()->addMinutes(-6))
            ->get();
        $result = [];
        $cum = 0;
        foreach ($query as $row) {
            $cum = $cum + $row->principal_amount;
            $result[$row->id] = $cum;
        }
        return $result;
    }
}