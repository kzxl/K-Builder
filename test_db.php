<?php
require 'vendor/autoload.php';
$config = require 'config/database.php';
$capsule = new \Illuminate\Database\Capsule\Manager();
$capsule->addConnection($config);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$users = \Illuminate\Database\Capsule\Manager::table('users')->get();
echo "Users count: " . count($users) . "\n";
foreach ($users as $u) {
    echo $u->email . "\n";
}
