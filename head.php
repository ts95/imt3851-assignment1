<?php

require_once __DIR__ . '/misc/Helper.php';
require_once __DIR__ . '/store/Store.php';
require_once __DIR__ . '/store/Collection.php';

date_default_timezone_set('CET');

$supportedCurrencyTypes = ['USD', 'EUR'];

// 14th of March 2016
$conversionRates = [
    'USD -> EUR' => 0.899118864,
    'EUR -> USD' => 1.1122,
];

/**
 * Convert currency
 */
function cconv($value, $from, $to) {
    global $conversionRates;
    return $conversionRates["$from -> $to"] * $value;
}

$store = new \Store\Store(__DIR__ . '/data');

if (!$store->collectionExists('customers')) {
    $store->makeCollection('customers', [
        'name',
        'surname',
        'birthdate',
        'person id',
        'address',
        'total assets',
    ])->save();
}

if (!$store->collectionExists('accounts')) {
    $store->makeCollection('accounts', [
        'account name',
        'account holder',
        'account number',
        'currency type',
        'balance',
        'withdrawals',
        'deposits',
    ])->save();
}

if (!$store->collectionExists('transactions')) {
    $store->makeCollection('transactions', [
        'type',
        'value',
        'associated account',
        'date',
    ])->save();
}
