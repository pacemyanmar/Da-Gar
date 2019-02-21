#!/usr/bin/env php
<?php

$max_process = 100;
$commands = [];
for ($i = 0; $i < 100; $i++) {
    $commands[] = 'vendor/bin/phpunit tests/SmsApiTest.php > /dev/null';
}
while ($countprocess = exec('ps aux | grep phpunit | wc -l') < $max_process) {

    $command = implode(';', $commands);
    exec($command);
}
