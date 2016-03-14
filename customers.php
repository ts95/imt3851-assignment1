<?php

require_once __DIR__ . '/head.php';

$customersCollection = $store->getCollection('customers');
$accountsCollection = $store->getCollection('accounts');

$totalCustomers = count($customersCollection->getRows());
$totalAccounts = count($accountsCollection->getRows());

$customers = $customersCollection->getRows();

?>
<html>
<head>
    <meta charset="utf-8">

    <title>Bank – Customers</title>

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
                <h1>Customers</h1>
                <hr>

                <div class="row">
                    <div class="col-sm-4">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>Total customers</th>
                                    <td><?php echo $totalCustomers; ?></td>
                                </tr>
                                <tr>
                                    <th>Total accounts</th>
                                    <td><?php echo $totalAccounts; ?></td>
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
                                        <th>Name</th>
                                        <th>Surname</th>
                                        <th>Birthdate</th>
                                        <th>Person ID</th>
                                        <th>Address</th>
                                        <th>Total assets</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><a href="account.php?customer=<?php echo $customer['person id']; ?>"><?php echo $customer['name']; ?></a></td>
                                        <td><?php echo $customer['surname']; ?></td>
                                        <td><?php echo $customer['birthdate']; ?></td>
                                        <td><?php echo $customer['person id']; ?></td>
                                        <td><?php echo $customer['address']; ?></td>
                                        <td><?php echo bankNumber($customer['total assets']) . ' USD'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
