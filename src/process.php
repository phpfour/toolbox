<?php

require_once './vendor/autoload.php';

$host = 'localhost';
$user = 'root';
$pass = 'commonrbs';
$name = 'toolbox';
$char = 'UTF8';

$dsn = "mysql:host=$host;dbname=$name;charset=$char";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
];

$pdo = new PDO($dsn, $user, $pass, $options);

$rows = $pdo->query("SELECT * FROM IBBL")->fetchAll();

$updateQuery = $pdo->prepare('UPDATE IBBL SET date = :date, party = :party, amount = :amount, type = :type WHERE id = :id');

foreach ($rows as $row) {
    list($type, $amount, $party) = IBBL::parse($row['text']);

    if ($type !== null) {
        $date = (new \DateTime($row['date']))->format('d-m-Y');
        echo sprintf("%s [%s] %0.2f - %s \n", $date, strtoupper($type), $amount, $party);

        $updateQuery->execute([
            'id' => $row['id'],
            'date' => $date,
            'party' => $party,
            'amount' => $amount,
            'type' => $type
        ]);
    }
}


