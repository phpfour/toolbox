<?php

class Citybank
{
    public static function parse($text)
    {
        if (stripos($text, 'Purchased') !== false) {
            return self::purchase($text);
        } elseif (stripos($text, 'Withdrawn') !== false) {
            return self::withdraw($text);
        } elseif (stripos($text, 'deposited') !== false) {
            return self::deposit($text);
        } elseif (stripos($text, 'transferred') !== false) {
            return self::transfer($text);
        } else {
            return [null, 0, null];
        }
    }

    private static function purchase($text)
    {
        $info = explode(PHP_EOL, strtoupper(trim($text)));
        $amount = floatval(trim(str_replace(['PURCHASED', 'BDT'], '', $info[1])));
        $merchant = trim(str_replace(['FROM', ', BD'], '', $info[2]));

        return [Transaction::TYPE_PURCHASE, $amount, $merchant];
    }

    private static function withdraw($text)
    {
        $info = explode(PHP_EOL, strtoupper(trim($text)));

        if (stripos($text, 'e-commerce') !== false) {
            $amount = self::extractAmount($info[0]);
            $merchant = 'E-COMMERCE';
        } elseif (stripos($text, 'iTransfer') !== false) {
            $amount = self::extractAmount($info[0]);
            $merchant = 'i-TRANSFER';
        } elseif (stripos($text, 'NPSB') !== false) {
            $amount = self::extractAmount($info[0]);
            $merchant = 'NPSB';
        } else {
            $amount = floatval(trim(str_replace(['WITHDRAWN', 'BDT'], '', $info[1])));
            $merchant = trim(str_replace(['FROM', ':'], '', $info[2]));
        }

        return [Transaction::TYPE_WITHDRAW, $amount, $merchant];
    }

    private static function deposit($text)
    {
        $info = explode(PHP_EOL, strtoupper(trim($text)));
        $amount = self::extractAmount($info[1]);
        $merchant = stripos($info[1], 'Cheque') !== false ? 'CHEQUE' : 'CASH';

        return [Transaction::TYPE_DEPOSIT, $amount, $merchant];
    }

    private static function transfer($text)
    {
        $info = explode(PHP_EOL, strtoupper(trim($text)));
        $amount = self::extractAmount($info[0]);
        $merchant = 'EXTERNAL';

        return [Transaction::TYPE_TRANSFER, $amount, $merchant];
    }

    private static function extractAmount($text)
    {
        $val = trim(substr($text, 2, strpos($text, 'has') - 1));
        $val = str_replace(',', '', $val);

        return floatval($val);
    }
}
