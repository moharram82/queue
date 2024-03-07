<?php

require_once __DIR__ . '/../vendor/autoload.php';

$database_connection_options = [
    'host' => '127.0.0.1',
    'username' => 'root',
    'password' => 'Moh_82_key',
    'database' => 'moharram82-queue',
    'port' => 3306,
];

try {
    $connection = new PDO(
        "mysql:host={$database_connection_options['host']};dbname={$database_connection_options['database']};port={$database_connection_options['port']};charset=utf8",
        $database_connection_options['username'],
        $database_connection_options['password'],
        [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET character_set_results=utf8',
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET character_set_client=utf8',
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET character_set_connection=utf8',
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET collation_connection=utf8_general_ci'
        ]
    );
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo $e->getMessage();
    die;
}

$config = new \Moharram82\Queue\QueueConfig();
$config->setDriver(new \Moharram82\Queue\MySQLQueue($connection, null, $config));

$manager = new \Moharram82\Queue\QueueManager($config);
