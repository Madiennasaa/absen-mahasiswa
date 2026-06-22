<?php
require 'vendor/autoload.php';

$server   = 'fef9f2859a7244f0b347d8aef97a6df7.s1.eu.hivemq.cloud';
$port     = 8883;
$clientId = 'Laravel-Web-Test-' . uniqid();
$username = 'uas.kelompok1';
$password = 'Admin123';

try {
    echo "Connecting to MQTT...\n";
    $connectionSettings = (new \PhpMqtt\Client\ConnectionSettings)
        ->setUsername($username)
        ->setPassword($password)
        ->setUseTls(true)
        ->setTlsVerifyPeer(false);

    $mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $clientId);
    $mqtt->connect($connectionSettings, true);
    $mqtt->publish('kampus/cmd/status', 'on', 0);
    $mqtt->disconnect();
    echo "Published 'on' to kampus/cmd/status successfully!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
