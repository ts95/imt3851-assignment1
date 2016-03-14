<?php

require_once __DIR__ . '/head.php';

$customersCollection = $store->getCollection('customers');
$accountsCollection = $store->getCollection('accounts');
$transactionsCollection = $store->getCollection('transactions');

if (!isset($_GET['customer'])) {
    die("Lacking customer GET parameter.");
}

$customer = $customersCollection->searchRow(function($customer) {
    return $customer['person id'] == $_GET['customer'];
});

if (!$customer) {
    die("This customer does not exist.");
}

$accounts = $accountsCollection->searchRows(function($account) {
    return $account['account holder'] == $_GET['customer'];
});

?>
<html>
<head>
    <meta charset="utf-8">

    <title>Bank – Account</title>

    <link rel="stylesheet" href="public/css/bootstrap.min.css">
    <link rel="stylesheet" href="public/css/styles.css">

    <script defer src="public/js/jquery-2.2.1.min.js"></script>
    <script defer src="public/js/bootstrap.min.js"></script>
    <script defer src="public/js/script.js"></script>
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <h1>Accounts of <b><?php echo $customer['name']; ?></b></h1>
                <hr>

                <?php if (count($accounts) == 0): ?>
                <p>This customer has no accounts. New accounts can be created <a href="data.php#new-account">here</a>.</p>
                <?php endif; ?>

                <?php foreach ($accounts as $account): ?>
                <div class="row">
                    <div class="col-md-5">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>Account name</th>
                                    <td><?php echo $account['account name']; ?></td>
                                </tr>
                                <tr>
                                    <th>Account number</th>
                                    <td><?php echo $account['account number']; ?></td>
                                </tr>
                                <tr>
                                    <th>Deposits</th>
                                    <td><?php echo $account['deposits']; ?></td>
                                </tr>
                                <tr>
                                    <th>Withdrawals</th>
                                    <td><?php echo $account['withdrawals']; ?></td>
                                </tr>
                                <tr>
                                    <th>Balance</th>
                                    <td><?php echo $account['balance'] . ' ' . $account['currency type']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-7">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Value</th>
                                        <th>Associated account</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $transactions = $transactionsCollection->searchRows(function($transaction) use($account) {
                                    return $transaction['associated account'] == $account['account number'];
                                });
                                ?>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo $transaction['type']; ?></td>
                                        <td><?php echo $transaction['value']; ?></td>
                                        <td><?php echo $transaction['associated account']; ?></td>
                                        <td><?php echo date('Y/m/d', $transaction['date']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <hr>

                <?php endforeach; ?>

                <a href="customers.php">← Back to <b>customers</b></a>
            </div>
        </div>
    </div>
</body>
</html>
