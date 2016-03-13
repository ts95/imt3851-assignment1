<?php

require_once __DIR__ . '/misc/Helper.php';
require_once __DIR__ . '/store/Store.php';
require_once __DIR__ . '/store/Collection.php';

date_default_timezone_set('CET');

$store = new \Store\Store(__DIR__ . '/data');

$customersCollection = $store->makeCollection('customers', [
    'name',
    'surname',
    'birthdate',
    'person id',
    'address',
    'total assets',
]);
//$customersCollection->addRow([
//    'name' => 'Toni',
//    'surname' => 'Sucic',
//    'birthdate' => '080695',
//    'person id' => '08069594375',
//    'address' => 'Jernbanegata 7, 2821 Gjøvik',
//    'total assets' => 1000,
//]);
$customersCollection->save();

$accountsCollection = $store->makeCollection('accounts', [
    'account holder',
    'account number',
    'currency type',
    'balance',
    'withdrawals',
    'deposits',
]);
//$accountsCollection->addRow([
//    'account holder' => '08069594375',
//    'account number' => '0539 63 63684',
//    'currency type' => 'NOK',
//    'balance' => 1000,
//    'withdrawals' => 0,
//    'deposits' => 1,
//]);
$accountsCollection->save();

$transactionsCollection = $store->makeCollection('transactions', [
    'type',
    'value',
    'associated account',
    'date',
]);
//$transactionsCollection->addRow([
//    'type' => 'deposit',
//    'value' => 1000,
//    'associated account' => '08069594375',
//    'date' => time(),
//]);
$transactionsCollection->save();
