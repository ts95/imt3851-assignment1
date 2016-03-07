<?php

require_once __DIR__ . '/classes/Helper.php';
require_once __DIR__ . '/classes/Storage.php';

$storage = new Storage(__DIR__ . '/data');
$storage->defineStore('customer', [
    'fname',
    'lname',
    'birthdate',
    'address',
    'assets',
]);

/*

$storage->insertIntoStore('customer', [
    'fname' => "Dario",
    'lname' => "Sucic",
    'birthdate' => "09.05.97",
    'address' => "Frøyas vei 55, 3472 Bødalen",
    'assets' => 1000000,
]);

$storage->insertIntoStore('customer', [
    'fname' => "Toni",
    'lname' => "Sucic",
    'birthdate' => "08.06.95",
    'address' => "Jernbanegata 7, 2821 Gjøvik",
    'assets' => 100,
]);

$matches = $storage->searchInStore('customer', function($customer) {
    return $customer['assets'] < 1000;
});

Helper::dd($matches);

$storage->updateInStore('customer', ['address' => "Frøyas vei 55, 3472 Bødalen"], function($customer) {
    return $customer['lname'] == 'Sucic';
});

*/
