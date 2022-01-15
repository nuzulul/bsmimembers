<?php
$auth = base64_encode("username:password");
$now = date("Y-m-d-H-i-s");
$apikey =  getenv('XAPIKEY');
$context = stream_context_create([
    "http" => [
        "method" => "GET",
        "header" => "X-API-Key: $apikey\r\n" .
        "Cookie: foo=$auth\r\n"
    ]
]);

$file = file_get_contents('https://bsmi.sourceforge.io/phpcrudapi/api.php/records/donasi/35?cache='. $now, false, $context);var_dump($file);
?>
