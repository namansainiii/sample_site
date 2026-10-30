<?php

namespace App\Support;

class Money
{
    /**
     * Format rupees with Indian digit grouping: 123456.5 => "₹1,23,456.50".
     */
    public static function inr(float|int|string|null $amount): string
    {
        $amount = round((float) $amount, 2);
        $sign = $amount < 0 ? '-' : '';
        [$whole, $fraction] = explode('.', number_format(abs($amount), 2, '.', ''));

        if (strlen($whole) > 3) {
            $lastThree = substr($whole, -3);
            $rest = substr($whole, 0, -3);
            $whole = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest).','.$lastThree;
        }

        return $sign.'₹'.$whole.'.'.$fraction;
    }
}
