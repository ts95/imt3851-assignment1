<?php

require_once __DIR__ . '/head.php';

?>
<html>
<head>
    <meta charset="utf-8">

    <title>Data</title>

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
                <h1>New customer</h1>
                <hr>
                <form>
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
                            <div class="col-xs-2">
                                <input type="number" class="form-control" name="day" value="1" min="1" max="31">
                            </div>
                            <div class="col-xs-7">
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
                            <div class="col-xs-3">
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
                <h1>New account</h1>
                <hr>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <h1>New transaction</h1>
                <hr>
            </div>
        </div>
    </div>
</body>
</html>
