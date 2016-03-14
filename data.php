<?php

require_once __DIR__ . '/head.php';

$customersCollection = $store->getCollection('customers');
$accountsCollection = $store->getCollection('accounts');
$transactionsCollection = $store->getCollection('transactions');

if (isset($_POST['form'])) {
    $form = $_POST['form'];

    // XSS protection
    foreach ($_POST as $key => $value) {
        $_POST[$key] = htmlentities($value);
    }

    if ($form == 'new customer') {
        $name = mb_convert_case($_POST['name'], MB_CASE_TITLE, 'UTF-8');
        $surname = mb_convert_case($_POST['surname'], MB_CASE_TITLE, 'UTF-8');
        $day = str_pad($_POST['day'], 2, '0', STR_PAD_LEFT);
        $month = str_pad($_POST['month'], 2, '0', STR_PAD_LEFT);
        $year = $_POST['year'];
        $personId = $_POST['person-id'];
        $address = $_POST['address'];

        $errors = [];

        if (mb_strlen($name) < 2) {
            $errors[] = "The name must be at least 2 characters long.";
        }

        if (mb_strlen($name) > 50) {
            $errors[] = "The name can't exceed 50 characters.";
        }

        if (mb_strlen($surname) < 2) {
            $errors[] = "The surname must be at least 2 characters long.";
        }

        if (mb_strlen($surname) > 50) {
            $errors[] = "The surname can't exceed 50 characters.";
        }

        if (mb_strlen($personId) != 11) {
            $errors[] = "Invalid person ID (length must be 11 characters long).";
        }

        if (mb_strlen($address) < 10) {
            $errors[] = "The address must be at least 10 characters long.";
        }

        if (mb_strlen($address) > 80) {
            $errors[] = "The address may not exceed 80 characters.";
        }

        if (count($errors) > 0) {
            die(implode($errors, '<br>'));
        }

        $customersCollection->addRow([
            'name' => $name,
            'surname' => $surname,
            'birthdate' => "$year/$month/$day",
            'person id' => $personId,
            'address' => $address,
            'total assets' => 0,
        ]);
        $customersCollection->save();

        header('Location: customers.php');
    }

    if ($form == 'new account') {
        $name = $_POST['name'];
        $holder = $_POST['holder'];
        $currencyType = $_POST['currency-type'];

        $errors = [];

        if (mb_strlen($name) < 2) {
            $errors[] = "The name must be at least 2 characters long.";
        }

        if (mb_strlen($name) > 50) {
            $errors[] = "The name can't exceed 50 characters.";
        }

        $customer = $customersCollection->searchRow(function($customer) use($holder) {
            return $customer['person id'] == $holder;
        });

        if (!$customer) {
            $errors[] = "The holder doesn't exist.";
        }

        if (!in_array($currencyType, $supportedCurrencyTypes)) {
            $errors[] = "$currencyType is not supported.";
        }

        if (count($errors) > 0) {
            die(implode($errors, '<br>'));
        }

        $accountNumber = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT)
            . ' ' . str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT)
            . ' ' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $accountsCollection->addRow([
            'account name' => $name,
            'account holder' => $holder,
            'account number' => $accountNumber,
            'currency type' => $currencyType,
            'balance' => 0,
            'withdrawals' => 0,
            'deposits' => 0,
        ]);
        $accountsCollection->save();

        header("Location: account.php?customer=$holder");
    }

    if ($form == 'new transaction') {
        $type = $_POST['type'];
        $value = (double)$_POST['value'];
        $number = $_POST['account-number'];

        if ($type == 'withdrawal')
            $value *= -1;

        $errors = [];

        if (!in_array($type, ['deposit', 'withdrawal']))
            $errors[] = "Invalid type.";

        if ($value == 0)
            $errors[] = "The transaction value can't be 0 (zero).";

        $account = $accountsCollection->searchRow(function($account) use($number) {
            return $account['account number'] == $number;
        });

        if (!$account)
            $errors[] = "The account doesn't exist.";

        if (count($errors) > 0) {
            die(implode($errors, '<br>'));
        }

        $transactionsCollection->addRow([
            'type' => $type,
            'value' => abs($value),
            'associated account' => $number,
            'date' => time(),
        ]);
        $transactionsCollection->save();

        $updatedValues = ['balance' => $account['balance'] + $value];

        switch ($type) {
        case 'deposit':
            $updatedValues['deposits'] = $account['deposits'] + 1;
            break;

        case 'withdrawal':
            $updatedValues['withdrawals'] = $account['withdrawals'] + 1;
            break;
        }

        $accountsCollection->updateRows($updatedValues, function($account) use($number) {
            return $account['account number'] == $number;
        });
        $accountsCollection->save();

        $customer = $customersCollection->searchRow(function($customer) use($account) {
            return $customer['person id'] == $account['account holder'];
        });

        $newTotalAssets = 'USD' == $account['currency type'] ?
            $customer['total assets'] + $value : $customer['total assets'] + cconv($value, $account['currency type'], 'USD');

        $customersCollection->updateRows(['total assets' => $newTotalAssets], function($customer) use($account) {
            return $customer['person id'] == $account['account holder'];
        });
        $customersCollection->save();

        header('Location: account.php?customer=' . $account['account holder']);
    }
}
?>
<html>
<head>
    <meta charset="utf-8">

    <title>Bank – Data</title>

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
            <div class="col-md-6 col-md-offset-3">
                <h1 id="new-customer">New customer</h1>
                <hr>
                <form action="data.php" method="POST">
                    <input type="hidden" name="form" value="new customer">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Will">
                    </div>
                    <div class="form-group">
                        <label for="surname">Surname</label>
                        <input type="text" class="form-control" name="surname" placeholder="e.g. Smith">
                    </div>
                    <div class="form-group">
                        <label for="birthdate">Birthdate</label>
                        <div class="row">
                            <div class="col-xs-3">
                                <input type="number" class="form-control" name="day" value="1" min="1" max="31">
                            </div>
                            <div class="col-xs-5">
                                <select name="month" class="form-control">
                                    <option value="1">January</option>
                                    <option value="2">February</option>
                                    <option value="3">March</option>
                                    <option value="4">April</option>
                                    <option value="5">May</option>
                                    <option value="6">June</option>
                                    <option value="7">July</option>
                                    <option value="8">August</option>
                                    <option value="9">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                            </div>
                            <div class="col-xs-4">
                                <input type="number" class="form-control" name="year" value="1990" min="1900" max="<?php echo date('Y'); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="person-id">Person ID</label>
                        <input type="text" class="form-control" name="person-id" placeholder="e.g. 01129335987">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" class="form-control" name="address" placeholder="e.g. Henrik Ibsens gate 48, 0244 Oslo">
                    </div>
                    <input type="submit" class="btn btn-primary" value="Add new customer">
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <h1 id="new-account">New account</h1>
                <hr>
                <?php if (count($customersCollection->getRows()) == 0): ?>
                <p>Before an account can be created a customer must be added.</p>
                <?php else: ?>
                <form action="data.php" method="POST">
                    <input type="hidden" name="form" value="new account">
                    <div class="form-group">
                        <label for="name">Account name</label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Spending account">
                    </div>
                    <div class="form-group">
                        <label for="holder">Account holder</label>
                        <select name="holder" class="form-control">
                        <?php foreach ($customersCollection->getRows() as $customer): ?>
                            <option value="<?php echo $customer['person id']; ?>"><?php echo $customer['name'] . ' ' . $customer['surname']; ?></option>
                        <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="currency-type">Currency type</label>
                        <select name="currency-type" class="form-control">
                        <?php foreach ($supportedCurrencyTypes as $currencyType): ?>
                            <option value="<?php echo $currencyType; ?>"><?php echo $currencyType; ?></option>
                        <?php endforeach; ?>
                        </select>
                    </div>
                    <input type="submit" class="btn btn-primary" value="Add new account">
                </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <h1 id="new-transaction">New transaction</h1>
                <hr>
                <?php if (count($accountsCollection->getRows()) == 0): ?>
                <p>Before a transaction can be created an account must be added.</p>
                <?php else: ?>
                <form action="data.php" method="POST">
                    <input type="hidden" name="form" value="new transaction">
                    <div class="form-group">
                        <label for="type">Type</label>
                        <select name="type" class="form-control">
                            <option value="deposit">Deposit</option>
                            <option value="withdrawal">Withdrawal</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="value">Value</label>
                        <input type="number" class="form-control" name="value" value="0">
                    </div>
                    <div class="form-group">
                        <label for="account-number">Associated account</label>
                        <select name="account-number" class="form-control">
                        <?php foreach ($customersCollection->getRows() as $customer): ?>
                            <optgroup label="<?php echo $customer['name'] . ' ' . $customer['surname']; ?>">
                                <?php
                                $accounts = $accountsCollection->searchRows(function($account) use($customer) {
                                    return $account['account holder'] == $customer['person id'];
                                });
                                ?>
                                <?php foreach ($accounts as $account): ?>
                                <option value="<?php echo $account['account number']; ?>"><?php echo $account['account number']; ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                        </select>
                    </div>
                    <input type="submit" class="btn btn-primary" value="Add new transaction">
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
