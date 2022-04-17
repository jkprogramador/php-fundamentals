<?php

define('DB_NAME', getenv('MYSQL_DATABASE'));
define('DB_USER', 'root');
define('DB_PASSWORD', getenv('MYSQL_ROOT_PASSWORD'));

try {
    echo <<<TEXT

    Querying via PDO (PHP Data Objects)
    Check http://php.net/manual/en/pdo.constants.php for fetch styles.


    TEXT;

    $conn = new PDO('mysql:host=db;dbname=' . DB_NAME, DB_USER, DB_PASSWORD, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $result = $conn->query('SELECT * FROM customer LIMIT 5');

    $customers = $result->fetchAll(PDO::FETCH_OBJ);

    foreach ($customers as $customer) {
        echo $customer->first_name, ' ', $customer->last_name, PHP_EOL;
    }

    echo <<<TEXT


    PDO provides named binding parameters, improving readability.


    TEXT;

    $statement = $conn->prepare('SELECT * FROM customer WHERE customer_id > :customer_id AND store_id = :store_id AND email LIKE :email');
    $statement->execute([
        ':customer_id' => 100,
        ':store_id' => 2,
        ':email' => '%ANN%',
    ]);

    $customers = $statement->fetchAll(PDO::FETCH_OBJ);

    foreach ($customers as $customer) {
        echo $customer->first_name, ' ', $customer->last_name, PHP_EOL;
    }

    echo <<<TEXT


    Inserting


    TEXT;

    $statement = $conn->prepare('INSERT INTO actor (first_name, last_name) VALUES (:first_name, :last_name);');
    $statement->execute([
        ':first_name' => 'Jane',
        ':last_name' => 'Doe',
    ]);

    echo <<<TEXT


    Updating


    TEXT;

    $statement = $conn->prepare('UPDATE address SET phone = :phone WHERE address_id = :address_id');
    $statement->execute([
        ':phone' => '888777666555',
        ':address_id' => 600,
    ]);

    echo <<<TEXT


    Deleting


    TEXT;

    $statement = $conn->prepare('DELETE FROM payment WHERE payment_id = :payment_id');
    $statement->execute([':payment_id' => 16046]);

    echo <<<TEXT


    Transactions
    Check https://dev.mysql.com/doc/refman/8.0/en/commit.html


    TEXT;

    $conn->beginTransaction();

    $statement = $conn->prepare('INSERT INTO actor (first_name, last_name) VALUES (:first_name, :last_name)');
    $statement->execute([
        ':first_name' => 'Boo',
        ':last_name' => 'Doe',
    ]);

    // Fetch newly created address id
    $actorId = $conn->lastInsertId();

    $statement = $conn->prepare('SELECT * from actor WHERE actor_id = :actor_id');
    $statement->execute([':actor_id' => $actorId]);
    $actor = $statement->fetchObject();

    $conn->commit();

    echo $actor->first_name, ' ', $actor->last_name, PHP_EOL;
} catch (PDOException $e) {

    $conn->rollback();

    echo $e->getMessage(), PHP_EOL;
} finally {
    exit;
}
