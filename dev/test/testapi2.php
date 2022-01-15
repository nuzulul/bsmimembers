<?php
$auth = base64_encode("username:password");
$now = date("Y-m-d-H-i-s");
$apikey =  "TES";
$context = stream_context_create([
    "http" => [
        "method" => "GET",
        "header" => "Accept-languange: ID\r\n" .
        "X-API-Key: $apikey\r\n" .
        "Cookie: foo=$auth\r\n"
    ]
]);

$url = "https://members.bsmijatim.org/dev/test/getallheader.php";

$file = file_get_contents($url. '?cache='. $now, false, $context);var_dump($file);
?>
