<?php
$login = json_decode(file_get_contents(__DIR__ . '/login.json'), true);
$token = $login['token'] ?? null;

$header = 'Authorization: Bearer ' . $token;

$ch = curl_init('http://127.0.0.1:8000/api/me');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    $header,
]);
$response = curl_exec($ch);
curl_close($ch);

echo $response . PHP_EOL;
