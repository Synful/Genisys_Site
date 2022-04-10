<?php
require_once("../backend/includes.php");

$errorCode = "";
if(isset($_POST['btnSignIn']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
	if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response']) || DEBUG_MODE) {
		if (!empty($_POST["username"]) && !empty($_POST["password"])) {
			$ret = $site->login(htmlspecialchars($_POST['username']), htmlspecialchars($_POST['password']), $_POST['g-recaptcha-response']);
			if($ret == "Login Successful!") {
        $_SESSION['user'] = serialize(user::$instance);
        header("Location: index");
			} else {
				$errorCode = $ret;
			}
		} else {
			$errorCode = "Please provide a username & password.<br>If you've forgot your password please contact support in our discord.";
		}
	} else {
		$errorCode = "Please check the captcha.";
	}
}

if(isset($_POST['btnRegister'])) {
  header("Location: register");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>Genisys</title>

  <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,600,700,800" rel="stylesheet" />
  <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">
  <link href="assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="assets/css/black-dashboard.css" rel="stylesheet" />
  <link href="../assets/css/particles.css" rel="stylesheet" />
</head>

<body>
  <div id="particles-js"></div>

  <div class="synful-LCont">
    <?php
      if($errorCode != "") {
        echo "<div class=\"alert alert-danger\">
                <button type=\"button\" aria-hidden=\"true\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
                  <i class=\"tim-icons icon-simple-remove\"></i>
                </button>
                <span><i class=\"alert-ico fa fa-fw fa-ban\"></i><strong> Error: </strong>".$errorCode."</span>
              </div>";
      }
    ?>
  </div>

  <div class="login">
    <form class="login_form" action="" method="POST">
      <div class="login_logo"></div>
      <div class="form-group login_group">
        <input class="form-control" name="username" id="username" type="text" placeholder="Username">
      </div>
      <div class="form-group login_group">
        <input class="form-control" name="password" id="password" type="password" placeholder="Password">
      </div>
      <div class="form-group login_group">
        <div class="g-recaptcha" data-theme="dark" data-sitekey="6LdazOYUAAAAAPYAz4Gs8VIupJPOpmTyvbhps8P8"></div>
      </div>
      <div class="form-group login_group login_action">
        <div class="login_submit">
          <button class="btn btn-primary login_btn" name="btnSignIn" type="submit">Sign in</button>
        </div>
        <div class="login_register">
          <button class="btn btn-primary login_btn" name="btnRegister" type="submit">Register</button>
        </div>
      </div>
    </form>
  </div>

  <script src='https://www.google.com/recaptcha/api.js'></script>
  <script src="../assets/js/particles.js"></script>
  <script src="../assets/js/app.js"></script>
  <script src="../assets/js/core/jquery.min.js"></script>
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>

</body>

</html>