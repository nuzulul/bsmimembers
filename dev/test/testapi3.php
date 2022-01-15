<?php
$auth = base64_encode("username:password");
$now = date("Y-m-d-H-i-s");
$apikey =  getenv('XAPIKEY');
    $fields = array(
        'keyword' => "bsmi53202db7-cdda-4516-bb79-de714bc889c9",
    );
    $payload = json_encode($fields);
    $context = stream_context_create([
      "http" => [
          "method" => "POST",
          "header" => "Content-Type: application/json; charset=utf-8\r\n".
            "X-API-Key: $apikey\r\n",
          'content' => $payload,
          'timeout' => 60
      ]
    ]);

//$file = file_get_contents('https://bsmi.sourceforge.io/phpcrudapi/api.php/records/token?cache='. $now, false, $context);var_dump($file);
?>
