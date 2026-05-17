<?php
echo "KB_ROOT: " . dirname(__DIR__) . "\n";
$path = dirname(__DIR__) . '/plugins/*/Plugin.php';
echo "Path: " . $path . "\n";
print_r(glob($path));
