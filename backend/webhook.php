<?php
require_once("../assets/lib/coinbase/vendor/autoload.php");
require_once("includes.php");

use CoinbaseCommerce\Webhook;

$secret = '';
$headerName = 'X-Cc-Webhook-Signature';
$headers = getallheaders();
$signraturHeader = isset($headers[$headerName]) ? $headers[$headerName] : null;
$payload = trim(file_get_contents('php://input'));

try {
  $event = Webhook::buildEvent($payload, $signraturHeader, $secret);
  http_response_code(200);
  if($event->type == "charge:confirmed" && isset($event->data['metadata']['lic']) && isset($event->data['metadata']['id'])) {
    $user->initUserByName($event->data['metadata']['lic']);
    $coinbase->response($user, $event->data['metadata']['lic'], $event->data['metadata']['id'], $event->data['payments'][0]);
  }
} catch (\Exception $exception) {
  http_response_code(400);
}

?>