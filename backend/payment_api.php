<?php

require_once(dirname(__FILE__). "/../assets/lib/coinbase/vendor/autoload.php");
use CoinbaseCommerce\ApiClient;
use CoinbaseCommerce\Resources\Charge;
use CoinbaseCommerce\Webhook;

class coinbase_api {
    public $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function create($id, $lic) {
        ApiClient::init('');
        $query = $this->db->prepare("SELECT * FROM `products` WHERE `id` = :id");
        $query->execute(array(
            "id" => $id
        ));
        $product = $query->fetch();

        $pricePlusFee = $product['price'] + ($product['price'] * 0.01);

        $chargeObj = new Charge([
            "name" => $product['name'],
            "description" => $product['name'],
            "metadata" => [
                "lic" => $lic,
                "id" => $id
            ],
            "local_price" => [ "amount" => $pricePlusFee, "currency" => "USD" ],
            "pricing_type" => "fixed_price",
            "requested_info" => []
        ]);
        $chargeObj->save();

        return "https://commerce.coinbase.com/charges/".$chargeObj->code;
    }

    public function response($user, $lic, $id, $payment) {
        $query = $this->db->prepare("SELECT * FROM `products` WHERE `id` = :id");
        $query->execute(array( "id" => $id ));
        $product = $query->fetch();

        $pricePlusFee = $product['price'] + ($product['price'] * 0.01);

        if($payment['value']['local']['amount'] >= $pricePlusFee && $payment['status'] == "CONFIRMED") {
            $user->db = $this->db;
            $user->addTime($id);
            $this->log($user->username, $user->license, $payment['value']['local']['amount'], $payment['value']['crypto']['currency'], $payment['transaction_id']);;
        }
    }

    private function log($username, $lic, $price_paid, $coin, $transaction_id) {
        $json = "{\"coin\": \"".$coin."\", \"transaction_id\": \"".$transaction_id."\"}";

		    $chk_query = $this->db->prepare("SELECT * FROM `payments` WHERE `info` = :info");
        $chk_query->execute(array( "info" => $json ));
        if($chk_query->rowCount() > 0){
            return;
        }
        
        $query = $this->db->prepare("INSERT INTO `payments`(`type`, `username`, `license`, `price_paid`, `date`, `info`) VALUES (:t, :user, :lic, :pp, :dat, :inf)");
        $query->execute(array(
            't' => 1,
            'user' => $username,
            'lic' => $lic,
            'pp' => $price_paid,
			      'dat' => strtotime("now"),
            'inf' => $json
        ));
    }
}

class paypal_api {
    public $db;

    private $username = "";
    private $pass     = "";
    private $sig      = "";
    private $email    = "";

    public function __construct(PDO $db) {
        if(!DEBUG_MODE) {
            $this->username = "";
            $this->pass     = "";
            $this->sig      = "";
            $this->email    = "";
        } else {
            $this->username = "";
            $this->pass     = "";
            $this->sig      = "";
            $this->email    = "";
        }
        $this->db = $db;
    }

    private function get_link($type) {
        if($type == "API"){
            if(DEBUG_MODE) {
                return "https://api-3t.sandbox.paypal.com/nvp";
            } else {
                return "https://api-3t.paypal.com/nvp";
            }
        } else {
            if(DEBUG_MODE) {
                return "https://sandbox.paypal.com";
            } else {
                return "https://www.paypal.com";
            }
        }
    }

