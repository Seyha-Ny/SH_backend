<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

$user = App\\Models\\User::first();
$bearer = explode('|', '|Bh...d8');
$bearer[0] = $user->createToken('test')->plainTextToken;
$token = implode('|', $bearer);

$ch = curl_init('http://127.0.0.1:8000/api/me');
$parts = [];
$parts[] = 'AUTHORIZATION: Bearer ' . *** curl_setopt_array($ch, [CURLOPT_HTTPHEADER=>$parts, CURLOPT_RETURNTRANSFER=>true]);
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP_CODE={$code}\n";
echo "BODY={$response}\n";
