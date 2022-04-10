<?php

class utils {
    public static function isValid($str) {
        return !preg_match('/[^A-Za-z0-9._#\\-$]/', htmlspecialchars($str));
    }
    public static function validName($str, $reg, $db) {
        $name = strtolower($str);
        $flagA = (!preg_match('/staff/', $name) && !preg_match('/admin/', $name) && !preg_match('/moderator/', $name) && !preg_match('/owner/', $name) && !preg_match('/creator/', $name));
		if(!$flagA) {
            return false;
        }

        if(!utils::isValid($str)) {
            return false;
        }
		
		$flagC = true;
		if($reg) {
			$query = $db->prepare("SELECT `username` FROM `users` WHERE `username` = :username");
			$query->execute(array( "username" => $str ));
			$flagC = !($query->rowCount() > 0);
        }
        if(!$flagC) {
            return false;
        }
		
        if(!(strlen($str) >= 4 && strlen($str) <= 16)) {
            return false;
        }
        
		return true;
    }
	public static function validEmail($str, $db) {
		$email = strtolower($str);
		$flagA = (preg_match('/@gmail/', $email) || preg_match('/@yahoo/', $email) || preg_match('/@hotmail/', $email) || preg_match('/@outlook/', $email) || preg_match('/@icloud/', $email));
        if(!$flagA) {
            return false;
        }

		$query = $db->prepare("SELECT `username` FROM `users` WHERE `email` = :username");
        $query->execute(array( "username" => $str ));
        if($query->rowCount() > 0) {
            return false;
        }
		
		return true;
    }
    public static function validLic($lic, $db) {
        $query = $db->prepare("SELECT `license` FROM `users` WHERE `license` = :license");
        $query->execute(array( "license" => $lic ));
        return $query->rowCount() > 0;
    }

    public static function getPS3Logins($db) {
        $query = $db->prepare("SELECT * FROM `logins` WHERE `type` = :t ORDER BY id DESC LIMIT 5");
        $query->execute(array( "t" => "PS3" ));
        return $query->fetchall(PDO::FETCH_CLASS, 'ArrayObject');
    }
    public static function getSiteLogins($db) {
        $query = $db->prepare("SELECT * FROM `logins` WHERE `type` = :t ORDER BY id DESC LIMIT 5");
        $query->execute(array( "t" => "Site" ));
        return $query->fetchall(PDO::FETCH_CLASS, 'ArrayObject');
    }

    public static function usersOnline($db) {
        return 0;
    }
    public static function usersCount($db) {
        $query = $db->prepare("SELECT * FROM `users`");
        $query->execute();
        return $query->rowCount();
    }
    public static function loginsCount($db) {
        $query = $db->prepare("SELECT * FROM `logins`");
        $query->execute();
        return $query->rowCount();
    }
    public static function salesCount($db) {
        $query = $db->prepare("SELECT * FROM `payments`");
        $query->execute();
        return $query->rowCount();
    }
}

class site {
    public $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function register($username, $email, $password, $captcha) {
        if ($this->post_captcha($captcha)) {
            if(isset($username) && utils::validName($username, true, $this->db)) {
                if(isset($email) && utils::validEmail($email, $this->db)) {
					if(isset($password) && utils::isValid($password)) {
						$query = $this->db->prepare("INSERT INTO `users`(`license`, `email`, `username`, `password`) VALUES (:lic, :email, :username, :pass)");
						$query->execute(array(
							'lic' => $this->generate_key(),
							'email' => $email,
							'username' => $username,
							'pass' => password_hash($password, PASSWORD_BCRYPT)
						));
						return "Register Successful!";
					} else {
						return "Password is invalid.";
					}
				} else {
					return "Email is invalid or already taken.";
				}
            } else {
				return "Username is invalid or already taken.";
            }
        } else {
            return "Please check the captcha.";
        }
    }
    public function login($username, $password, $captcha) {
        if ($this->post_captcha($captcha)) {
            if(isset($username) && utils::validName($username, false, $this->db)) {
                if(isset($password) && utils::isValid($password)) {
                    $user = user::$instance;
                    $user->initUserByName($username);
                    if(!$user->banned) {
                        if(password_verify($password, $user->password)) {
                            $this->log_login($username, $user->license, "AuthSuccess");
                            $_SESSION['user'] = serialize($user);
                            return "Login Successful!";
                        } else {
                            $this->log_login($username, $user->license, "InvalidPassword");
                            return "Invalid username or password.";
                        }
                    } else {
                        $this->log_login($username, $user->license, "UserIsBanned");
                        return "You Have Been Banned.";
                    }
                } else {
                    $this->log_login($username, "", "InvalidPassword");
                    return "Invalid username or password.";
                }
            } else {
                $this->log_login($username, "", "InvalidUsername");
                return "Invalid username or password.";
            }
        } else {
            $this->log_login($username, "", "IncorrectCaptcha");
            return "Please check the captcha.";
        }
    }

