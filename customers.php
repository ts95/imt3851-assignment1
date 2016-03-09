<?php

require_once __DIR__ . '/classes/Helper.php';
require_once __DIR__ . '/classes/Storage.php';

$storage = new Storage(__DIR__ . '/data');
$storage->defineStore('customer', [
    'first name',
    'last name',
    'birthdate',
    'address',
    'assets',
]);
$storage->defineStore('account', [
    'account holder',
    'currency type',
    'balance',
    'withdrawals',
    'deposits',
]);

?>
<html>
<head>
    <meta charset="utf-8">

    <title>Customers</title>
</head>
<body>
    <h1>Customers</h1>

    Customers: <?php echo count($storage->allInStore('customer')); ?><br>
    Accounts: <?php echo count($storage->allInStore('account')); ?><br>

    <br>

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>First name</th>
                <th>Last name</th>
                <th>Birthdate</th>
                <th>Address</th>
                <th>Assets</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($storage->allInStore('customer') as $customer): ?>
            <tr>
                <td><?php echo $customer['id']; ?></td>
                <td><?php echo $customer['first name']; ?></td>
                <td><?php echo $customer['last name']; ?></td>
                <td><?php echo $customer['birthdate']; ?></td>
                <td><?php echo $customer['address']; ?></td>
                <td><?php echo $customer['assets']; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
