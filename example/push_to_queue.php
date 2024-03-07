<?php

require_once __DIR__ . '/init.php';

$message = new \Example\MailMessage();
$job = new \Example\SendEmailJob($message);

$manager->push($job, 'default');