    private function post_captcha($user_response) {
        $fields_string = '';
        $fields = array(
            'secret' => '6LdazOYUAAAAAA_j8RrmLFZ0F_T8gyg8XrPYKTbe',
            'response' => $user_response
        );
        foreach($fields as $key=>$value)
        $fields_string .= $key . '=' . $value . '&';
        $fields_string = rtrim($fields_string, '&');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);

        $result = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($result, true);
        if(DEBUG_MODE) {
            return true;
        } else {
            return $res['success'];
        }
    }

    private function generate_key() {
        start:
	    $key = '';
	    list($usec, $sec) = explode(' ', microtime());
	    mt_srand((float) $sec + ((float) $usec * 100000));
	
   	    $inputs = array_merge(range('z','a'),range(0,9),range('A','Z'));

   	    for($a=0; $a<10; $a++) {
   	        $key .= $inputs[mt_rand(0,61)];
        }
        $key .='-';
        for($b=0; $b<10; $b++) {
            $key .= $inputs[mt_rand(0,61)];
        }
        $key .='-';
        for($c=0; $c<10; $c++) {
            $key .= $inputs[mt_rand(0,61)];
        }
        if(utils::validLic($key, $this->db)) {
            goto start;
        }
	    return $key;
    }

    private function log_login($username, $lic, $info) {
        $query = $this->db->prepare("INSERT INTO `logins`(`username`, `license`, `ip`, `date`, `type`, `info`) VALUES (:username, :lic, :ip, :dat, :typ, :info)");
        $query->execute(array(
            'username' => $username,
            'lic' => $lic,
            'ip' => $_SERVER["HTTP_CF_CONNECTING_IP"],
            'dat' => strtotime("now"),
            'typ' => "Site",
            'info' => $info
        ));
    }
}

class user {
    public $db;
    
    public static $instance;

    public $username;
    public $password;
    public $email;
    public $license;
    public $time;
    public $banned;
    public $level;

    public $mac;
    public $psid;
    public $setlock;

    public function __construct(PDO $db) {
        $this->db = $db;
        user::$instance = $this;
    }
    public function __serialize(): array {
        return [
          'username' => $this->username,
          'password' => $this->password,
          'email' => $this->email,
          'license' => $this->license,
          'time' => $this->time,
          'banned' => $this->banned,
          'level' => $this->level
        ];
    }
    public function __unserialize(array $data): void {
        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->email = $data['email'];
        $this->license = $data['license'];
        $this->time = $data['time'];
        $this->banned = $data['banned'];
        $this->level = $data['level'];
    }
    
    public function initUserByName($username) {
        $query = $this->db->prepare("SELECT * FROM `users` WHERE `username` = :username");
        $query->execute(array(
            "username" => $username
        ));
        $data = $query->fetch();

        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->email = $data['email'];
        $this->license = $data['license'];
        $this->time = $data['enddate'];
        $this->banned = $data['banned'];
        $this->level = $data['level'];
    }
    public function initUserByLic($lic) {
        $query = $this->db->prepare("SELECT * FROM `users` WHERE `license` = :license");
        $query->execute(array(
            "license" => $lic
        ));
        $data = $query->fetch();

        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->email = $data['email'];
        $this->license = $data['license'];
        $this->time = $data['enddate'];
        $this->banned = $data['banned'];
        $this->level = $data['level'];
        
        $this->mac = $data['mac'];
        $this->psid = $data['psid'];
        $this->setlock = $data['setlock'];
    }

