<?php

namespace App\Services\AlemTv;

use Carbon\Carbon;

class PaymentCalculatorService
{
    /**
     *
     * @param array $tarif
     * @param int   $end
     * @return array
     */
    public function calculate(array $tarif, int $end): array
    {
        $price     = (float) $tarif['price'];
        $maxPeriod = (int)   $tarif['max_period'];
      //  $endDate   = Carbon::createFromTimestamp($end);

        $periods = [];
        for ($month = 1; $month <= $maxPeriod; $month++) {
           // $newEnd = $endDate->copy()->addMonths($month);

            $periods[] = [
                'months'  => $month,
                'amount'  => $price * $month,
            //    'new_end' => $newEnd->toDateString(),
            ];
        }

        return $periods;
    }
}
