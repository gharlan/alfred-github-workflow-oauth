<?php

$code = $_GET['code'] ?? null;

if (!$code) {
    exit('Missing code parameter.');
}

$state = $_GET['state'] ?? '';
// bc
if (1 == $state) {
    $state = 'm';
}

$data = json_encode([
    'client_id' => $_ENV['CLIENT_ID'],
    'client_secret' => $_ENV['CLIENT_SECRET'],
    'code' => $code,
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://github.com/login/oauth/access_token');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: '.strlen($data),
    'Accept: application/json',
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$result = curl_exec($ch);
$status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (200 !== $status) {
    exit('Response status '.$status.' from GitHub.');
}

$result = json_decode($result, true);
$result['enterprise'] = str_contains($state, 'e');

if (str_contains($state, 'm')) {
    echo 'Please open Alfred and call this command:<br><br><code>gh > login ' . $result['access_token'] . '</code>';
    exit;
}

header('Location: http://localhost:2233/?'.http_build_query($result));
exit;