    public function hasTime() {
        return ($this->time != 0 && $this->time > strtotime("now"));
    }
    public function updatePassword($oldpass, $newpass, $rnewpass) {
        if(utils::isValid($oldpass) && utils::isValid($newpass) && utils::isValid($rnewpass)) {
            if(password_verify($oldpass, $this->password)) {
                if($newpass == $rnewpass) {
                    $query = $this->db->prepare("UPDATE `users` SET `password` = :pass WHERE `license` = :license");
                    $query->execute(array("pass" => password_hash($newpass, PASSWORD_BCRYPT), "license" => $this->license));

                    return "Password updated successfully.";
                } else {
                    return "Passwords do not match.";
                }
            } else {
                return "Current password is incorrect.";
            }
        } else {
            return "Please provide valid inputs.";
        }
    }
    public function requestReset($reason) {
        $checkQ = $this->db->prepare("SELECT * FROM `requests` WHERE `license` = :license AND `status` = :stat");
        $checkQ->execute(array( "license" => $this->license, "stat" => 0 ));
        if($checkQ->rowCount() > 0) {
            return "You already have an active reset request.";
        } else {
            $query = $this->db->prepare("INSERT INTO `requests`(`username`, `license`, `requestdate`, `filleddate`, `time`, `handler`, `status`, `requestreason`) VALUES (:username, :lic, :rqdate, :fdate, :utime, :handler, :stat, :reason)");
            $query->execute(array(
                'username' => $this->username,
                'lic' => $this->license,
                'rqdate' => strtotime("now"),
                'fdate' => 0,
                'utime' => $this->time,
                'handler' => "N/A",
                'stat' => 0,
                'reason' => $reason
            ));
            return "Reset request submitted. Please allow at least a day for a response.";
        }
    }
    public function addTime($type) {
        switch($type) {
            case 1:
                $query = $this->db->prepare("UPDATE `users` SET `enddate` = :ed WHERE `license` = :lic");
                $query->execute(array(
                    "lic" => $this->license,
                    "ed" => strtotime("+1 week")
                ));
                break;
            case 2:
                $query = $this->db->prepare("UPDATE `users` SET `enddate` = :ed WHERE `license` = :lic");
                $query->execute(array(
                    "lic" => $this->license,
                    "ed" => strtotime("+1 month")
                ));
                break;
            case 3:
                $query = $this->db->prepare("UPDATE `users` SET `enddate` = :ed WHERE `license` = :lic");
                $query->execute(array(
                    "lic" => $this->license,
                    "ed" => strtotime("+1 year")
                ));
                break;
            case 4:
                $query = $this->db->prepare("UPDATE `users` SET `enddate` = :ed WHERE `license` = :lic");
                $query->execute(array(
                    "lic" => $this->license,
                    "ed" => strtotime("2037-12-31")
                ));
                break;
        }
    }
}

class admin {
    public $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function get_user($license) {
        $query = $this->db->prepare("SELECT * FROM `users` WHERE `license` = :license");
        $query->execute(array(
            "license" => $license
        ));
        $data = $query->fetch();

        return $data;
    }

    public function get_all_users() {
        $query = $this->db->prepare("SELECT * FROM `users` ORDER BY id");
        $query->execute();
        return $query->fetchall(PDO::FETCH_CLASS, 'ArrayObject');
    }

    public function get_user_ps3_logins($license) {
        $query = $this->db->prepare("SELECT * FROM `logins` WHERE `license` = :license AND `type` = :t ORDER BY id");
        $query->execute(array( "license" => $license, "t" => "PS3" ));
        return $query->fetchall(PDO::FETCH_CLASS, 'ArrayObject');
    }
    public function get_user_site_logins($license) {
        $query = $this->db->prepare("SELECT * FROM `logins` WHERE `license` = :license AND `type` = :t ORDER BY id");
        $query->execute(array( "license" => $license, "t" => "Site" ));
        return $query->fetchall(PDO::FETCH_CLASS, 'ArrayObject');
    }

    public function get_user_payments($username) {
        $query = $this->db->prepare("SELECT * FROM `payments` WHERE `username` = :usern ORDER BY id");
        $query->execute(array( "usern" => $username ));
        return $query->fetchall(PDO::FETCH_CLASS, 'ArrayObject');
    }

    public function get_user_latest_request($license) {
        $query = $this->db->prepare("SELECT * FROM `requests` WHERE `license` = :license AND `status` = :stat ORDER BY id");
        $query->execute(array( "license" => $license, "stat" => 0 ));
        if($query->rowCount() > 0) {
            return $query->fetchall(PDO::FETCH_CLASS, 'ArrayObject')[0];
        } else {
            return null;
        }
    }

    public function set_user_request($id, $lic, $type, $reason, $user) {
        $resetQ = $this->db->prepare("UPDATE `requests` SET `filleddate` = :fdate, `handler` = :handler, `status` = :stat, `denyreason` = :dreason WHERE `id` = :id AND `status` = 0");
        $resetQ->execute(array( "id" => $id,
                                "fdate" => strtotime("now"),
                                "handler" => $user,
                                "stat" => $type,
                                "dreason" => $reason));
        if($type == 2) {
            $query = $this->db->prepare("UPDATE `users` SET `setlock` = 1 WHERE `license` = :license");
            $query->execute(array("license" => $lic));
        }
    }
}
?>