    private function setup_curl($link,$params) {
        $ch = curl_init($link);
        curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($params));
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER ,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST ,false);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER ,1);
        return curl_exec($ch);
    }

    private function verify($var) {
        switch ($var['PAYMENTINFO_0_ERRORCODE']) {
            case '11607':
                $desc = "Duplicate request for specified Message Submission ID, you will be redirected in 5 seconds";
                return $desc;
                break;
            case '11610':
                $desc = "Payment Pending your review in Fraud Management Filters, you will be redirected in 5 seconds";
                return $desc;
                break;
            case '11821':
                $desc = "Transaction denied by fraud management contact PayPal, you will be redirected in 5 seconds";
                return $desc;
                break;
            case '13112':
                $desc = "Unable to pay seller for this transaction, you will be redirected in 5 seconds";
                return $desc;
                break;
            case '13113':
                $desc = "You can not pay with PayPal for this transaction, you will be redirected in 5 seconds";
                return $desc;
                break;
            case '15001':
                $desc = "Transaction could not be processed, you will be redirected in 5 seconds";
                return $desc;
                break;
            case '15005':
                $desc = "Transaction declined please try another card, you will be redirected in 5 seconds";
                return $desc;
                break;
            case '15006':
                $desc = "Transaction declined, please try another car, you will be redirected in 5 seconds";
                return $desc;
                break;
            case '15011':
                $desc = "The credit card used, is from an unsupported country, you will be redirected in 5 seconds";
                return $desc;
                break;
            case '99998':
                $desc = "The transaction could not be processed, you will be redirected in 5 seconds";
                return $desc;
                break;
            case '10416':
                $desc = "You have exceeded max payment attempts for this token, you will be redirected in 5 seconds";
                return $desc;
                break;
            case '0':
                return "No Errors";
                break;
            default:
                return "Unknown error";
                break;
        }
    }

    public function create($id) {
        $query = $this->db->prepare("SELECT * FROM `products` WHERE `id` = :id");
        $query->execute(array(
            "id" => $id
        ));
        $product = $query->fetch();

        $_SESSION['price'] = $product['price'];
        $arr = array(
            "USER"=>$this->username,
            "PWD"=>$this->pass,
            "SIGNATURE"=>$this->sig,
            "METHOD"=>"SetExpressCheckout",
            "VERSION"=>"109.0",
            "RETURNURL"=>"https://".$_SERVER['HTTP_HOST']."/panel/index",
            "CANCELURL"=>"https:/".$_SERVER['HTTP_HOST']."/panel/products",
            "PAYMENTREQUEST_0_PAYMENTACTION"=>"Sale",
            "PAYMENTREQUEST_0_AMT"=>$product['price'],
            "PAYMENTREQUEST_0_CURRENCYCODE"=>"USD",
            "L_PAYMENTREQUEST_0_NAME0"=>"". $product['name'],
            "L_PAYMENTREQUEST_0_DESC0"=>"". $product['name'],
            "L_PAYMENTREQUEST_0_QTY0"=>"1",
            "PAYMENTREQUEST_0_ITEMAMT"=>$product['price'],
            "L_PAYMENTREQUEST_0_AMT0"=>$product['price'],
            "REQCONFIRMSHIPPING"=>1,
            "PAYMENTREQUEST_0_SELLERPAYPALACCOUNTID"=>$this->email
        );
      
        $return = $this->setup_curl($this->get_link("API"),$arr);
      
        $decode = urldecode($return);

        $arr = explode('&',$decode);
      
        $arr = explode('=',$arr[0]);
        $token =  $arr[1];
      
        $link = $this->get_link("")."/checkoutnow?token=".$token."&useraction=commit";
        return $link;
    }

    public function response($user, $token, $payerId, $price) {
    
        $arr = array(
            "USER"=>$this->username,
            "PWD"=>$this->pass,
            "SIGNATURE"=>$this->sig,
            "METHOD"=>"DoExpressCheckoutPayment",
            "VERSION"=>"109.0",
            "TOKEN"=>$token,
            "PAYERID"=>$payerId,
            "PAYMENTREQUEST_0_AMT"=>$price,
            "PAYMENTREQUEST_0_CURRENCYCODE"=>"USD"
        );
        
        $return = $this->setup_curl($this->get_link("API"),$arr);
    
        $arr = explode('&',urldecode($return));
        foreach ($arr as $val) {
            $value = explode('=',$val);
            $var[$value[0]] = $value[1];
        }
    
        $verify = $this->verify($var);
        if($verify == null) { return; }
        
        if($verify == "No Errors") {
            if($var['ACK'] == "Success") {
                if($var['PAYMENTINFO_0_AMT']==$price && $var['PAYMENTINFO_0_PAYMENTSTATUS'] == "Completed") {
                    $user->db = $this->db;
                    switch($price) {
                        case 5:
                            $user->addTime(1);
                            break;
                        case 12.50:
                            $user->addTime(2);
                            break;
                        case 30:
                            $user->addTime(3);
                            break;
                    }
                    $this->log($user->username, $user->license, $var['PAYMENTINFO_0_AMT'], $var['TOKEN'], $var['PAYMENTINFO_0_TRANSACTIONID'], $var['PAYMENTINFO_0_SECUREMERCHANTACCOUNTID']);
                }
            }
        }
    }

    private function log($username, $lic, $price_paid, $token, $payment_id, $merchant_id) {
        $json = "{\"token\": \"".$token."\", \"payment_id\": \"".$payment_id."\", \"merchant_id\": \"".$merchant_id."\"}";

		    $chk_query = $this->db->prepare("SELECT * FROM `payments` WHERE `info` = :info");
        $chk_query->execute(array( "info" => $json ));
        if($chk_query->rowCount() > 0) {
            return;
        }
        
        $query = $this->db->prepare("INSERT INTO `payments`(`type`, `username`, `license`, `price_paid`, `date`, `info`) VALUES (:t, :user, :lic, :pp, :dat, :info)");
        $query->execute(array(
            't' => 0,
            'user' => $username,
            'lic' => $lic,
            'pp' => $price_paid,
		        'dat' => strtotime("now"),
            'info' => $json
        ));
    }
}

?>