<?php

require_once __DIR__ . '/head.php';

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

    <title>Account</title>

    <link rel="stylesheet" href="/public/css/bootstrap.min.css">
    <link rel="stylesheet" href="/public/css/styles.css">

    <script defer src="/public/js/jquery-2.2.1.min.js"></script>
    <script defer src="/public/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <h1>Accounts of <b><?php echo $customer['name']; ?></b></h1>
                <hr>

                <?php foreach ($accounts as $account): ?>
                <div class="row">
                    <div class="col-sm-4">
                        <table class="table table-bordered">
                            <tbody>
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
                                    <td><?php echo $account['balance']; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
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
                                        <td><?php echo date('Y-m-d', $transaction['date']); ?></td>
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
