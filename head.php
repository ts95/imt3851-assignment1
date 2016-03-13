<?php

require_once __DIR__ . '/misc/Helper.php';
require_once __DIR__ . '/store/Store.php';
require_once __DIR__ . '/store/Collection.php';

date_default_timezone_set('CET');

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
