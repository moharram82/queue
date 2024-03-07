# Queue Package

## Introduction
This is a PHP Queue package for executing time-consuming tasks in the background.

## Requirements
- PHP 7.4+
- MySQL 5.6+ (for database driver)

## Installation & Configuration
- Install using composer:
```composer
composer require moharram82/php-queue
```
- Instantiate a new `\Moharram82\Queue\QueueConfig` instance, which accepts an optional configuration array.
```php
$config = new \Moharram82\Queue\QueueConfig([
    'queue' => 'default',
    'retry_after' => 90, // in seconds
    'table' => 'queues', // database driver table name
]);
```
- Instantiate a driver implementation of `\Moharram82\Queue\QueueInterface` instance like `\Moharram82\Queue\MySQLQueue`
```php
$database_connection_options = [
    'host' => '127.0.0.1',
    'username' => 'root',
    'password' => '',
    'database' => 'queue',
    'port' => 3306,
];

// create PDO database connection, which is required by `\Moharram82\Queue\MySQLQueue` as first parameter
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

// pass the connection as first optional parameter or database connection array as second optional parameter, then `\Moharram82\Queue\QueueConfig` as third optional parameter.
// keep in mind, you have to pass at least one of the first and the second parameters.
$config->setDriver(new \Moharram82\Queue\MySQLQueue($connection, [], $config));
```
- Instantiate a new `\Moharram82\Queue\QueueManager` instance
```php
$manager = new \Moharram82\Queue\QueueManager($config);
```

## Usage
### Push to Queue
```php
// create a Job class (which is a normal PHP class with a handle method) passing to it the object you want to process.

namespace Example;

class SendEmailJob
{
    public MailMessage $message;

    public function __construct(MailMessage $message)
    {
        $this->message = $message;
    }

    public function handle()
    {
        $this->message->send();
    }
}

$job = new \Example\SendEmailJob(new \Example\MailMessage());

// call the QueueManager instance's push method and pass to it the Job class object with an optional second parameter for the queue name.
$manager->push($job, 'default');
```

### Process the Queue
```php
// call the QueueManager instance's run method.
// You can call the `run` method from a cron job or supervisor process.
$manager->run('default');
```

## Examples
Check the `example` directory for sample scripts for pushing and processing the queue.

## Extending Queue
You can add more drivers by extending the abstract class `\Moharram82\Queue\Queue` and implementing the interface `\Moharram82\Queue\QueueInterface`
