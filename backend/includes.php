<?php
DEFINE ("DEBUG_MODE", 0);
DEFINE ("DISABLE_PAYPAL", 1);
DEFINE ("DISABLE_COINBASE", 1);
DEFINE ("FREE_MODE", 1);

date_default_timezone_set("America/New_York");
session_set_cookie_params(0);
session_start();
ob_start();

require_once("site_api.php");
require_once("payment_api.php");

$db = new PDO("");

$site = new site($db);
$user = new user($db);
$paypal = new paypal_api($db);
$coinbase = new coinbase_api($db);
?>