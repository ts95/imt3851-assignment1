<?php

require_once __DIR__ . '/classes/Helper.php';
require_once __DIR__ . '/classes/Storage.php';

date_default_timezone_set('CET');

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
$storage->defineStore('transaction', [
    'type',
    'associated account',
    'date',
]);

$selectedAccountID = isset($_GET['account']) ? $_GET['account'] : 1;

$accounts = $storage->allInStore('account');

if (count($accounts) > 0) {
    $selectedAccount = $storage->searchInStore('account', function($account) use($selectedAccountID) {
        return $account['id'] == $selectedAccountID;
    })[0];

    $transactions = $storage->searchInStore('transaction', function($transaction) use($selectedAccountID) {
        return $transaction['associated account'] == $selectedAccountID;
    });
}

?>
<html>
<head>
    <meta charset="utf-8">

    <title>Account</title>
</head>
<body>
    <h1>Account</h1>

    <?php if (count($accounts) == 0): ?>
        <h3>There are no accounts.</h3>
    <?php else: ?>
        <form action="account.php" method="GET">
            <select name="account">
            <?php foreach ($accounts as $account): ?>
                <?php
                    $id = $account['id'];
                    $name = $account['account holder'];
                    $selected = $id == $selectedAccountID ? 'selected' : '';
                ?>
                <option <?php echo $selected; ?> value="<?php echo $id; ?>"><?php echo $name; ?></option>
            <?php endforeach; ?>
            </select>
            <input type="submit" value="Change account">
        </form>

        <br>

        Withdrawals: <?php echo $selectedAccount['withdrawals']; ?><br>
        Deposits: <?php echo $selectedAccount['deposits']; ?><br>
        Balance: <?php echo $selectedAccount['balance']; ?> <?php echo $selectedAccount['currency type']; ?><br>

        <br>

        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?php echo $transaction['id']; ?></td>
                    <td><?php echo $transaction['type']; ?></td>
                    <td><?php echo date('Y-m-d', $transaction['date']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
