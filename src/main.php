<?php

mysqli_report(MYSQLI_REPORT_STRICT);

try {
    echo <<<TEXT

    Querying via MySQLi driver


    TEXT;

    $mysqli = new mysqli('db', 'root', getenv('MYSQL_ROOT_PASSWORD'), getenv('MYSQL_DATABASE'));

    $result = $mysqli->query('SELECT * FROM customer WHERE email LIKE "MARIA.MILLER@sakilacustomer.org"');

    // Return current row of a result set as an object
    $customer = $result->fetch_object();

    echo $customer->first_name, ' ', $customer->last_name, PHP_EOL;

    echo <<<TEXT


    Binding parameters


    TEXT;

    $customerIdGt = 100;
    $storeId = 2;
    $email = '%ANN%';

    $statement = $mysqli->prepare('SELECT * FROM customer WHERE customer_id > ? AND store_id = ? AND email LIKE ?');

    // First parameter of bind_param() is a string containing one or more types for the corresponding bind variables:
    // i (integer); d (double); s (string); b (blob)
    $statement->bind_param('iis', $customerIdGt, $storeId, $email);

    $statement->execute();

    $result = $statement->get_result();

    while ($customer = $result->fetch_object()) {
        echo $customer->first_name, ' ', $customer->last_name, PHP_EOL;
    }

    $statement->close();

    echo <<<TEXT


    Inserting


    TEXT;

    $address = 'The street';
    $district = 'The district';
    $cityId = 135;
    $postalCode = '31000';
    $phone = '123456789';
    $x = 56;
    $y = 70;

    $statement = $mysqli->prepare('INSERT INTO address (address, district, city_id, postal_code, phone, location) VALUES (?, ?, ?, ?, ?, POINT(?, ?));');
    $statement->bind_param('ssissii', $address, $district, $cityId, $postalCode, $phone, $x, $y);
    $statement->execute();
    $statement->close();

    // Quick and dirty way to fetch newly created address id
    $addressId = $mysqli->insert_id;

    echo "{$addressId}", PHP_EOL;

    echo <<<TEXT


    Updating


    TEXT;

    $address = 'The new street';

    $statement = $mysqli->prepare('UPDATE address SET address = ? WHERE address_id = ?');
    $statement->bind_param('si', $address, $addressId);
    $statement->execute();
    $statement->close();

    echo <<<TEXT


    Deleting


    TEXT;

    $paymentId = 500;

    $statement = $mysqli->prepare('DELETE FROM payment WHERE payment_id = ?');
    $statement->bind_param('i', $paymentId);
    $statement->execute();
    $statement->close();

    echo <<<TEXT


    Transactions
    Check https://dev.mysql.com/doc/refman/8.0/en/commit.html


    TEXT;

    $mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    $mysqli->query('INSERT INTO actor (first_name, last_name) VALUES ("John", "Doe");');
    $actorId = $mysqli->insert_id;
    $statement = $mysqli->prepare('SELECT * from actor WHERE actor_id = ?');
    $statement->bind_param('i', $actorId);
    $statement->execute();
    $result = $statement->get_result();
    $actor = $result->fetch_object();

    $mysqli->commit();

    echo $actor->first_name, ' ', $actor->last_name, PHP_EOL;
} catch (mysqli_sql_exception $e) {

    $mysqli->rollback();

    echo $e->getMessage(), PHP_EOL;
} finally {
    $mysqli->close();

    exit;
}
